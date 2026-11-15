<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TongQuanController extends Controller
{
    /**
     * FR-STAT-01: Chỉ số Dashboard tổng quan thời gian thực
     */
    public function getOverview()
    {
        $today = Carbon::today();

        $soCaKhamNgay = DB::table('phieu_kham')->whereDate('ngaykham', $today)->count();
        $soThuNoiTru  = DB::table('thu_cung')->where('trangthai', 'NOI_TRU')->count();
        $soLongTrong  = DB::table('long_nuoi')->where('trangthai', 'CON_TRONG')->count();
        $doanhThuNgay = DB::table('hoa_don')->where('trangthai', 'DA_THANH_TOAN')->whereDate('ngaytao', $today)->sum('tongtien');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'so_ca_kham_ngay' => $soCaKhamNgay,
                'so_thu_noi_tru'  => $soThuNoiTru,
                'so_long_trong'   => $soLongTrong,
                'doanh_thu_ngay'  => (float)$doanhThuNgay,
            ]
        ]);
    }

    /**
     * Cảnh báo mặt hàng sắp hết dựa trên số lượng tồn trực tiếp.
     */
    public function getInventoryAlerts()
    {
        $lowStockRows = DB::table('kho_hang')
            ->whereIn('danhmuc', ['THUOC', 'VAC_XIN', 'THUC_AN', 'DO_DUNG'])
            ->where('soluongton', '<=', 5)
            ->orderBy('danhmuc')
            ->orderBy('ten_sanpham')
            ->get(['item_id', 'masanpham', 'ten_sanpham', 'danhmuc', 'soluongton']);

        $khoSapHet = [
            'THUOC' => ['ten_kho' => 'Kho thuốc', 'items' => []],
            'VAC_XIN' => ['ten_kho' => 'Kho vắc-xin tiêm phòng', 'items' => []],
            'THUC_AN' => ['ten_kho' => 'Kho thức ăn', 'items' => []],
            'DO_DUNG' => ['ten_kho' => 'Kho đồ dùng', 'items' => []],
        ];

        foreach ($lowStockRows as $item) {
            $khoSapHet[$item->danhmuc]['items'][] = [
                'ma' => $item->masanpham,
                'ten' => $item->ten_sanpham,
                'so_luong' => (int) $item->soluongton,
            ];
        }

        foreach ($khoSapHet as $key => $store) {
            $khoSapHet[$key]['so_mat_hang'] = count($store['items']);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'kho_sap_het' => $khoSapHet,
            ],
        ]);
    }

    /**
     * Báo cáo doanh thu theo ngày, tuần, tháng và năm.
     */
    public function getRevenueReport(Request $request)
    {
        $validated = $request->validate([
            'ngay' => 'nullable|date',
            'tuan' => 'nullable|date',
            'thang' => 'nullable|date_format:Y-m',
            'nam' => 'nullable|integer|min:2000|max:2100',
        ]);

        $ngay = Carbon::parse($validated['ngay'] ?? Carbon::today())->startOfDay();
        $tuan = Carbon::parse($validated['tuan'] ?? Carbon::today())->startOfWeek(Carbon::MONDAY);
        $thang = Carbon::createFromFormat('Y-m-d', ($validated['thang'] ?? Carbon::today()->format('Y-m')) . '-01')->startOfMonth();
        $nam = (int) ($validated['nam'] ?? Carbon::today()->year);

        $paidInvoices = DB::table('hoa_don')->where('trangthai', 'DA_THANH_TOAN');
        $sumInvoices = function (Carbon $from, Carbon $to) use ($paidInvoices) {
            return (clone $paidInvoices)->whereBetween(DB::raw('DATE(ngaytao)'), [
                $from->toDateString(), $to->toDateString()
            ]);
        };
        $categoryTotals = function (Carbon $from, Carbon $to) {
            return DB::table('chi_tiet_hoa_don as detail')
                ->join('hoa_don as invoice', 'invoice.invoice_id', '=', 'detail.invoice_id')
                ->where('invoice.trangthai', 'DA_THANH_TOAN')
                ->whereBetween(DB::raw('DATE(invoice.ngaytao)'), [$from->toDateString(), $to->toDateString()])
                ->select('detail.loai_chiphi',
                    DB::raw('SUM(detail.thanhtien) as tong_doanh_thu'),
                    DB::raw('COUNT(DISTINCT invoice.invoice_id) as so_hoa_don'))
                ->groupBy('detail.loai_chiphi')->orderByDesc('tong_doanh_thu')->get();
        };

        $dayInvoices = $sumInvoices($ngay, $ngay);
        $dayTotal = (float) (clone $dayInvoices)->sum('tongtien');
        $dayCount = (int) (clone $dayInvoices)->count();
        $dayCategories = $categoryTotals($ngay, $ngay);

        $weekEnd = $tuan->copy()->endOfWeek(Carbon::SUNDAY);
        $weekInvoices = $sumInvoices($tuan, $weekEnd);
        $weekTotal = (float) (clone $weekInvoices)->sum('tongtien');
        $weekCount = (int) (clone $weekInvoices)->count();
        $weeklyRows = [];
        for ($date = $tuan->copy(); $date->lte($weekEnd); $date->addDay()) {
            $row = $sumInvoices($date, $date);
            $weeklyRows[] = [
                'ngay' => $date->toDateString(),
                'tong_doanh_thu' => (float) (clone $row)->sum('tongtien'),
                'so_hoa_don' => (int) (clone $row)->count(),
            ];
        }

        $monthEnd = $thang->copy()->endOfMonth();
        $monthInvoices = $sumInvoices($thang, $monthEnd);
        $monthTotal = (float) (clone $monthInvoices)->sum('tongtien');
        $monthCount = (int) (clone $monthInvoices)->count();

        $yearStart = Carbon::create($nam, 1, 1)->startOfDay();
        $yearEnd = $yearStart->copy()->endOfYear();
        $yearInvoices = $sumInvoices($yearStart, $yearEnd);
        $yearTotal = (float) (clone $yearInvoices)->sum('tongtien');
        $yearCount = (int) (clone $yearInvoices)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'ngay' => $ngay->toDateString(),
                'tong_doanh_thu_ngay' => $dayTotal,
                'so_hoa_don_ngay' => $dayCount,
                'so_ca_kham' => (int) DB::table('phieu_kham')->whereDate('ngaykham', $ngay->toDateString())->count(),
                'theo_loai_chi_phi_ngay' => $dayCategories,
                'tuan_tu_ngay' => $tuan->toDateString(),
                'tuan_den_ngay' => $weekEnd->toDateString(),
                'tong_doanh_thu_tuan' => $weekTotal,
                'so_hoa_don_tuan' => $weekCount,
                'theo_ngay_trong_tuan' => $weeklyRows,
                'thang' => $thang->format('Y-m'),
                'tong_doanh_thu_thang' => $monthTotal,
                'so_hoa_don_thang' => $monthCount,
                'theo_loai_chi_phi_thang' => $categoryTotals($thang, $monthEnd),
                'nam' => $nam,
                'tong_doanh_thu_nam' => $yearTotal,
                'so_hoa_don_nam' => $yearCount,
                'theo_loai_chi_phi_nam' => $categoryTotals($yearStart, $yearEnd),
            ],
        ]);
    }
}
