<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminActivityLogTable extends Migration
{
    public function up()
    {
        Schema::create('admin_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id', 20);
            $table->string('action', 255);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->integer('response_code');
            $table->float('execution_time');
            $table->timestamps();
            
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_activity_log');
    }
}
