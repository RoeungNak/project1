<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    // Get all suppliers
    public function index()
    {
        $suppliers = Supplier::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $suppliers
        ], 200);
    }

    // Store a new supplier
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:400',
            'status' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $supplier = Supplier::create($request->only(['name', 'email', 'phone', 'address', 'status']));

        return response()->json([
            'status' => 201,
            'message' => 'Supplier created successfully',
            'data' => $supplier
        ], 201);
    }

    // Get a single supplier
    public function show($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $supplier
        ], 200);
    }

    // Update a supplier
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:400',
            'status' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $supplier->update($request->only(['name', 'email', 'phone', 'address', 'status']));

        return response()->json([
            'status' => 200,
            'message' => 'Supplier updated successfully',
            'data' => $supplier
        ], 200);
    }

    // Delete a supplier
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 404,
                'message' => 'Supplier not found',
                'data' => []
            ], 404);
        }

        // Optional: Check for related products before deleting
        if (method_exists($supplier, 'products') && $supplier->products()->count() > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot delete supplier with associated products'
            ], 400);
        }

        $supplier->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Supplier deleted successfully'
        ], 200);
    }
}
