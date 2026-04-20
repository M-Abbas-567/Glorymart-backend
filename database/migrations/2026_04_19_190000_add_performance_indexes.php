<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name', 'products_name_idx');
            $table->index('price', 'products_price_idx');
            $table->index('featured', 'products_featured_idx');
            $table->index(['category_id', 'price'], 'products_category_price_idx');
            $table->index(['vendor_id', 'price'], 'products_vendor_price_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->index(['is_approved', 'store_name'], 'vendors_approved_store_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'orders_user_created_idx');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_created_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_approved_store_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_vendor_price_idx');
            $table->dropIndex('products_category_price_idx');
            $table->dropIndex('products_featured_idx');
            $table->dropIndex('products_price_idx');
            $table->dropIndex('products_name_idx');
        });
    }
};
