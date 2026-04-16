<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RoastingBatch extends Model
{
    use HasFactory;

    protected $table = 'roasting_batch';

    protected $fillable = [
        'user_id',
        'green_bean_id',
        'roasted_bean_id',
        'berat_mentah',
        'susut_uap',
        'susut_kotoran',
        'susut_gosong',
        'berat_matang',
        'tanggal_roasting',
        'status_batch'
    ];
}
