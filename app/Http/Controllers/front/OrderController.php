<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function saveOrder(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        if (empty($request->cart)) {
            return response()->json([
                'status' => 400,
                'message' => 'Cart is empty'
            ], 400);
        }

        try {
            $orderId = DB::transaction(function () use ($request, $user) {
                $order = new Order();
                $order->user_id = $user->id;
                $order->phone_number = $request->phone_number;
                $order->location = $request->location;
                $order->total_usd = $request->total_usd; // This is already the final total (subtotal - discount + shipping)
                $order->total_riel = $request->total_riel;
                $order->shipping = $request->shipping;
                $order->discount = $request->discount;
                $order->payment_status = $request->payment_status;
                $order->status = $request->status;
                $order->delivery = $request->delivery;
                $order->save();

                foreach ($request->cart as $item) {
                    $product = Product::find($item['product_id']);

                    if (!$product || $product->qty < $item['qty']) {
                        throw new \Exception('Not enough stock for product: ' . ($product->title ?? 'Unknown'));
                    }

                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->price = $item['price']; // This is the original total for the item
                    $orderItem->unit_price = $item['price'];
                    $orderItem->qty = $item['qty'];
                    $orderItem->product_id = $item['product_id'];
                    $orderItem->size = $item['size'] ?? null;
                    $orderItem->name = $item['name'];
                    $orderItem->save();

                    // Subtract the quantity from the product's stock
                    $product->qty -= $item['qty'];
                    $product->save();
                }

                return $order->id;
            });

            return response()->json([
                'status' => 200,
                'id' => $orderId,
                'message' => 'Order saved successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['status' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    public function getOrder($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $order = Order::with('items', 'items.product')->where('user_id', $user->id)->find($id);

        if (!$order) {
            return response()->json(['status' => 404, 'message' => 'Order not found'], 404);
        }

        // The frontend expects a `cart` property, so let's alias `items`.
        $order->cart = $order->items;

        return response()->json($order);
    }

    public function getOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }
}
