<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Exception;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;

class KhoController extends Controller
{
    public function index()
    {
        $items = DB::table('kho_hang')->get();
        foreach ($items as $item) {
            $item->tong_ton_kho = (int) $item->soluongton;
        }

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'masanpham' => 'required|string|max:20|unique:kho_hang,masanpham',
            'ten_sanpham' => 'required|string|max:100',
            'danhmuc' => 'required|in:THUOC,VAC_XIN,THUC_AN,DO_DUNG,DICH_VU_SPA',
            'donvitinh' => 'required|string|max:20',
            'dongia' => 'required|numeric|min:0',
        ]);

        $id = DB::table('kho_hang')->insertGetId([
            'masanpham' => $fields['masanpham'],
            'ten_sanpham' => $fields['ten_sanpham'],
            'danhmuc' => $fields['danhmuc'],
            'donvitinh' => $fields['donvitinh'],
            'dongia' => $fields['dongia'],
            'soluongton' => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm danh mục sản phẩm/dịch vụ thành công!',
            'data' => DB::table('kho_hang')->where('item_id', $id)->first(),
        ], 201);
    }

    public function exportMedicine(Request $request)
    {
        $fields = $request->validate([
            'medicine_id' => 'required|integer|exists:kho_hang,item_id',
            'required_qty' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($fields) {
                $item = DB::table('kho_hang')->where('item_id', $fields['medicine_id'])->lockForUpdate()->first();
                if (!$item || !in_array($item->danhmuc, ['THUOC', 'VAC_XIN', 'THUC_AN', 'DO_DUNG'], true)) {
                    throw new Exception('Mặt hàng không hợp lệ để xuất kho.');
                }
                if ((int) $item->soluongton < (int) $fields['required_qty']) {
                    throw new Exception('Kho không đủ số lượng yêu cầu. Hiện còn ' . (int) $item->soluongton . '.');
                }
                DB::table('kho_hang')->where('item_id', $item->item_id)
                    ->decrement('soluongton', (int) $fields['required_qty']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Xuất kho thành công.',
                    'data' => ['item_id' => $item->item_id, 'quantity' => (int) $fields['required_qty']],
                ]);
            });
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
