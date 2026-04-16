<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RoastedBean extends Model
{
    use HasFactory;

    protected $table = 'roasted_bean';

    protected $fillable = [
        'nama_kopi',
        'level_roast',
        'total_stok',
        'satuan_dasar'
    ];
}
