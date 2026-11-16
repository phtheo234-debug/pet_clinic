<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DuLieuMauPetClinicSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Moi danh muc kho co 5 san pham mau.
            $nhomKho = [
                'THUOC' => [
                    ['Thuoc sat trung Povidine', 'Chai', 45000, 30],
                    ['Thuoc tẩy giun cho cho', 'Hop', 85000, 25],
                    ['Thuoc nho mat', 'Lo', 55000, 20],
                    ['Dung dich ve sinh tai', 'Chai', 65000, 18],
                    ['Men tieu hoa thu cung', 'Hop', 95000, 15],
                ],
                'VAC_XIN' => [
                    ['Vac xin 5 benh cho', 'Mui', 180000, 40],
                    ['Vac xin 7 benh cho', 'Mui', 220000, 35],
                    ['Vac xin 3 benh meo', 'Mui', 160000, 30],
                    ['Vac xin dai', 'Mui', 90000, 50],
                    ['Vac xin care-parvo', 'Mui', 200000, 25],
                ],
                'THUC_AN' => [
                    ['Hat cho truong thanh', 'Kg', 120000, 40],
                    ['Hat meo truong thanh', 'Kg', 135000, 35],
                    ['Pate cho', 'Hop', 28000, 50],
                    ['Pate meo', 'Hop', 25000, 50],
                    ['Sua bot cho meo con', 'Hop', 165000, 15],
                ],
                'DO_DUNG' => [
                    ['Day dat cho', 'Cai', 75000, 20],
                    ['Vong co meo', 'Cai', 45000, 25],
                    ['Binh nuoc thu cung', 'Cai', 60000, 18],
                    ['Khay ve sinh meo', 'Cai', 145000, 12],
                    ['Lo long van chuyen', 'Cai', 320000, 8],
                ],
                'DICH_VU_SPA' => [
                    ['Tam ve sinh co ban', 'Luot', 120000, 999],
                    ['Tam tri ve sinh da', 'Luot', 180000, 999],
                    ['Cat tia long', 'Luot', 150000, 999],
                    ['Ve sinh tai va mat', 'Luot', 60000, 999],
                    ['Cat mong', 'Luot', 50000, 999],
                ],
            ];

            foreach ($nhomKho as $danhMuc => $sanPhams) {
                foreach ($sanPhams as $index => [$ten, $donVi, $donGia, $soLuong]) {
                    $ma = $danhMuc . '-M-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                    DB::table('kho_hang')->updateOrInsert(
                        ['masanpham' => $ma],
                        [
                            'ten_sanpham' => $ten,
                            'danhmuc' => $danhMuc,
                            'donvitinh' => $donVi,
                            'dongia' => $donGia,
                            'soluongton' => $soLuong,
                        ]
                    );
                }
            }

            // Them 5 dich vu Spa theo dung ma va cau truc ma dich vu cua API.
            $dichVuSpa = [
                ['SPA-20261116-130001', 'Tam va say long thu cung', 120000],
                ['SPA-20261116-130002', 'Tam tri viem da va ngua', 180000],
                ['SPA-20261116-130003', 'Cat tia long tao kieu', 150000],
                ['SPA-20261116-130004', 'Ve sinh tai, mat va rang', 90000],
                ['SPA-20261116-130005', 'Cat mong va cham soc ban chan', 60000],
            ];

            foreach ($dichVuSpa as [$ma, $ten, $gia]) {
                DB::table('kho_hang')->updateOrInsert(
                    ['masanpham' => $ma],
                    [
                        'ten_sanpham' => $ten,
                        'danhmuc' => 'DICH_VU_SPA',
                        'donvitinh' => '',
                        'dongia' => $gia,
                        'soluongton' => 0,
                    ]
                );
            }

            // 2. Them 5 danh muc phau thuat.
            $phauThuats = [
                ['PT-M-001', 'Triet san cho', 650000],
                ['PT-M-002', 'Triet san meo', 500000],
                ['PT-M-003', 'Mo lay di vat duong tieu hoa', 1200000],
                ['PT-M-004', 'Khau vet thuong phan mem', 450000],
                ['PT-M-005', 'Phau thuat cat bo khoi u nho', 1500000],
            ];

            foreach ($phauThuats as [$ma, $ten, $gia]) {
                DB::table('danh_muc_phau_thuat')->updateOrInsert(
                    ['ma_phauthuat' => $ma],
                    ['ten_phauthuat' => $ten, 'gia' => $gia, 'trangthai' => true,
                        'created_at' => now(), 'updated_at' => now()]
                );
            }

            // 3. Them 5 chuong luu tru dang trong.
            $chuongs = [
                ['LN-M-001', 'Long thuong 01', 'THUONG', 2, 25.00, null],
                ['LN-M-002', 'Long thuong 02', 'THUONG', 2, 25.00, null],
                ['LN-M-003', 'Long cach ly 01', 'CACH_LY', 1, 15.00, null],
                ['LN-M-004', 'Long cap cuu ICU 01', 'CAP_CUU_ICU', 1, 20.00, null],
                ['LN-M-005', 'Long thuong 03', 'THUONG', 3, 35.00, null],
            ];

            foreach ($chuongs as [$ma, $ten, $loai, $sucChua, $taiTrong, $loaiApDung]) {
                DB::table('long_nuoi')->updateOrInsert(
                    ['malong' => $ma],
                    [
                        'ten_long' => $ten,
                        'loailong' => $loai,
                        'succhua' => $sucChua,
                        'soluonghientai' => 0,
                        'tai_trong_toi_da' => $taiTrong,
                        'loai_ap_dung' => $loaiApDung,
                        'trangthai' => 'CON_TRONG',
                    ]
                );
            }

            // 4. Tao 5 chu nuoi, 5 ho so thu cung va 5 luot tiep nhan.
            $thuCungs = [
                ['TN-M-001', 'Nguyen Van An', '0901000001', 'Cho', 'Poodle', 'Milo', 'DUC', 'Cho kham tong quat'],
                ['TN-M-002', 'Tran Thi Binh', '0901000002', 'Meo', 'Anh long ngan', 'Luna', 'CAI', 'Bo an, can kiem tra'],
                ['TN-M-003', 'Le Minh Chau', '0901000003', 'Cho', 'Corgi', 'Bong', 'DUC', 'Kiem tra da va long'],
                ['TN-M-004', 'Pham Thu Dung', '0901000004', 'Meo', 'Meo ta', 'Mun', 'CAI', 'Theo doi suc khoe'],
                ['TN-M-005', 'Hoang Quoc Dat', '0901000005', 'Cho', 'Golden Retriever', 'Bap', 'DUC', 'Kham dinh ky'],
            ];

            foreach ($thuCungs as [$maThuCung, $chu, $phone, $loai, $giong, $tenThuCung, $gioiTinh, $ghiChu]) {
                DB::table('chu_nuoi')->updateOrInsert(
                    ['phone' => $phone],
                    [
                        'ho_ten' => $chu,
                        'diachi' => 'Ha Noi',
                        'email' => null,
                        'ngaytao' => now(),
                    ]
                );

                $ownerId = DB::table('chu_nuoi')->where('phone', $phone)->value('owner_id');

                DB::table('thu_cung')->updateOrInsert(
                    ['mathucung' => $maThuCung],
                    [
                        'owner_id' => $ownerId,
                        'cage_id' => null,
                        'ten_thucung' => $tenThuCung,
                        'loai' => $loai,
                        'giong' => $giong,
                        'cannang' => 4.50,
                        'gioitinh' => $gioiTinh,
                        'ngaysinh' => '2023-01-15',
                        'trangthai' => 'NGOAI_TRU',
                        'ngaytao' => now(),
                    ]
                );

                $petId = DB::table('thu_cung')->where('mathucung', $maThuCung)->value('pet_id');

                DB::table('hang_doi_kham')->updateOrInsert(
                    [
                        'pet_id' => $petId,
                        'ghi_chu_tiep_nhan' => $ghiChu,
                    ],
                    [
                        'created_by' => null,
                        'ghi_chu' => $ghiChu,
                        'trangthai' => 'CHO_KHAM',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}
