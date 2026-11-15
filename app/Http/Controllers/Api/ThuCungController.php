<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThuCungController extends Controller
{
    private function query()
    {
        // Subquery lấy chỉ số cân nặng mới nhất từ lich_su_can_nang (BR-04)
        $latestWeight = DB::table('lich_su_can_nang as wl1')
            ->select('wl1.pet_id', 'wl1.cannang')
            ->whereRaw('wl1.id = (SELECT MAX(wl2.id) FROM lich_su_can_nang wl2 WHERE wl2.pet_id = wl1.pet_id)');

        $latestExam = DB::table('phieu_kham as mr1')->select('mr1.pet_id', 'mr1.record_id', 'mr1.trangthai as exam_status', 'mr1.ngaykham as exam_date', 'mr1.invoice_id as exam_invoice_id')->whereRaw('mr1.record_id = (SELECT MAX(mr2.record_id) FROM phieu_kham mr2 WHERE mr2.pet_id = mr1.pet_id)');

        $latestQueue = DB::table('hang_doi_kham as mq1')
            ->select('mq1.pet_id', 'mq1.trangthai as queue_status', 'mq1.ghi_chu as queue_note',
                'mq1.ghi_chu_tiep_nhan as intake_note', 'mq1.created_at as queue_created_at')
            ->whereRaw('mq1.queue_id = (SELECT MAX(mq2.queue_id) FROM hang_doi_kham mq2 WHERE mq2.pet_id = mq1.pet_id)');

        // Lịch hẹn khám bệnh gần nhất còn hiệu lực được ưu tiên lên đầu danh sách hồ sơ.
        $latestBooking = DB::table('lich_hen_khach as b1')
            ->select('b1.pet_id', 'b1.ngay_hen', 'b1.gio_mong_muon', 'b1.ghi_chu_kham',
                'b1.loai_dich_vu', 'b1.trang_thai as booking_status')
            ->whereIn('b1.loai_dich_vu', ['MEDICAL', 'BOTH'])
            ->whereIn('b1.trang_thai', ['CHO_XAC_NHAN', 'DA_XAC_NHAN'])
            ->whereRaw('b1.id = (SELECT MAX(b2.id) FROM lich_hen_khach b2
                WHERE b2.pet_id = b1.pet_id
                AND b2.loai_dich_vu IN (\'MEDICAL\', \'BOTH\')
                AND b2.trang_thai IN (\'CHO_XAC_NHAN\', \'DA_XAC_NHAN\'))');

        return DB::table('thu_cung')
            ->leftJoinSub($latestExam, 'latest_exam', function ($join) { $join->on('thu_cung.pet_id', '=', 'latest_exam.pet_id'); })
            ->leftJoinSub($latestQueue, 'latest_queue', function ($join) { $join->on('thu_cung.pet_id', '=', 'latest_queue.pet_id'); })
            ->leftJoinSub($latestBooking, 'latest_booking', function ($join) { $join->on('thu_cung.pet_id', '=', 'latest_booking.pet_id'); })
            ->leftJoin('chu_nuoi', 'chu_nuoi.owner_id', '=', 'thu_cung.owner_id')
            ->leftJoinSub($latestWeight, 'latest_weight', function ($join) {
                $join->on('thu_cung.pet_id', '=', 'latest_weight.pet_id');
            })
            ->select(
                'thu_cung.*',
                'chu_nuoi.ho_ten as owner_name',
                'chu_nuoi.phone as owner_phone',
                'chu_nuoi.diachi as owner_address',
                'chu_nuoi.email as owner_email',
                'latest_weight.cannang as current_weight',
                'latest_exam.record_id as record_id', 'latest_exam.exam_status as trangthai_kham',
                'latest_exam.exam_date as ngaykham_gannhat', 'latest_exam.exam_invoice_id as mahoadon_kham',
                'latest_queue.queue_status as queue_status', 'latest_queue.queue_note as queue_note',
                'latest_queue.intake_note as intake_note', 'latest_queue.queue_created_at as queue_created_at',
                'latest_booking.ngay_hen as booking_date', 'latest_booking.gio_mong_muon as booking_time',
                'latest_booking.ghi_chu_kham as booking_note', 'latest_booking.loai_dich_vu as booking_service_type',
                'latest_booking.booking_status as booking_status'
            );
    }

    private function findOrFail($id)
    {
        $pet = $this->query()->where('thu_cung.pet_id', $id)->first();
        abort_if(!$pet, 404, 'Không tìm thấy thú cưng');
        return $pet;
    }

    private function format($p): array
    {
        return [
            'id'             => $p->pet_id,
            'mathucung'      => $p->mathucung,
            'name'           => $p->ten_thucung,
            'species'        => $p->loai,
            'breed'          => $p->giong,
            'gender'         => $p->gioitinh,
            'dob'            => $p->ngaysinh,
            'weight'         => $p->current_weight ? (float)$p->current_weight : null,
            'owner_id'       => $p->owner_id,
            'cage_id'        => $p->cage_id,
            'trangthai' => $p->trangthai,
            'record_id' => $p->record_id ?? null,
            'trangthai_kham' => ($p->queue_status ?? null) === 'DANG_KHAM'
                ? 'DANG_KHAM'
                : (($p->queue_status ?? null) === 'CHO_KHAM'
                    ? 'CHO_KHAM'
                    : (($p->queue_status ?? null) === 'DA_KHAM'
                        ? 'DA_KHAM'
                        : (!empty($p->record_id) ? 'DA_KHAM' : 'KHONG_KHAM'))),
            'ngaykham_gannhat' => $p->ngaykham_gannhat ?? null,
            'kham_da_thanh_toan' => !empty($p->mahoadon_kham),
            'trangthai_hangdoi' => $p->queue_status ?? null,
            'dang_cho_kham' => in_array($p->queue_status ?? null, ['CHO_KHAM', 'DANG_KHAM'], true),
            'ghi_chu_cho_kham' => $p->queue_note ?? null,
            'ghi_chu_tiep_nhan' => $p->intake_note ?? null,
            'ghi_chu_dat_lich' => $p->booking_note ?? null,
            'ngay_hen_kham' => $p->booking_date ?? null,
            'gio_hen_kham' => $p->booking_time ?? null,
            'loai_dich_vu_dat_lich' => $p->booking_service_type ?? null,
            'owner'          => [
                'id'    => $p->owner_id,
                'name'  => $p->owner_name,
                'phone' => $p->owner_phone,
                'address' => $p->owner_address ?? null,
                'email' => $p->owner_email ?? null
            ],
        ];
    }

    /** Danh sách chủ nuôi dùng cho bảng quản lý và dropdown. */
    public function owners()
    {
        $owners = DB::table('chu_nuoi')
            ->select(
                'chu_nuoi.owner_id',
                'chu_nuoi.ho_ten',
                'chu_nuoi.phone',
                'chu_nuoi.diachi',
                'chu_nuoi.email'
            )
            ->selectSub(function ($query) {
                $query->from('thu_cung')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('thu_cung.owner_id', 'chu_nuoi.owner_id');
            }, 'pet_count')
            ->orderByDesc('chu_nuoi.owner_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $owners,
        ], 200);
    }

    // Lấy danh sách chủ nuôi đổ vào Dropdown chọn Chủ nuôi ở Frontend
    public function chu_nuoi()
    {
        $chu_nuoi = DB::table('chu_nuoi')
            ->leftJoin('thu_cung', 'thu_cung.owner_id', '=', 'chu_nuoi.owner_id')
            ->select('chu_nuoi.owner_id', 'chu_nuoi.ho_ten', 'chu_nuoi.phone', 'chu_nuoi.diachi', 'chu_nuoi.email', DB::raw('COUNT(thu_cung.pet_id) as pet_count'))
            ->groupBy('chu_nuoi.owner_id', 'chu_nuoi.ho_ten', 'chu_nuoi.phone', 'chu_nuoi.diachi', 'chu_nuoi.email')
            ->orderByDesc('chu_nuoi.owner_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $chu_nuoi
        ], 200);
    }

    public function index()
    {
        $thu_cung = $this->query()
            ->orderByRaw("CASE WHEN latest_booking.pet_id IS NOT NULL THEN 0 WHEN latest_queue.queue_status IN ('CHO_KHAM','DANG_KHAM') THEN 1 ELSE 2 END")
            ->orderBy('latest_booking.ngay_hen')
            ->orderBy('latest_booking.gio_mong_muon')
            ->orderByRaw("CASE WHEN latest_queue.queue_status = 'DANG_KHAM' THEN 0 WHEN latest_queue.queue_status = 'CHO_KHAM' AND (NULLIF(TRIM(latest_queue.queue_note), '') IS NOT NULL OR NULLIF(TRIM(latest_queue.intake_note), '') IS NOT NULL) THEN 1 WHEN latest_queue.queue_status = 'CHO_KHAM' THEN 2 WHEN latest_queue.queue_status = 'DA_KHAM' THEN 3 ELSE 4 END")
            ->orderBy('latest_queue.queue_created_at')
            ->orderBy('thu_cung.ngaytao')
            ->orderBy('thu_cung.pet_id')
            ->get();
        return response()->json([
            'status' => 'success',
            'data'   => $thu_cung->map(fn ($p) => $this->format($p))->values()
        ], 200);
    }

    public function store(Request $request)
    {
        $hasOwnerId = $request->filled('owner_id');

        $rules = [
            'name'           => 'required|string|max:50',
            'species'        => 'required|string|max:30',
            'breed'          => 'nullable|string|max:50',
            'gender'         => 'nullable|in:DUC,CAI,CHUA_XAC_DINH',
            'dob'            => 'nullable|date',
            'weight'         => 'required|numeric|min:0.1|max:999',
            'kham_benh'     => 'sometimes|boolean',
            'ghi_chu_kham'   => 'nullable|string|max:2000',
        ];

        if ($hasOwnerId) {
            $rules['owner_id'] = 'required|integer|min:1|exists:chu_nuoi,owner_id';
        } else {
            $rules['ho_ten'] = 'required|string|max:100';
            $rules['phone']  = 'required|string|max:20';
            $rules['diachi'] = 'nullable|string|max:255';
            $rules['email']  = 'nullable|email|max:100';
        }

        $v = $request->validate($rules, [
            'owner_id.required'     => 'Vui lòng chọn chủ nuôi.',
            'owner_id.integer'      => 'ID chủ nuôi phải là số nguyên.',
            'owner_id.min'          => 'ID chủ nuôi phải lớn hơn 0.',
            'owner_id.exists'       => 'Chủ nuôi không tồn tại trong hệ thống.',
            'ho_ten.required'       => 'Vui lòng nhập họ tên chủ nuôi.',
            'phone.required'        => 'Vui lòng nhập số điện thoại chủ nuôi.',
            'name.required'         => 'Vui lòng nhập tên thú cưng.',
            'species.required'      => 'Vui lòng chọn loài thú cưng.',
            'weight.required'       => 'Vui lòng nhập cân nặng ban đầu.',
            'weight.min'            => 'Cân nặng phải tối thiểu 0.1 kg.',
        ]);

        try {
            return DB::transaction(function () use ($request, $v, $hasOwnerId) {
                // 1. Lấy hoặc tự động tạo mới Chủ nuôi nếu tiếp nhận khách mới
                if ($hasOwnerId) {
                    $ownerId = $v['owner_id'];
                } else {
                    $phone = trim($v['phone']);
                    $existingOwner = DB::table('chu_nuoi')->where('phone', $phone)->first();

                    if ($existingOwner) {
                        // Số điện thoại là khóa nhận diện chủ nuôi; hồ sơ đã có thì chỉ liên kết,
                        // tuyệt đối không ghi đè thông tin chủ nuôi từ form tiếp nhận thú cưng.
                        $ownerId = $existingOwner->owner_id;
                    } else {
                        $ownerId = DB::table('chu_nuoi')->insertGetId([
                            'ho_ten'  => trim($v['ho_ten']),
                            'phone'   => $phone,
                            'diachi'  => $v['diachi'] ?? null,
                            'email'   => $v['email'] ?? null,
                            'ngaytao' => now(),
                        ]);
                    }
                }

                // 2. Khởi tạo hồ sơ thú cưng
                $id = DB::table('thu_cung')->insertGetId([
                    'mathucung'   => 'TMP' . uniqid(),
                    'owner_id'    => $ownerId,
                    'ten_thucung' => $v['name'],
                    'loai'        => $v['species'],
                    'giong'       => $v['breed'] ?? null,
                    'gioitinh'    => $v['gender'] ?? 'CHUA_XAC_DINH',
                    'ngaysinh'    => $v['dob'] ?? null,
                    'trangthai'   => 'NGOAI_TRU',
                ]);

                // 3. Tạo mã định danh duy nhất TC0001 (BR-01)
                DB::table('thu_cung')->where('pet_id', $id)->update(['mathucung' => sprintf('TC%04d', $id)]);

                // 4. Ghi nhận cân nặng khởi tạo vào lịch sử cân nặng.
                DB::table('lich_su_can_nang')->insert([
                    'pet_id'  => $id,
                    'cannang' => $v['weight'],
                    'ngaydo'  => now(),
                    'nguoido' => $request->user()->user_id ?? 1,
                    'ghichu'  => 'Cân nặng khởi tạo hồ sơ',
                ]);

                // Chỉ tạo lượt khám khi nhân viên tích "Khám bệnh" lúc tiếp nhận.
                if ($request->boolean('kham_benh')) {
                    $note = trim((string) ($v['ghi_chu_kham'] ?? ''));
                    DB::table('hang_doi_kham')->insert([
                        'pet_id' => $id,
                        'created_by' => $request->user()->user_id,
                        'ghi_chu' => null,
                        'ghi_chu_tiep_nhan' => $note !== '' ? $note : null,
                        'trangthai' => 'CHO_KHAM',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Thêm hồ sơ thú cưng thành công!',
                    'data'    => $this->format($this->findOrFail($id)),
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi tạo hồ sơ thú cưng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pet = $this->findOrFail($id);

        $medicalHistory = DB::table('phieu_kham as mr')
            ->leftJoin('danh_muc_phau_thuat as s', 's.surgery_id', '=', 'mr.surgery_id')
            ->leftJoin('hoa_don as i', 'i.invoice_id', '=', 'mr.invoice_id')
            ->where('mr.pet_id', $id)
            ->select('mr.record_id', 'mr.ngaykham', 'mr.chandoan', 'mr.huong_xuly', 'mr.trangthai', 'mr.invoice_id', 'i.mahoadon', 's.ten_phauthuat')
            ->orderByDesc('mr.ngaykham')->orderByDesc('mr.record_id')->get();
        $latestExam = $medicalHistory->first();

        return response()->json([
            'status' => 'success',
            'data'   => array_merge($this->format($pet), [
                'medical_history' => $medicalHistory,
                'latest_exam' => $latestExam ? ['record_id' => $latestExam->record_id, 'trangthai' => $latestExam->trangthai, 'ngaykham' => $latestExam->ngaykham] : null,
            ]),
        ], 200);
    }

    /**
     * Chỉ ghi nhận cân nặng mới; không cho sửa thông tin hồ sơ chủ nuôi/thú cưng.
     * Mỗi lần cân đo tạo thêm một dòng lịch sử, không ghi đè dữ liệu cũ.
     */
    public function update(Request $request, $id)
    {
        $this->findOrFail($id);

        if (array_diff(array_keys($request->all()), ['weight'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hồ sơ chủ nuôi và thú cưng là dữ liệu lưu trữ, không được chỉnh sửa tại chức năng này.',
            ], 403);
        }

        $v = $request->validate([
            'weight' => 'required|numeric|min:0.1|max:999',
        ]);

        DB::table('lich_su_can_nang')->insert([
            'pet_id' => $id,
            'cannang' => $v['weight'],
            'ngaydo' => now(),
            'nguoido' => $request->user()->user_id ?? 1,
            'ghichu' => 'Cập nhật cân nặng mới',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã ghi nhận cân nặng mới vào lịch sử.',
            'data' => $this->format($this->findOrFail($id)),
        ], 200);
    }

}
