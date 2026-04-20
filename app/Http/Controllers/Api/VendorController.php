<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VendorController extends Controller
{
    public function index()
    {
        $cacheVersion = (int) Cache::get('vendors.cache.version', 1);
        $cacheKey = 'vendors.public.index.v' . $cacheVersion;

        $data = Cache::remember($cacheKey, 120, function () {
            $vendors = Vendor::query()
                ->select(['id', 'user_id', 'store_name', 'rating', 'is_approved'])
                ->with('user:id,name')
                ->where('is_approved', true)
                ->orderBy('store_name')
                ->get();

            return $vendors->map(fn (Vendor $vendor) => $this->serializeVendor($vendor))->values()->all();
        });

        return response()
            ->json($data)
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function dashboard(Request $request)
    {
        if ($request->user()->role !== 'vendor') {
            return response()->json(['message' => 'Insufficient role'], 403);
        }

        $vendor = $this->resolveVendor($request);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $productIds = $vendor->products()->pluck('id');
        $ordersCount = Order::whereHas('items', function ($builder) use ($productIds) {
            $builder->whereIn('product_id', $productIds);
        })->count();

        return response()->json([
            'vendor' => $this->serializeVendor($vendor),
            'productsCount' => $vendor->products()->count(),
            'ordersCount' => $ordersCount,
        ]);
    }

    public function myProducts(Request $request)
    {
        if ($request->user()->role !== 'vendor') {
            return response()->json(['message' => 'Insufficient role'], 403);
        }

        $vendor = $this->resolveVendor($request);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $perPage = $request->integer('per_page', 20);
        $products = $vendor->products()->with('category')->paginate($perPage);

        return response()->json([
            'data' => $products->getCollection()->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price,
                    'stock' => (int) $product->stock,
                ];
            }),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function myOrders(Request $request)
    {
        if ($request->user()->role !== 'vendor') {
            return response()->json(['message' => 'Insufficient role'], 403);
        }

        $vendor = $this->resolveVendor($request);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $productIds = $vendor->products()->pluck('id');
        $perPage = $request->integer('per_page', 10);
        $orders = Order::with('items.product')
            ->whereHas('items', function ($builder) use ($productIds) {
                $builder->whereIn('product_id', $productIds);
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $orders->getCollection(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    private function resolveVendor(Request $request): ?Vendor
    {
        return Vendor::with('products', 'user')
            ->where('user_id', $request->user()->id)
            ->first();
    }

    private function serializeVendor(Vendor $vendor): array
    {
        return [
            'id' => $vendor->id,
            'name' => $vendor->store_name,
            'rating' => (float) $vendor->rating,
            'userId' => $vendor->user_id,
            'isApproved' => (bool) $vendor->is_approved,
        ];
    }
}
