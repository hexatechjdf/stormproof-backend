<?php

namespace App\Services;

use App\Models\Agency;
use Illuminate\Support\Facades\Http;

class CompanyCamService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.companycam.com/v2/';

    public function __construct(Agency $agency)
    {
        // Retrieve the API key from the agency's settings
        $this->apiKey = $agency->settings->where('key', 'company_cam_access_token')->first()->value ?? null;
    }

    /**
     * Check if the service is configured and ready to be used.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Create a new project in CompanyCam.
     *
     * @param string $projectName
     * @param string $address
     * @return string|null The ID of the created project, or null on failure.
     */
    public function createProject(string $projectName, string $address, ?string $collaboratorId = null): ?string
    {
        if (!$this->isConfigured()) {
            // Or throw an exception, depending on desired error handling
            return null;
        }
        $payload = [
            'project' => [
                'name' => $projectName,
                'primary_address' => $address,
            ]
        ];

        // If a collaborator ID is provided, add it to the payload
        if ($collaboratorId) {
            $payload['project']['collaborators'] = [$collaboratorId];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . 'projects', $payload);

        if ($response->successful() && isset($response->json()['id'])) {
            return $response->json()['id'];
        }

        // Log error for debugging
        // Log::error('CompanyCam API Error: ' . $response->body());
        return null;
    }
    public function getUsers(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . 'users');

        if ($response->successful()) {
            // We only need the id and name for mapping
            return collect($response->json())->map(function ($user) {
                return [
                    'id' => $user['id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email_address'] . ')',
                ];
            })->all();
        }

        return [];
    }
    public function createWebhook()
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . 'webhooks');

        if ($response->successful() && isset($response->json()['id'])) {
            return $response->json();
        }else{
                $payload = [
                    'url' => route('companycam.webhook'),
                    ''
                ];
        return $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->baseUrl . 'webhooks',$payload);
        }

        return [];
    }
}