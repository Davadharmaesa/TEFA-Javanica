<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PesananKeluar extends Model
{
    use HasFactory;

    protected $table = 'pesanan_keluar';

    protected $fillable = [
        'user_id',
        'roasted_bean_id',
        'nama_pesanan',
        'jumlah_gram_pesanan',
        'harga_jual_pesanan',
        'tanggal_pesanan',
        'total_pembayaran',
        'status_pembayaran',
        'status_pengiriman'
    ];

    public function roastedBean()
    {
        return $this->belongsTo(RoastedBean::class, 'roasted_bean_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

