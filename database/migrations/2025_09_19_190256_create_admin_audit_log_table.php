<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Run this migration with: php artisan migrate
 * 
 * To create this file: 
 * php artisan make:migration create_admin_audit_log_table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id', 20)->index(); // e.g., AD001
            $table->string('admin_username')->nullable();
            $table->string('action', 100); // e.g., update_package, delete_package
            $table->string('target_type', 50); // e.g., package, user, delivery
            $table->string('target_id', 50); // e.g., P001
            $table->text('description')->nullable(); // Human-readable description
            $table->json('old_values')->nullable(); // Original data
            $table->json('new_values')->nullable(); // Updated data
            $table->json('metadata')->nullable(); // Additional context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->string('url')->nullable();
            $table->string('status', 20)->default('success'); // success, failed, pending
            $table->text('error_message')->nullable(); // If action failed
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Indexes for better query performance
            $table->index('action');
            $table->index('target_type');
            $table->index('created_at');
            $table->index(['admin_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_log');
    }
};