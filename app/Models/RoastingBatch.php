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
        'bentuk_akhir',
        'berat_mentah',
        'berat_matang',
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
}
