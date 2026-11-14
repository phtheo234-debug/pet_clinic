<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Long extends Model
{
    protected $table = 'cages';
    protected $primaryKey = 'cage_id';
    public $timestamps = false;

    protected $fillable = [
        'malong',
        'ten_long',
        'loailong',
        'succhua',
        'soluonghientai',
        'trangthai',
    ];

    /**
     * Quan hệ: Một lồng có thể chứa nhiều thú cưng (tối đa theo succhua)
     */
    public function pets()
    {
        return $this->hasMany(ThuCung::class, 'cage_id', 'cage_id');
    }
}