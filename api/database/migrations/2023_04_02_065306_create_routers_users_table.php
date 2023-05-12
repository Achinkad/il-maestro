<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_nodes_user', function (Blueprint $table) {
            $table->unsignedBigInteger('master_nodes_id');
                $table->foreign('master_nodes_id')->references('id')->on('master_nodess')->onDelete("cascade");
            $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete("cascade");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_nodes_user');
    }
};
