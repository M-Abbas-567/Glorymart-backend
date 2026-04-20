<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);
        $orders = Order::with(['items.product'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $orders->getCollection()->map(fn (Order $order) => $this->serializeOrder($order)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'shipping.fullName' => 'required|string|max:100',
            'shipping.phone' => 'required|string|max:30',
            'shipping.email' => 'required|email',
            'shipping.address' => 'required|string|max:255',
            'shipping.city' => 'required|string|max:100',
            'shipping.payment' => 'nullable|in:COD,DUMMY',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $total = 0;
            $itemIds = collect($data['items'])->pluck('productId')->unique()->values();
            $products = Product::query()->whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $product = $products->get($item['productId']);
                if (!$product) {
                    abort(422, 'Invalid product in order items.');
                }
                $total += $product->price * $item['qty'];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
                'status' => 'Pending',
                'shipping_name' => $data['shipping']['fullName'],
                'shipping_phone' => $data['shipping']['phone'],
                'shipping_email' => $data['shipping']['email'],
                'shipping_address' => $data['shipping']['address'],
                'shipping_city' => $data['shipping']['city'],
                'payment_method' => $data['shipping']['payment'] ?? 'COD',
            ]);

            foreach ($data['items'] as $item) {
                $product = $products[$item['productId']];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $item['qty']);
            }

            return $order->load('items.product');
        });

        Cache::increment('products.cache.version');

        return response()->json($this->serializeOrder($order), 201);
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'customerId' => $order->user_id,
            'status' => $order->status,
            'createdAt' => $order->created_at?->toISOString(),
            'items' => $order->items->map(fn (OrderItem $item) => [
                'productId' => $item->product_id,
                'qty' => $item->qty,
                'price' => (float) $item->price,
            ])->values(),
            'total' => (float) $order->total,
            'shipping' => [
                'fullName' => $order->shipping_name,
                'phone' => $order->shipping_phone,
                'email' => $order->shipping_email,
                'address' => $order->shipping_address,
                'city' => $order->shipping_city,
                'payment' => $order->payment_method,
            ],
        ];
    }
}
