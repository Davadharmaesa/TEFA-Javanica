<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Testing\Fluent\Concerns\Has;

class GreenBean extends Model
{
    use HasFactory;

    protected $table = 'green_bean';
    
    protected $fillable = [
        'nama_biji',
        'asal_kopi',
    ];

    public function stocks()
    {
        return $this->hasMany(GreenBeanStock::class);
    }
}
