<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class SaleReportController extends Controller
{
    public function index()
    {
        try {
            $totalSales = Order::count();
            $totalRevenue = Order::sum('total_usd');

            return response()->json([
                'status' => 200,
                'data' => [
                    'totalSales' => $totalSales,
                    'totalRevenue' => $totalRevenue,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch sale report',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
