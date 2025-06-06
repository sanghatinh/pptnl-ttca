<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('full_name');
            $table->string('position');
            $table->string('department');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->foreignId('role_id')->constrained('roles');
            $table->timestamps();
            
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
       

    }
};