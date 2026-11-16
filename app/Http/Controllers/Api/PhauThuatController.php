<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PhauThuatController extends Controller
{
    private function taoMaPhauThuat(): string
    {
        do {
            $code = 'PT-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(6));
        } while (DB::table('danh_muc_phau_thuat')->where('ma_phauthuat', $code)->exists());

        return $code;
    }

    public function previewCode()
    {
        return response()->json([
            'status' => 'success',
            'data' => ['ma_phauthuat' => $this->taoMaPhauThuat()],
        ]);
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => DB::table('danh_muc_phau_thuat')->orderBy('ten_phauthuat')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'ma_phauthuat' => 'nullable|string|max:30|regex:/^PT-[0-9]{8}-[0-9]{6}-[A-Z0-9]{6}$/|unique:danh_muc_phau_thuat,ma_phauthuat',
            'ten_phauthuat' => 'required|string|max:100',
            'gia' => 'required|integer|min:0',
            'trangthai' => 'nullable|boolean',
        ]);

        $id = DB::table('danh_muc_phau_thuat')->insertGetId([
            'ma_phauthuat' => ($fields['ma_phauthuat'] ?? null) ?: $this->taoMaPhauThuat(),
            'ten_phauthuat' => trim($fields['ten_phauthuat']),
            'gia' => $fields['gia'],
            'trangthai' => $fields['trangthai'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'data' => DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->first()], 201);
    }

    public function update(Request $request, int $id)
    {
        $fields = $request->validate([
            'ten_phauthuat' => 'required|string|max:100',
            'gia' => 'required|integer|min:0',
            'trangthai' => 'required|boolean',
        ]);

        DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->update([
            'ten_phauthuat' => trim($fields['ten_phauthuat']),
            'gia' => $fields['gia'],
            'trangthai' => $fields['trangthai'],
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'data' => DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->first()]);
    }

    public function toggleStatus(int $id)
    {
        $surgery = DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->first();
        abort_if(!$surgery, 404, 'Không tìm thấy loại phẫu thuật.');

        $newStatus = !(bool) $surgery->trangthai;
        DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->update([
            'trangthai' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $newStatus ? 'Đã bật sử dụng loại phẫu thuật.' : 'Đã tạm ngưng loại phẫu thuật.',
            'data' => DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->first(),
        ]);
    }

    public function destroy(int $id)
    {
        $used = DB::table('phieu_kham')->where('surgery_id', $id)->exists();
        if ($used) {
            DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->update(['trangthai' => false, 'updated_at' => now()]);
            return response()->json(['status' => 'success', 'message' => 'Loại phẫu thuật đã được sử dụng nên được ngừng áp dụng, không xóa lịch sử.']);
        }

        DB::table('danh_muc_phau_thuat')->where('surgery_id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Đã xóa loại phẫu thuật.']);
    }
}