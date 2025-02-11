<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleComponentTable extends Migration
{
    public function up()
    {
        Schema::create('role_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
            $table->boolean('can_view')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_component');
    }
};

