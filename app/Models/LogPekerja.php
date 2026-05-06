<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPekerja extends Model
{
    use HasFactory;

    protected $table = 'log_pekerja';

    protected $fillable = [
        'roasting_batch_id',
        'pekerja_id',
        'biaya_id',
        'jumlah_kerja',
        'total_upah',
    ];

    public function batch()
    {
        return $this->belongsTo(RoastingBatch::class, 'roasting_batch_id');
    }

    public function pekerja()
    {
        return $this->belongsTo(User::class, 'pekerja_id');
    }

    public function komponenBiaya()
    {
        return $this->belongsTo(Biaya::class, 'biaya_id');
    }
}
