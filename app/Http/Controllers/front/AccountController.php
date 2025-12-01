<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AccountController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
        $validator = FacadesValidator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        if ($user->save()) {
            Auth::login($user);
            $token = $user->createToken('token')->plainTextToken;
            return response()->json([
                'status' => 200,
                'message' => 'User registered and logged in successfully',
                "token" => $token,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ], 200);
        }
    }
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('token')->plainTextToken;
            return response()->json([
                'status' => 200,
                "token" => $token,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ], 200);
        } else {
            return response()->json([
                'status' => 401,
                'message' => "Invalid email or password."
            ], 401);
        }
    }
    public function getOrders(Request $request)
    {
        $user = $request->user();
        $orders = Order::with(['user', 'items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')->get();

        // Format the user info
        $orders->map(function ($order) {
            $order->customer_name = $order->user->name ?? 'N/A';
            $order->customer_email = $order->user->email ?? 'N/A';
            return $order;
        });

        return response()->json([
            'status' => 200,
            'data' => $orders
        ], 200);
    }

    public function getOrderDetails(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::with('items')
            ->where('user_id', $user->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'status' => 404,
                'message' => 'Order not found or you do not have permission to view it.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $order
        ], 200);
    }
}
