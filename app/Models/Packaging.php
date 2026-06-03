<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipe',
        'nama_kemasan',
        'kapasitas_gram',
        'satuan',
        'harga_satuan',
        'harga_total_pembelian',
        'jumlah_qty_pembelian',
        'stok_pcs',
    ];
}
