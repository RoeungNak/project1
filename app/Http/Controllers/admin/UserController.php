<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 200, 'data' => $users]);
    }

    // Show single user
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'User not found'], 404);
        }

        return response()->json(['status' => 200, 'data' => $user]);
    }

    // Update user
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:customer,admin',
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json(['status' => 200, 'message' => 'User updated successfully', 'data' => $user]);
    }

    // Delete user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['status' => 200, 'message' => 'User deleted successfully']);
    }
    public function counts()
    {
        $admins = User::where('role', 'admin')->count();
        $customers = User::where('role', 'customer')->count();

        return response()->json([
            'status' => 200,
            'admins' => $admins,
            'customers' => $customers,
        ]);
    }
}
