<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function uploadPayment(Request $request)
    {
        // 1. Validate inputs
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Fetch the order to get payer details
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['status' => 404, 'message' => 'Order not found'], 404);
        }

        $image = $request->file('image');
        $imageName = $request->order_id . '-' . time() . '.' . $image->getClientOriginalExtension();

        // 2. Define directory
        $uploadDir = public_path('uploads/payments');

        // 3. Create folder if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            // 4. Move file to public/uploads/payments
            $image->move($uploadDir, $imageName);

            // 5. Save to database
            $payment = Payment::create([
                'order_id' => $request->order_id,
                'payer_name' => $order->name,
                'phone_number' => $order->phone_number,
                'image_path' => 'uploads/payments/' . $imageName,
                'status' => 'pending', // default status
            ]);

            // 6. Return JSON with public URL
            return response()->json([
                'status' => 201,
                'message' => 'Payment image uploaded successfully',
                'payment' => [
                    'id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'payer_name' => $payment->payer_name,
                    'phone_number' => $payment->phone_number,
                    'status' => $payment->status,
                    'image_path' => $payment->image_path,
                    'image_url' => url($payment->image_path),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Image upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
