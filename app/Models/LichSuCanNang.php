<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuCanNang extends Model
{
    protected $table = 'lich_su_can_nang';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['pet_id', 'cannang', 'ngaydo', 'nguoido', 'ghichu'];

    protected $casts = [
        'ngaydo' => 'datetime',
        'cannang' => 'decimal:2',
    ];
}
