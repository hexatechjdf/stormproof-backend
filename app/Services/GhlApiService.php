<?php

namespace App\Services;

use App\Helper\CRM;
use App\Models\Agency;
use App\Models\User;
use App\Models\CrmAuths;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhlApiService
{
    private const BASE_URL = 'https://services.leadconnectorhq.com/';
    private const API_VERSION = '2021-07-28';

    private ?Agency $userContext = null;
    private ?CrmAuths $tokenRecord = null;
    private $company_id = null;
    public function forUser(Agency $agency, $location_id = null): self
    {
        $this->userContext = $agency;
        if ($location_id) {
            $token = CrmAuths::where('location_id', $location_id)->first();
            if (!$token) {
                $token = CRM::getLocationAccessToken($agency, $location_id);
            }
            $this->tokenRecord = $token;
        } else {
            $this->tokenRecord = CrmAuths::where('agency_id', $agency->id)->where('user_type', 'Company')->first();
        }
        $this->company_id = $this->tokenRecord->company_id;
        return $this;
    }
    private function makeRequest(string $endpoint, string $method = 'GET', array $data = [], int $retries = 0)
    {
        if (!$this->tokenRecord) {
            throw new \Exception('GHL API Error: No token record found for the user.');
        }

        // Refresh token if expired
        if ($this->isTokenExpired() && $retries === 0) {
            $this->refreshToken();
            return $this->makeRequest($endpoint, $method, $data, 1);
        }

        $url = self::BASE_URL . $endpoint;
        $method = strtolower($method);

        $request = Http::withToken($this->tokenRecord->access_token)
            ->withHeaders(['Version' => self::API_VERSION]);

        // Detect file upload
        $hasFile = false;
        foreach ($data as $value) {
            if ($value instanceof \CURLFile) {
                $hasFile = true;
                break;
            }
        }
        if ($hasFile) {
            // Multipart request
            $multipartData = [];
            foreach ($data as $key => $value) {
                if ($value instanceof \CURLFile) {
                    $multipartData[] = [
                        'name'     => $key,
                        'contents' => fopen($value->getFilename(), 'r'),
                        'filename' => $value->getPostFilename() ?? basename($value->getFilename()),
                        'headers'  => ['Content-Type' => $value->getMimeType()],
                    ];
                } else {
                    $multipartData[] = [
                        'name'     => $key,
                        'contents' => $value,
                    ];
                }
            }

            $response = $request->withOptions(['multipart' => $multipartData])
                ->send($method, $url); // send() works with custom method
        } else {
            // JSON request
            $response = $method === 'get'
                ? $request->get($url, $data)
                : $request->{$method}($url, $data);
        }
        if ($response->failed()) {
            Log::error('GHL API Request Failed', [
                'user_id'  => $this->userContext->id ?? null,
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
        }

        $responseObject = $response->object();

        // Retry for invalid JWT
        if (
            isset($responseObject->message)
            && is_string($responseObject->message)
            && str_contains($responseObject->message, 'Invalid JWT')
            && $retries < 2
        ) {
            $this->refreshToken();
            return $this->makeRequest($endpoint, $method, $data, $retries + 1);
        }

        return $responseObject;
    }

    private function refreshToken(): void
    {
        if (!$this->tokenRecord || !$this->tokenRecord->refresh_token) {
            throw new \Exception("Cannot refresh GHL token: No refresh token available for user {$this->userContext->id}.");
        }
        Log::info("Attempting to refresh GHL token for user {$this->userContext->id}");
        $clientId = CRM::getDefault('crm_client_id', '', $this->userContext);
        $clientSecret = CRM::getDefault('crm_client_secret', '', $this->userContext);

        if (!$clientId || !$clientSecret) {
            throw new \Exception("Cannot refresh GHL token: Superadmin CRM credentials are not set.");
        }
        $response = Http::asForm()->post(self::BASE_URL . 'oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->tokenRecord->refresh_token,
        ]);
        if ($response->failed() || !isset($response->object()->access_token)) {
            Log::error("Failed to refresh GHL token for user {$this->userContext->id}", [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            $this->tokenRecord->delete();
            throw new \Exception("Failed to refresh GHL token. The user may need to re-authenticate.");
        }

        $newTokenData = $response->object();
        $this->tokenRecord->update([
            'access_token' => $newTokenData->access_token,
            'refresh_token' => $newTokenData->refresh_token,
            'expires_in' => $newTokenData->expires_in,
        ]);
        $this->tokenRecord->refresh();
        Log::info("Successfully refreshed GHL token for user {$this->userContext->id}");
    }

    private function isTokenExpired(): bool
    {
        return empty($this->tokenRecord->access_token);
    }
    public function getCustomFields()
    {
        if (!$this->userContext) throw new \Exception('User context not set.');

        $endpoint = "locations/{$this->userContext->location_id}/customFields";
        $response = $this->makeRequest($endpoint);

        return $response->customFields ?? [];
    }

    public function upsertContact(array $payload)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = "contacts/upsert";
        $response = $this->makeRequest($endpoint, 'POST', $payload);

        if (!isset($response->contact)) {
            throw new \Exception("Failed to upsert contact. Response: " . json_encode($response));
        }
        return $response->contact;
    }
    public function getLocations()
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'locations/search?companyId=' . $this->company_id . '&limit=100';
        $response = $this->makeRequest($endpoint, 'GET', ['companyId' => $this->company_id]);
        return $response ?? [];
    }
    public function getProducts($location_id = null)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'products/?limit=100';
        $response = $this->makeRequest($endpoint, 'GET', ['locationId' => $location_id]);
        return $response ?? [];
    }
    public function getProductPrices($productId, $locationId)
    {
        $endpoint = "products/{$productId}/price";
        $response = $this->makeRequest($endpoint, 'GET', ['locationId' => $locationId]);
        return $response ?? [];
    }
    public function getUsers()
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'users/?companyId=' . $this->company_id;
        $response = $this->makeRequest($endpoint, 'GET', ['companyId' => $this->company_id]);
        dd($response);
        return $response ?? [];
    }
    public function getUsersByLocation($locationId)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'users/?locationId=' . $locationId;
        $response = $this->makeRequest($endpoint, 'GET', ['locationId' => $locationId]);
        return $response ?? [];
    }
    public function createUser(array $userData, $locationId)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'users/?locationId=' . $locationId;
        $response = $this->makeRequest($endpoint, 'POST', $userData);
        $status = $response->statusCode ?? $response->status ?? null;

        if (in_array($status, [400, 422, 500, 501, 503])) {
            throw new \Exception("Failed to create user. Response: " . json_encode($response));
        }
        return $response;
    }
    public function getCrmUserById($userId, $locationId)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'users/' . $userId . '?locationId=' . $locationId;
        $response = $this->makeRequest($endpoint, 'GET', ['locationId' => $locationId]);

        if (!isset($response)) {
            throw new \Exception("Failed to fetch CRM user by ID. Response: " . json_encode($response));
        }
        return $response;
    }
    public function createAppointment(array $appointmentData, $locationId = null)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'calendars/events/appointments?locationId=' . $locationId;
        $response = $this->makeRequest($endpoint, 'POST', $appointmentData);
        $status = $response->statusCode ?? $response->status ?? null;

        if (in_array($status, [400, 422, 500, 501, 503])) {
            throw new \Exception("Failed to create appointment. Response: " . json_encode($response));
        }
        return $response;
    }
    public function getCalendars($location_id)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'calendars/?locationId=' . $location_id;
        $response = $this->makeRequest($endpoint, 'GET', ['locationId' => $location_id]);
        return $response ?? [];
    }
    public function uploadFile($payload, $location_id)
    {
        if (!$this->userContext) throw new \Exception('User context not set.');
        $endpoint = 'medias/upload-file/?locationId=' . $location_id;
        $response = $this->makeRequest($endpoint, 'POST', $payload);
        return $response ?? [];
    }
}
