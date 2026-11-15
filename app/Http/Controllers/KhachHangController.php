<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KhachHangController extends Controller
{
    public function timChuNuoi(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);
        $owner = DB::table('chu_nuoi')->where('phone', trim($data['phone']))->first();
        if (!$owner) return response()->json(['status' => 'success', 'found' => false, 'data' => null]);
        $pets = DB::table('thu_cung')->where('owner_id', $owner->owner_id)
            ->select('pet_id','mathucung','ten_thucung','loai','giong','gioitinh','ngaysinh','trangthai')->orderBy('pet_id')->get();
        return response()->json(['status'=>'success','found'=>true,'data'=>['owner'=>$owner,'pets'=>$pets]]);
    }

    public function dichVuSpa()
    {
        $items = DB::table('kho_hang')->where('danhmuc','DICH_VU_SPA')
            ->select('item_id','ten_sanpham','dongia')->orderBy('ten_sanpham')->get();
        return response()->json(['status'=>'success','data'=>$items]);
    }

    public function datLich(Request $request)
    {
        $data = $request->validate([
            'phone'=>'required|string|max:20',
            'owner_id'=>'nullable|integer|exists:chu_nuoi,owner_id',
            'owner_name'=>'required_without:owner_id|string|max:100',
            'address'=>'nullable|string|max:255',
            'email'=>'nullable|email|max:100',
            'pet_id'=>'nullable|integer|exists:thu_cung,pet_id',
            'new_pet'=>'nullable|boolean',
            'pet_name'=>'required_if:new_pet,1|string|max:50',
            'species'=>'required_if:new_pet,1|string|max:30',
            'breed'=>'nullable|string|max:50',
            'gender'=>'nullable|in:DUC,CAI,CHUA_XAC_DINH',
            'dob'=>'nullable|date',
            'service_type'=>['required',Rule::in(['MEDICAL','SPA','BOTH'])],
            'appointment_date'=>'required|date|after_or_equal:today',
            'shift'=>['required',Rule::in(['SANG','CHIEU'])],
            'preferred_time'=>'required|date_format:H:i',
            'medical_note'=>'nullable|string|max:2000',
            'spa_services'=>'required_if:service_type,SPA,BOTH|array|min:1',
            'spa_services.*'=>'integer|distinct|exists:kho_hang,item_id',
        ]);

        $time = $data['preferred_time'];
        if (($data['shift']==='SANG' && ($time<'07:00' || $time>'11:00')) ||
            ($data['shift']==='CHIEU' && ($time<'14:00' || $time>'17:00'))) {
            return response()->json(['message'=>'Giờ mong muốn không nằm trong ca đã chọn.'],422);
        }

        return DB::transaction(function () use ($data) {
            $phone = trim($data['phone']);
            $owner = DB::table('chu_nuoi')->where('phone',$phone)->lockForUpdate()->first();
            if (!empty($data['owner_id'])) {
                $owner = DB::table('chu_nuoi')->where('owner_id',$data['owner_id'])->where('phone',$phone)->lockForUpdate()->first();
                if (!$owner) return response()->json(['message'=>'Thông tin chủ nuôi không khớp số điện thoại.'],422);
            }
            if (!$owner) {
                $ownerId = DB::table('chu_nuoi')->insertGetId([
                    'ho_ten'=>trim($data['owner_name']),'phone'=>$phone,
                    'diachi'=>$data['address']??null,'email'=>$data['email']??null,'ngaytao'=>now()
                ]);
            } else {
                $ownerId = $owner->owner_id;
            }

            if (!empty($data['new_pet'])) {
                $petId = DB::table('thu_cung')->insertGetId([
                    'mathucung'=>'TMP'.Str::upper(Str::random(10)),'owner_id'=>$ownerId,'ten_thucung'=>trim($data['pet_name']),
                    'loai'=>$data['species'],'giong'=>$data['breed']??null,'gioitinh'=>$data['gender']??'CHUA_XAC_DINH',
                    'ngaysinh'=>$data['dob']??null,'trangthai'=>'NGOAI_TRU','ngaytao'=>now()
                ]);
                DB::table('thu_cung')->where('pet_id',$petId)->update(['mathucung'=>sprintf('TC%04d',$petId)]);
            } else {
                $pet = DB::table('thu_cung')->where('pet_id',$data['pet_id']??0)->where('owner_id',$ownerId)->first();
                if (!$pet) return response()->json(['message'=>'Vui lòng chọn thú cưng thuộc đúng chủ nuôi.'],422);
                $petId = $pet->pet_id;
            }

            $type = $data['service_type'];
            $spaIds = in_array($type,['SPA','BOTH'],true) ? array_values(array_unique($data['spa_services']??[])) : [];
            if ($spaIds) {
                $valid = DB::table('kho_hang')->where('danhmuc','DICH_VU_SPA')->whereIn('item_id',$spaIds)->count();
                if ($valid !== count($spaIds)) return response()->json(['message'=>'Danh sách dịch vụ Spa không hợp lệ.'],422);
            }

            $code = 'PC'.now()->format('ymd').Str::upper(Str::random(5));
            $id = DB::table('lich_hen_khach')->insertGetId([
                'ma_lich_hen'=>$code,'owner_id'=>$ownerId,'pet_id'=>$petId,'loai_dich_vu'=>$type,
                'ngay_hen'=>$data['appointment_date'],'ca_hen'=>$data['shift'],'gio_mong_muon'=>$time,
                'ghi_chu_kham'=>$data['medical_note']??null,'dich_vu_spa'=>json_encode($spaIds),
                'trang_thai'=>'CHO_XAC_NHAN','created_at'=>now(),'updated_at'=>now()
            ]);

            $queueId = null;
            if (in_array($type,['MEDICAL','BOTH'],true)) {
                $note = trim($data['medical_note']??'');
                $note = 'Đặt lịch trực tuyến '.$code.' · Ngày '.$data['appointment_date'].' · Ca '.($data['shift']==='SANG'?'sáng':'chiều').' · Giờ mong muốn '.$time.($note ? ' · Ghi chú: '.$note : '');
                $active = DB::table('hang_doi_kham')->where('pet_id',$petId)->whereIn('trangthai',['CHO_KHAM','DANG_KHAM'])->first();
                if ($active) {
                    DB::table('hang_doi_kham')->where('queue_id',$active->queue_id)->update(['ghi_chu'=>$note,'updated_at'=>now()]);
                    $queueId = $active->queue_id;
                } else {
                    $queueId = DB::table('hang_doi_kham')->insertGetId([
                        'pet_id'=>$petId,'created_by'=>null,'ghi_chu'=>$note,'trangthai'=>'CHO_KHAM','created_at'=>now(),'updated_at'=>now()
                    ]);
                }
                DB::table('lich_hen_khach')->where('id',$id)->update(['queue_id'=>$queueId]);
            }

            return response()->json(['status'=>'success','message'=>'Đã tiếp nhận yêu cầu đặt lịch. Vui lòng lưu mã lịch hẹn để tra cứu hoặc chỉnh sửa.','data'=>[
                'ma_lich_hen'=>$code,'owner_name'=>DB::table('chu_nuoi')->where('owner_id',$ownerId)->value('ho_ten'),
                'pet_name'=>DB::table('thu_cung')->where('pet_id',$petId)->value('ten_thucung'),'queue_id'=>$queueId
            ]],201);
        });
    }

    public function traCuu(Request $request)
    {
        $data=$request->validate(['phone'=>'required|string|max:20','code'=>'required|string|max:30']);
        $booking=DB::table('lich_hen_khach as b')->join('chu_nuoi as o','o.owner_id','=','b.owner_id')
            ->join('thu_cung as p','p.pet_id','=','b.pet_id')->where('o.phone',trim($data['phone']))
            ->where('b.ma_lich_hen',strtoupper(trim($data['code'])))
            ->select('b.*','o.ho_ten as owner_name','o.phone','o.email','o.diachi','p.ten_thucung','p.loai','p.giong','p.pet_id')->first();
        if (!$booking) return response()->json(['message'=>'Không tìm thấy lịch hẹn phù hợp với số điện thoại và mã lịch.'],404);
        $booking->spa_services = json_decode($booking->dich_vu_spa,true) ?: [];
        return response()->json(['status'=>'success','data'=>$booking]);
    }

    public function capNhat(Request $request)
    {
        $data=$request->validate([
            'phone'=>'required|string|max:20','code'=>'required|string|max:30',
            'appointment_date'=>'required|date|after_or_equal:today','shift'=>['required',Rule::in(['SANG','CHIEU'])],
            'preferred_time'=>'required|date_format:H:i','medical_note'=>'nullable|string|max:2000',
            'spa_services'=>'nullable|array','spa_services.*'=>'integer|distinct|exists:kho_hang,item_id'
        ]);
        $time=$data['preferred_time'];
        if (($data['shift']==='SANG' && ($time<'07:00'||$time>'11:00')) || ($data['shift']==='CHIEU' && ($time<'14:00'||$time>'17:00')))
            return response()->json(['message'=>'Giờ mong muốn không nằm trong ca đã chọn.'],422);
        $booking=DB::table('lich_hen_khach as b')->join('chu_nuoi as o','o.owner_id','=','b.owner_id')
            ->where('o.phone',trim($data['phone']))->where('b.ma_lich_hen',strtoupper(trim($data['code'])))
            ->select('b.*')->first();
        if (!$booking) return response()->json(['message'=>'Không tìm thấy lịch hẹn.'],404);
        if ($booking->trang_thai!=='CHO_XAC_NHAN') return response()->json(['message'=>'Lịch hẹn đã được xử lý, không thể chỉnh sửa trực tuyến.'],409);
        $spaIds=in_array($booking->loai_dich_vu,['SPA','BOTH'],true)?($data['spa_services']??json_decode($booking->dich_vu_spa,true)?:[]):[];
        if ($spaIds && DB::table('kho_hang')->where('danhmuc','DICH_VU_SPA')->whereIn('item_id',$spaIds)->count()!==count($spaIds))
            return response()->json(['message'=>'Danh sách dịch vụ Spa không hợp lệ.'],422);
        DB::table('lich_hen_khach')->where('id',$booking->id)->update([
            'ngay_hen'=>$data['appointment_date'],'ca_hen'=>$data['shift'],'gio_mong_muon'=>$time,
            'ghi_chu_kham'=>$data['medical_note']??null,'dich_vu_spa'=>json_encode($spaIds),'updated_at'=>now()
        ]);
        if ($booking->queue_id) {
            $note='Đặt lịch trực tuyến '.$booking->ma_lich_hen.' · Ngày '.$data['appointment_date'].' · Ca '.($data['shift']==='SANG'?'sáng':'chiều').' · Giờ mong muốn '.$time.
                (!empty($data['medical_note'])?' · Ghi chú: '.$data['medical_note']:'');
            DB::table('hang_doi_kham')->where('queue_id',$booking->queue_id)->update(['ghi_chu'=>$note,'updated_at'=>now()]);
        }
        return response()->json(['status'=>'success','message'=>'Đã cập nhật lịch hẹn.']);
    }

    /** Danh sách lịch hẹn để lễ tân/quản trị tiếp nhận. */
    public function danhSachLichHen()
    {
        $rows = DB::table('lich_hen_khach as b')
            ->join('chu_nuoi as o','o.owner_id','=','b.owner_id')
            ->join('thu_cung as p','p.pet_id','=','b.pet_id')
            ->leftJoin('hang_doi_kham as q','q.queue_id','=','b.queue_id')
            ->select('b.*','o.ho_ten as owner_name','o.phone','p.ten_thucung','p.loai','p.giong',
                'q.trangthai as queue_status')
            ->orderByRaw("CASE WHEN b.trang_thai='CHO_XAC_NHAN' THEN 0 WHEN b.trang_thai='DA_XAC_NHAN' THEN 1 ELSE 2 END")
            ->orderBy('b.ngay_hen')->orderBy('b.gio_mong_muon')
            ->limit(300)->get();
        foreach ($rows as $row) {
            $row->spa_services = json_decode($row->dich_vu_spa,true) ?: [];
            $row->spa_service_names = $row->spa_services
                ? DB::table('kho_hang')->whereIn('item_id',$row->spa_services)->pluck('ten_sanpham')->all()
                : [];
        }
        return response()->json(['status'=>'success','data'=>$rows]);
    }

    /** Lễ tân xác nhận, hoàn tất hoặc hủy yêu cầu đặt lịch. */
    public function capNhatTrangThai(Request $request, int $id)
    {
        $data=$request->validate(['trang_thai'=>'required|in:DA_XAC_NHAN,DA_HOAN_THANH,DA_HUY']);
        return DB::transaction(function () use ($id,$data) {
            $booking=DB::table('lich_hen_khach')->where('id',$id)->lockForUpdate()->first();
            abort_if(!$booking,404,'Không tìm thấy lịch hẹn.');
            if (in_array($booking->trang_thai,['DA_HOAN_THANH','DA_HUY'],true))
                return response()->json(['message'=>'Lịch hẹn đã kết thúc, không thể thay đổi trạng thái.'],409);
            if ($data['trang_thai']==='DA_HOAN_THANH' && $booking->queue_id) {
                $queue=DB::table('hang_doi_kham')->where('queue_id',$booking->queue_id)->first();
                if ($queue && $queue->trangthai!=='DA_KHAM')
                    return response()->json(['message'=>'Chỉ hoàn tất lịch khám sau khi bác sĩ hoàn thành lượt khám.'],409);
            }
            if ($data['trang_thai']==='DA_HUY' && $booking->queue_id) {
                $queue=DB::table('hang_doi_kham')->where('queue_id',$booking->queue_id)->lockForUpdate()->first();
                if ($queue && $queue->trangthai==='DANG_KHAM')
                    return response()->json(['message'=>'Thú cưng đang được khám, không thể hủy lịch.'],409);
                if ($queue && $queue->trangthai==='CHO_KHAM')
                    DB::table('hang_doi_kham')->where('queue_id',$queue->queue_id)->delete();
            }
            DB::table('lich_hen_khach')->where('id',$id)->update(['trang_thai'=>$data['trang_thai'],'updated_at'=>now()]);
            return response()->json(['status'=>'success','message'=>'Đã cập nhật trạng thái lịch hẹn.']);
        });
    }
}
