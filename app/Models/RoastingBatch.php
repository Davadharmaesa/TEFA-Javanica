<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RoastingBatch extends Model
{
    public function up() : void {
        Schema::create('roasting_batches', function (Blueprint $table) {
            $table->id();
            //relasi 
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('green_bean_id')->constrained('green_beans');
            $table->foreignId('roasted_bean_id')->constrained('roasted_beans');
            //atribut
            $table->decimal('berat_mentah', 8, 2); 
            $table->decimal('susut_uap', 5, 2); 
            $table->decimal('susut_kotoran', 5, 2); 
            $table->decimal('susut_gosong', 5, 2); 
            $table->decimal('berat_matang', 8, 2);
            
            $table->date('tanggal_roasting');
            $table->string('status_batch');
            $table->timestamps();
        });
    }
}
