<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoastedBeanStock extends Model
{
    use HasFactory;

    protected $table = 'roasted_bean_stock';

    protected $fillable = [
        'roasted_bean_id',
        'stok_gram',
        'harga_roasting',
        'tanggal_roasting',
    ];

    public function roastedBean()
    {
        return $this->belongsTo(RoastedBean::class);
    }
}
