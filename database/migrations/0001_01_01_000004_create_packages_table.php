<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('packages', function (Blueprint $table) {
            $table->string('package_id', 20)->primary();
            $table->string('customer_id', 20);
            $table->string('description')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('destination')->nullable();
            $table->enum('status', [
                'pending',
                'in_transit',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')
                    ->references('customer_id')
                    ->on('customers')
                    ->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('packages');
    }
};
