<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GreenBean extends Model
{
    public function up(): void {
        Schema::create('green_beans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biji');
            $table->id('stok_gram');
            $table->id('asal_kopi');
            $table->id('harga_jual');
            $table->timestamps();
        });
    }
}
