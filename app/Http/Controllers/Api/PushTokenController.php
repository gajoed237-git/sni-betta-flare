<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->update([
            'fcm_token' => $request->token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push token updated successfully',
        ]);
    }
}
