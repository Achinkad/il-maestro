<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_user', function (Blueprint $table) {
            $table->unsignedBigInteger('node_id');
                $table->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_user');
    }
};
