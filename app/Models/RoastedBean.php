<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoastedBean extends Model
{
    use HasFactory;

    protected $table = 'roasted_bean';

    protected $fillable = [
        'nama_kopi',
        'bentuk_akhir',
        'total_stok',
        'satuan_dasar',
        'tanggal_roasting',
        'tanggal_expire',
        'sisa_stok'
    ];

    public function stocks()
    {
        return $this->hasMany(RoastedBeanStock::class, 'roasted_bean_id');
    }
}

