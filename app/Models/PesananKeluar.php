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
        'packaging_id',
        'nama_pesanan',
        'jumlah_gram_pesanan',
        'jumlah_pcs',
        'harga_jual_pesanan',
        'tanggal_pesanan',
        'total_pembayaran',
        'status_pembayaran',
        'status_pengiriman',
        'metode_pembayaran',
        'nominal_dp',
        'sisa_pembayaran',
        'tanggal_bayar_dp',
        'tanggal_pelunasan',
        'batas_waktu_pembayaran',
        'status_pengambilan'
    ];

    protected $casts = [
        'tanggal_pesanan' => 'date',
        'tanggal_bayar_dp' => 'date',
        'tanggal_pelunasan' => 'date',
        'batas_waktu_pembayaran' => 'date',
    ];

    public function roastedBean()
    {
        return $this->belongsTo(RoastedBean::class, 'roasted_bean_id');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

