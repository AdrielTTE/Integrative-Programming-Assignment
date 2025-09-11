<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery', function (Blueprint $table) {
            $table->string('delivery_id', 20)->primary();
            $table->string('package_id', 20);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->enum('delivery_status', [
                'pending',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'failed'
            ])->default('pending');
            $table->timestamp('pickup_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('package_id')
                  ->references('package_id')
                  ->on('packages') // correct table name
                  ->onDelete('cascade');

            $table->foreign('driver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index(['delivery_status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery');
    }
};
