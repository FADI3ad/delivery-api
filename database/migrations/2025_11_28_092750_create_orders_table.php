<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();


            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');


            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('price');
            $table->string('vechicle_type');

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
