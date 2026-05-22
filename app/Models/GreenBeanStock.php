<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GreenBeanStock extends Model
{
    use HasFactory;

    protected $table = 'green_bean_stock';

    protected $fillable = [
        'green_bean_id',
        'stok_gram',
        'harga_beli',
        'tanggal_beli',
    ];

    public function greenBean()
    {
        return $this->belongsTo(GreenBean::class);
    }
}
