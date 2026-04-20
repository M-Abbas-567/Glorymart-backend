<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $customer = User::create([
            'name' => 'Ava Ray',
            'email' => 'customer@glorymart.local',
            'password' => bcrypt('customer123'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->call(AdminSeeder::class);

        $vendorUsers = [
            'NovaTech Store' => User::create([
                'name' => 'Nova Vendor',
                'email' => 'vendor@glorymart.local',
                'password' => bcrypt('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ]),
            'UrbanThread' => User::create([
                'name' => 'Urban Vendor',
                'email' => 'vendor2@glorymart.local',
                'password' => bcrypt('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ]),
            'HomeNest Market' => User::create([
                'name' => 'HomeNest Vendor',
                'email' => 'vendor3@glorymart.local',
                'password' => bcrypt('vendor123'),
                'role' => 'vendor',
                'status' => 'active',
            ]),
        ];

        $vendors = collect($vendorUsers)->mapWithKeys(function (User $user, string $storeName) {
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $storeName,
                'rating' => match ($storeName) {
                    'NovaTech Store' => 4.8,
                    'UrbanThread' => 4.6,
                    default => 4.7,
                },
                'is_approved' => true,
            ]);

            return [$storeName => $vendor];
        });

        $categories = collect([
            'Beauty',
            'Books',
            'Clothing',
            'Computers',
            'Devices',
            'Electronics',
            'Fashion',
            'Furniture',
            'Grocery',
            'Home',
            'Sports',
        ])->mapWithKeys(function (string $name) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);

            return [$name => $category];
        });

        $products = [
            [
                'vendor' => 'NovaTech Store',
                'category' => 'Electronics',
                'name' => 'Nebula Pro Wireless Headphones',
                'description' => 'Premium ANC headphones with spatial audio and 40-hour battery life.',
                'price' => 179.99,
                'stock' => 34,
                'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=900&q=80',
                'featured' => true,
            ],
            [
                'vendor' => 'NovaTech Store',
                'category' => 'Electronics',
                'name' => 'AeroLight Smartwatch X',
                'description' => 'Fitness tracking, OLED display, and multi-day battery in a lightweight build.',
                'price' => 229.00,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80',
                'featured' => true,
            ],
            [
                'vendor' => 'UrbanThread',
                'category' => 'Fashion',
                'name' => 'Monochrome Street Jacket',
                'description' => 'Water-resistant, breathable outerwear with minimalist city styling.',
                'price' => 94.50,
                'stock' => 61,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
                'featured' => false,
            ],
            [
                'vendor' => 'HomeNest Market',
                'category' => 'Home',
                'name' => 'CloudFoam Lounge Chair',
                'description' => 'Modern comfort chair with ergonomic support and premium fabric finish.',
                'price' => 289.00,
                'stock' => 9,
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80',
                'featured' => true,
            ],
            [
                'vendor' => 'HomeNest Market',
                'category' => 'Beauty',
                'name' => 'HydraGlow Skin Serum',
                'description' => 'Vitamin-rich hydration serum designed for bright, smooth daily skin care.',
                'price' => 38.99,
                'stock' => 120,
                'image' => 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?auto=format&fit=crop&w=900&q=80',
                'featured' => false,
            ],
            [
                'vendor' => 'UrbanThread',
                'category' => 'Sports',
                'name' => 'Velocity Running Shoes',
                'description' => 'Responsive cushioning and ultra-light sole for daily training sessions.',
                'price' => 124.00,
                'stock' => 44,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
                'featured' => true,
            ],
            [
                'vendor' => 'HomeNest Market',
                'category' => 'Books',
                'name' => 'Atomic Habits Hardcover',
                'description' => 'A practical framework for building better habits and long-term growth.',
                'price' => 19.50,
                'stock' => 77,
                'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=900&q=80',
                'featured' => false,
            ],
            [
                'vendor' => 'HomeNest Market',
                'category' => 'Grocery',
                'name' => 'Organic Essentials Box',
                'description' => 'Weekly healthy picks including organic snacks and pantry staples.',
                'price' => 29.99,
                'stock' => 95,
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80',
                'featured' => false,
            ],
        ];

        $createdProducts = collect($products)->map(function (array $product) use ($vendors, $categories) {
            return Product::create([
                'vendor_id' => $vendors[$product['vendor']]->id,
                'category_id' => $categories[$product['category']]->id,
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'image' => $product['image'],
                'featured' => $product['featured'],
            ]);
        });

        $vendorIds = $vendors->pluck('id');
        $categoryIds = $categories->pluck('id');
        for ($i = 0; $i < 28; $i++) {
            Product::create([
                'vendor_id' => $vendorIds->random(),
                'category_id' => $categoryIds->random(),
                'name' => 'Product ' . ($i + 9),
                'description' => 'Description for product ' . ($i + 9),
                'price' => rand(10, 500),
                'stock' => rand(1, 100),
                'image' => 'https://via.placeholder.com/900x900?text=Product+' . ($i + 9),
                'featured' => rand(0,1),
            ]);
        }

        $firstProduct = $createdProducts->first();
        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'Shipped',
            'total' => $firstProduct->price,
            'shipping_name' => 'Ava Ray',
            'shipping_phone' => '0300-0000000',
            'shipping_email' => 'customer@glorymart.local',
            'shipping_address' => '123 Main Street',
            'shipping_city' => 'Lahore',
            'payment_method' => 'COD',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $firstProduct->id,
            'qty' => 1,
            'price' => $firstProduct->price,
        ]);
    }
}
