<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Validation\\Rule;

class QuanLyKhoController extends Controller
{
    private const CATEGORIES = ['THUOC', 'VAC_XIN', 'THUC_AN', 'DO_DUNG'];

    public function store(Request $request)
    {
        $fields = $request->validate([
            'masanpham' => ['required', 'string', 'max:50', 'regex:/^(THUOC|VACXIN|THUCAN|DODUNG)-[0-9]{8}-[0-9]{6}$/', 'unique:kho_hang,masanpham'],
            'ten_sanpham' => 'required|string|max:100',
            'danhmuc' => ['required', Rule::in(self::CATEGORIES)],
            'donvitinh' => 'nullable|string|max:20',
            'dongia' => 'required|numeric|min:0',
            'soluong' => 'required|integer|min:0',
        ]);

        if ($fields['danhmuc'] === 'VAC_XIN'
            && DB::table('loai_vac_xin')->where('name', $fields['ten_sanpham'])->whereNotNull('inventory_item_id')->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Tên vắc-xin này đã có trong kho. Hãy cập nhật mặt hàng hiện có.'], 422);
        }

        $item = DB::transaction(function () use ($fields) {
            $id = DB::table('kho_hang')->insertGetId([
                'masanpham' => $fields['masanpham'],
                'ten_sanpham' => $fields['ten_sanpham'],
                'danhmuc' => $fields['danhmuc'],
                'donvitinh' => $fields['donvitinh'] ?? '',
                'dongia' => $fields['dongia'],
                'soluongton' => (int) $fields['soluong'],
            ]);

            if ($fields['danhmuc'] === 'VAC_XIN') {
                $existingType = DB::table('loai_vac_xin')->where('name', $fields['ten_sanpham'])->first();
                if ($existingType) {
                    DB::table('loai_vac_xin')->where('vaccination_type_id', $existingType->vaccination_type_id)->update([
                        'inventory_item_id' => $id, 'is_active' => true, 'updated_at' => now(),
                    ]);
                } else {
                    DB::table('loai_vac_xin')->insert([
                        'name' => $fields['ten_sanpham'], 'inventory_item_id' => $id, 'is_active' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            return DB::table('kho_hang')->where('item_id', $id)->first();
        });

        return response()->json(['status' => 'success', 'message' => 'Đã thêm mặt hàng vào danh mục kho.', 'data' => $item], 201);
    }

    public function update(Request $request, int $id)
    {
        $item = DB::table('kho_hang')->where('item_id', $id)->first();
        if (!$item || !in_array($item->danhmuc, self::CATEGORIES, true)) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy mặt hàng.'], 404);
        }

        $fields = $request->validate([
            'ten_sanpham' => 'required|string|max:100',
            'danhmuc' => ['required', Rule::in(self::CATEGORIES)],
            'donvitinh' => 'nullable|string|max:20',
            'dongia' => 'required|numeric|min:0',
            'soluong' => 'required|integer|min:0',
        ]);

        if ($item->danhmuc !== $fields['danhmuc']) {
            if (DB::table('don_thuoc')->where('item_id', $id)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Không thể đổi danh mục thuốc đã từng được kê đơn.'], 422);
            }
            if (DB::table('loai_vac_xin')->where('inventory_item_id', $id)->exists() && $fields['danhmuc'] !== 'VAC_XIN') {
                return response()->json(['status' => 'error', 'message' => 'Mặt hàng đang liên kết với danh mục tiêm chủng nên không thể đổi khỏi loại Vắc-xin.'], 422);
            }
        }

        DB::transaction(function () use ($fields, $id) {
            DB::table('kho_hang')->where('item_id', $id)->update([
                'ten_sanpham' => $fields['ten_sanpham'],
                'danhmuc' => $fields['danhmuc'],
                'donvitinh' => $fields['donvitinh'] ?? '',
                'dongia' => $fields['dongia'],
                'soluongton' => (int) $fields['soluong'],
            ]);

            DB::table('loai_vac_xin')->where('inventory_item_id', $id)->update([
                'name' => $fields['ten_sanpham'], 'updated_at' => now(),
            ]);
        });

        return response()->json([
            'status' => 'success', 'message' => 'Đã cập nhật mặt hàng.',
            'data' => DB::table('kho_hang')->where('item_id', $id)->first(),
        ]);
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $item = DB::table('kho_hang')->where('item_id', $id)->lockForUpdate()->first();
            if (!$item || !in_array($item->danhmuc, self::CATEGORIES, true)) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy mặt hàng.'], 404);
            }
            if (DB::table('loai_vac_xin')->where('inventory_item_id', $id)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Mặt hàng đang được liên kết với danh mục vắc-xin.'], 422);
            }
            if ((int) $item->soluongton > 0) {
                return response()->json(['status' => 'error', 'message' => 'Không thể xóa mặt hàng còn tồn kho. Hãy xử lý số lượng tồn trước.'], 422);
            }
            if (DB::table('don_thuoc')->where('item_id', $id)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Không thể xóa thuốc đã từng được kê đơn để bảo toàn lịch sử khám chữa bệnh.'], 422);
            }
            DB::table('kho_hang')->where('item_id', $id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Đã xóa mặt hàng.']);
        });
    }
}
