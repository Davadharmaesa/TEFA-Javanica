<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoastingBatch extends Model
{
    use HasFactory;

    protected $table = 'roasting_batch';

    protected $fillable = [
        'kode_batch',
        'user_id',
        'green_bean_id',
        'roasted_bean_id',
        'profil_sangrai',
        'durasi_roasting',
        'berat_mentah_gudang',
        'berat_mentah_sortasi',
        'berat_matang_kotor',
        'berat_matang_bersih',
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
}
