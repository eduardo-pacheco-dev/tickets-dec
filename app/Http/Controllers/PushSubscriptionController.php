<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url:http,https'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['public_key'] ?? null,
            $data['auth_token'] ?? null,
            $data['content_encoding'] ?? null,
        );

        return response()->json([], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url:http,https'],
        ]);

        $request->user()->deletePushSubscription($data['endpoint']);

        return response()->json([], 204);
    }
}
