<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cacheVersion = (int) Cache::get('products.cache.version', 1);
        $cacheKey = 'products.index.v' . $cacheVersion . '.' . md5($request->fullUrl());

        $data = Cache::remember($cacheKey, 120, function () use ($request) {
            $query = Product::query()
                ->select(['id', 'vendor_id', 'category_id', 'name', 'description', 'price', 'stock', 'image', 'featured'])
                ->with([
                    'category:id,name',
                    'vendor:id,user_id,store_name',
                    'vendor.user:id,name',
                ]);

            if ($request->filled('category')) {
                $query->whereHas('category', function ($builder) use ($request) {
                    $builder->where('name', $request->string('category')->toString());
                });
            }

            if ($request->filled('search')) {
                $search = '%' . $request->string('search')->toString() . '%';
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->float('max_price'));
            }

            $sort = $request->string('sort')->toString();
            if ($sort === 'priceAsc') {
                $query->orderBy('price');
            } elseif ($sort === 'priceDesc') {
                $query->orderByDesc('price');
            } elseif ($sort === 'nameAsc') {
                $query->orderBy('name');
            }

            $perPage = $request->integer('per_page', 20);
            $paginated = $query->paginate($perPage);

            return [
                'data' => $paginated->getCollection()->map(fn (Product $product) => $this->serializeProduct($product))->values()->all(),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
            ];
        });

        return response()
            ->json($data)
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function show($id)
    {
        $cacheVersion = (int) Cache::get('products.cache.version', 1);
        $cacheKey = 'products.show.v' . $cacheVersion . '.' . $id;

        $data = Cache::remember($cacheKey, 120, function () use ($id) {
            $product = Product::query()
                ->select(['id', 'vendor_id', 'category_id', 'name', 'description', 'price', 'stock', 'image', 'featured'])
                ->with([
                    'category:id,name',
                    'vendor:id,user_id,store_name',
                    'vendor.user:id,name',
                ])
                ->findOrFail($id);
            return $this->serializeProduct($product);
        });

        return response()
            ->json($data)
            ->header('Cache-Control', 'public, max-age=60');
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name,
            'vendorId' => $product->vendor_id,
            'vendorName' => $product->vendor?->store_name ?? $product->vendor?->user?->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'image' => $product->image,
            'description' => $product->description,
            'featured' => (bool) $product->featured,
        ];
    }
}
