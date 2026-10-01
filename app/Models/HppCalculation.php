<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppCalculation extends Model
{
    protected $fillable = [
        'roasting_batch_id',
        'biaya_bahan_baku',
        'biaya_listrik',
        'biaya_penyusutan',
        'biaya_gaji',
        'biaya_kemasan',
        'hasil_kemasan_pcs',
        'total_hpp',
        'harga_jual_final',
        'packaging_id',
    ];

    public function roastingBatch()
    {
        return $this->belongsTo(RoastingBatch::class, 'roasting_batch_id');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging_id');
    }
}
