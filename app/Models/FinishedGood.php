<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'roasted_bean_id',
        'packaging_id',
        'stok_pcs',
    ];

    public function roastedBean()
    {
        return $this->belongsTo(RoastedBean::class, 'roasted_bean_id');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging_id');
    }
}
