<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsernameController extends Controller
{
    public function checkAvailability(Request $request): JsonResponse
    {
        $username = $request->input('username');

        if (empty($username) || strlen($username) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Username minimal 3 karakter',
                'status' => 'invalid'
            ]);
        }

        if (strlen($username) > 30) {
            return response()->json([
                'available' => false,
                'message' => 'Username maksimal 30 karakter',
                'status' => 'invalid'
            ]);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Username hanya boleh huruf, angka, dan underscore',
                'status' => 'invalid'
            ]);
        }

        $exists = User::where('username', $username)->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'Username sudah digunakan',
                'status' => 'taken'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Username tersedia',
            'status' => 'available'
        ]);
    }
}
