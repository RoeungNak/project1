<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function getFinanceSummary()
    {
        try {
            // Replace 'total' with your column that stores order amount
            $revenue = Order::where('status', 'completed')->sum('total_usd');

            // Replace 'stock' with your product quantity column
            $expenses = DB::table('products')->sum(DB::raw('cost * qty'));

            return response()->json([
                'status' => 200,
                'data' => [
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve finance data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
