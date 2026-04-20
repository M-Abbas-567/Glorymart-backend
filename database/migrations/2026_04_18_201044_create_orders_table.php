<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->string('shipping_email');
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('payment_method')->default('COD');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
