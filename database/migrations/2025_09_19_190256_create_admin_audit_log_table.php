<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAuditLogTable extends Migration
{
    public function up()
    {
        Schema::create('admin_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id', 20);
            $table->string('action', 255);
            $table->string('target_type', 50)->nullable();
            $table->string('target_id', 255)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('admin_id');
            $table->index('created_at');
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_audit_log');
    }
}
