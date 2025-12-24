<?php

namespace App\Services;

use App\Helper\CRM;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrmService
{
    protected $agency;
    protected $ghlApi;
    public function __construct(Agency $agency = null, GhlApiService $ghlApi = null)
    {
        $this->agency = $agency ?? auth()->user()->agency ??  Agency::first();
        $this->ghlApi = $ghlApi ?? app(GhlApiService::class);

        if (!$this->agency) {
            throw new \Exception("No authenticated user context available.");
        }
    }
    public function getLocations()
    {
        return $this->ghlApi->forUser($this->agency)->getLocations();
    }
    public function getProducts($locationId = null)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->getProducts($locationId);
    }
    public function getProductPrices($productId, $locationId)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->getProductPrices($productId, $locationId);
    }
    public function getUsers()
    {
        return $this->ghlApi->forUser($this->agency)->getUsers();
    }
    public function getUsersByLocation($locationId)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->getUsersByLocation($locationId,'location');
    }
     public function getCrmUserById($userId, $locationId)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->getCrmUserById($userId,$locationId);
    }
    public function createUser(array $userData, $locationId)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->createUser($userData,$locationId);
    }
    public function createAppointment(array $appointmentData, $locationId = null)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->createAppointment($appointmentData, $locationId);
    }
    public function getCalendar($locationId)
    {
        return $this->ghlApi->forUser($this->agency, $locationId)->getCalendars($locationId);
    }
    public function handleCrmAuth(string $code, Agency $agency)
    {
        $tokenResponse = CRM::crm_token($code, '', $agency);
        $tokenData = json_decode($tokenResponse);
        if (!isset($tokenData->userType)) {
            return ['status' => false, 'message' => 'Invalid CRM response'];
        }
        $userType = strtolower($tokenData->userType);
        [$connected, $con] = CRM::go_and_get_token($tokenData, '', $agency->id, $agency->crmauth ?? null);
        if ($connected) {
            $newUser = User::where('agency_id', $agency->id)->first();
            Log::info('User Created' . json_encode($newUser));
            Auth::login($newUser);
            return ['status' => true, 'message' => 'Connected Successfully'];
        }
        return ['status' => false, 'message' => 'Unable to connect to the ' . $userType];
    }
    public function uploadFile($payload , $locationId)
    {
         return $this->ghlApi->forUser($this->agency, $locationId)->uploadFile($payload,$locationId);
    }
}
