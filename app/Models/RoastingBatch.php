<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoastingBatch extends Model
{
    use HasFactory;

    protected $table = 'roasting_batch';

    protected static function booted()
    {
        static::created(function ($batch) {
            if ($batch->status_batch === 'completed') {
                $hpp = $batch->berat_matang_bersih > 0 ? ($batch->biaya_bahan_baku / $batch->berat_matang_bersih) : 0;
                
                \App\Models\RoastedBeanStock::create([
                    'roasted_bean_id' => $batch->roasted_bean_id,
                    'stok_gram' => $batch->berat_matang_bersih * 1000,
                    'harga_roasting' => round($hpp),
                    'tanggal_roasting' => $batch->tanggal_roasting ?? now()->toDateString(),
                ]);
            }
        });
    }

    protected $fillable = [
        'kode_batch',
        'user_id',
        'green_bean_id',
        'roasted_bean_id',
        'profil_sangrai',
        'durasi_roasting',
        'waktu_first_crack',
        'waktu_development',
        'durasi_pre_heating',
        'suhu',
        'berat_mentah_gudang',
        'berat_mentah_sortasi',
        'berat_matang_kotor',
        'berat_matang_bersih',
        'sisa_stok',
        'biaya_bahan_baku',
        'persentase_susut',
        'tanggal_roasting',
        'status_batch'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function greenBean()
    {
        return $this->belongsTo(GreenBean::class);
    }

    public function roastedBean()
    {
        return $this->belongsTo(RoastedBean::class);
    }

    public function hppCalculation()
    {
        return $this->hasOne(HppCalculation::class);
    }

    public function logPekerjas()
    {
        return $this->hasMany(LogPekerja::class, 'roasting_batch_id');
    }
}
