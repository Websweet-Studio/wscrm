<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerUsernameController extends Controller
{
    public function checkAvailability(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $excludeId = $request->input('exclude_id'); // For edit mode

        if (empty($username)) {
            return response()->json([
                'available' => true,
                'message' => '',
            ]);
        }

        // Check minimum length
        if (strlen($username) < 5) {
            return response()->json([
                'available' => false,
                'message' => 'Username minimal 5 karakter',
            ]);
        }

        // Check if username contains only allowed characters
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore',
            ]);
        }

        // Check availability in database
        $query = Customer::where('username', $username);

        // Exclude current customer when editing
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia',
        ]);
    }
}
