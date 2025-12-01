<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        $size = Size::orderBy('name', 'ASC')->get();
        return response()->json([
            'status' => 200,
            'data' => $size
        ], 200);
    }
}
