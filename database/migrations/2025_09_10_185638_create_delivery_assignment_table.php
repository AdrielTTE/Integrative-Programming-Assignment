<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('delivery_assignment', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->string('package_id', 20); // must match packages.package_id
            $table->unsignedBigInteger('driver_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('status', [
                'assigned',
                'accepted',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('assigned');
            $table->timestamps();

            // Foreign keys
            $table->foreign('package_id')
                  ->references('package_id')
                  ->on('packages')
                  ->onDelete('cascade');

            $table->foreign('driver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Indexes
            $table->index(['driver_id', 'status']);
            $table->unique(['package_id', 'driver_id']);
        });
    }

    public function down() {
        Schema::dropIfExists('delivery_assignment');
    }
};
