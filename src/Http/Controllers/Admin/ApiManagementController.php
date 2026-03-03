<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use DarkCoder\Ofa\Services\ApiService;
use DarkCoder\Ofa\Models\ApiKey;
use DarkCoder\Ofa\Models\Webhook;
use DarkCoder\Ofa\Models\ApiRequestLog;
use Illuminate\Http\Request;

class ApiManagementController
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create a new API key
     */
    public function createApiKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'restricted_servers' => 'array',
            'expires_at' => 'nullable|date',
        ]);

        $apiKey = $this->apiService->createApiKey(
            auth()->id(),
            $validated['name'],
            $validated['permissions'],
            $validated['restricted_servers'] ?? [],
            $validated['expires_at'] ?? null
        );

        return response()->json([
            'message' => 'API key created successfully',
            'api_key' => $apiKey,
        ], 201);
    }

    /**
     * List API keys
     */
    public function listApiKeys()
    {
        $keys = ApiKey::where('user_id', auth()->id())
            ->paginate(25);

        return response()->json($keys);
    }

    /**
     * Revoke API key
     */
    public function revokeApiKey(ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $apiKey->update(['active' => false]);

        return response()->json([
            'message' => 'API key revoked successfully',
        ]);
    }

    /**
     * Create webhook
     */
    public function createWebhook(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array',
            'headers' => 'array',
            'description' => 'nullable|string',
        ]);

        $webhook = $this->apiService->createWebhook(
            auth()->id(),
            $validated['name'],
            $validated['url'],
            $validated['events'],
            $validated['headers'] ?? [],
            $validated['description'] ?? null
        );

        return response()->json([
            'message' => 'Webhook created successfully',
            'webhook' => $webhook,
        ], 201);
    }

    /**
     * List webhooks
     */
    public function listWebhooks()
    {
        $webhooks = Webhook::where('user_id', auth()->id())
            ->paginate(25);

        return response()->json($webhooks);
    }

    /**
     * Get webhook statistics
     */
    public function webhookStats(Webhook $webhook)
    {
        if ($webhook->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $this->apiService->getWebhookStats($webhook);

        return response()->json($stats);
    }

    /**
     * Update webhook
     */
    public function updateWebhook(Request $request, Webhook $webhook)
    {
        if ($webhook->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'url' => 'url',
            'events' => 'array',
            'active' => 'boolean',
        ]);

        $webhook->update($validated);

        return response()->json([
            'message' => 'Webhook updated successfully',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(Webhook $webhook)
    {
        if ($webhook->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $webhook->delete();

        return response()->json([
            'message' => 'Webhook deleted successfully',
        ]);
    }

    /**
     * Get API usage statistics
     */
    public function apiUsageStats(Request $request)
    {
        $apiKey = ApiKey::where('user_id', auth()->id())->first();
        $days = $request->input('days', 30);

        if (!$apiKey) {
            return response()->json(['error' => 'No API key found'], 404);
        }

        $stats = $this->apiService->getApiUsageStats($apiKey->id, $days);

        return response()->json($stats);
    }

    /**
     * Get API request logs
     */
    public function getRequestLogs(Request $request)
    {
        $apiKey = ApiKey::where('user_id', auth()->id())->first();

        if (!$apiKey) {
            return response()->json(['error' => 'No API key found'], 404);
        }

        $query = ApiRequestLog::where('api_key_id', $apiKey->id);

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'errors') {
                $query->where('status_code', '>=', 400);
            } elseif ($status === 'slow') {
                $query->where('response_time_ms', '>', 1000);
            }
        }

        $logs = $query->latest()->paginate(50);

        return response()->json($logs);
    }

    /**
     * Test webhook delivery
     */
    public function testWebhookDelivery(Webhook $webhook)
    {
        if ($webhook->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $testPayload = [
            'event' => 'test.webhook',
            'timestamp' => now(),
            'test' => true,
        ];

        $this->apiService->queueWebhookDelivery($webhook, 'test', $testPayload);

        return response()->json([
            'message' => 'Test webhook queued',
        ]);
    }
}
