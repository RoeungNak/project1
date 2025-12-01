<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {

        $orders = Order::select(
            'orders.*',
            'users.name as customer_name',
            'users.email as customer_email'
        )
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->with('items')->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'data' => $orders,
            'status' => 200
        ], 200);
    }
    public function details($id)
    {
        $order = Order::select(
            'orders.*',
            'users.name as customer_name',
            'users.email as customer_email'
        )
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->with('items')->find($id);

        if ($order === null) {
            return response()->json([
                'data' => [],
                'message' => 'Order not found',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'data' => $order,
            'status' => 200
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Shipped,Delivered,Completed,Cancelled',
            'payment_status' => 'required|in:Paid,Unpaid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        $order->update($validator->validated());

        return $this->details($id);
    }
    public function date_query(Request $request)
    {
        $query = Order::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $orders
        ], 200);
    }
    public function completedSales()
    {
        try {
            $completedOrders = Order::where('status', 'completed')->get();
            return response()->json([
                'status' => 200,
                'data' => $completedOrders, // array of completed orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch completed sales',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
