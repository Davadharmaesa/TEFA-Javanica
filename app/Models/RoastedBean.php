<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RoastedBean extends Model
{
    public function up() : void {
        Schema::create('roasted_beans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kopi');
            $table->id('level_roast');
            $table->id('total_stok');
            $table->id('satuan_dasar');
            $table->timestamps();
        });
    }
}
