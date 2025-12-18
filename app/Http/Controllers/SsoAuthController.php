<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CrmAuths;
use App\Models\CrmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

class SsoAuthController extends Controller
{
    /**
     * Replicates OpenSSL EVP_BytesToKey key derivation.
     */
    private function evp_bytes_to_key($password, $salt)
    {
        $derived_bytes = '';
        $previous = '';

        // Need 32 bytes key + 16 bytes IV = 48 bytes
        while (strlen($derived_bytes) < 48) {
            $previous = md5($previous . $password . $salt, true);
            $derived_bytes .= $previous;
        }

        $key = substr($derived_bytes, 0, 32);
        $iv  = substr($derived_bytes, 32, 16);

        return [$key, $iv];
    }


    /**
     * Validate SSO token
     */
    public function validateToken(Request $request)
    {
        try {
            $request->validate([
                'app_id' => 'required|string',
                'sso_token' => 'required|string',
            ]);

            $ssoKey = env('SSO_KEY', 'bf17cd7e-3fa1-4deb-b612-7d655f7332ff');

            if (!$ssoKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'SSO key is not configured.'
                ], 500);
            }

            // Decode Base64 encrypted token
            $ciphertext = base64_decode($request->sso_token);

            // Openssl enc format: "Salted__" + salt(8) + ciphertext
            if (substr($ciphertext, 0, 8) !== "Salted__") {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format.'
                ], 400);
            }

            $salt = substr($ciphertext, 8, 8);
            $ciphertext = substr($ciphertext, 16);

            // Derive key + IV
            list($key, $iv) = $this->evp_bytes_to_key($ssoKey, $salt);

            // Decrypt AES-256-CBC
            $decrypted = openssl_decrypt(
                $ciphertext,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token decryption failed.'
                ], 400);
            }

            // Decode JSON payload
            $payload = json_decode($decrypted, true);

            if (!is_array($payload)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid decrypted token payload.'
                ], 400);
            }
            // Extract location
            $locationId = $payload['activeLocation'] ?? null;
            $crmUserId = $payload['userId'] ?? null;

            if (!$locationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID missing from token.'
                ], 400);
            }

            // Match user by location_id
            $user = User::where('id',10)
            // where('crm_location_id', $locationId)
            // ->where('crm_user_id',$crmUserId)
            ->first();
           
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' =>
                    "Location not found in system. Please uninstall and reinstall the app."
                ], 404);
            }

            // Login the user
            Auth::login($user);
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed.'
                ], 500);
            }

            // SUCCESS → Send user + redirect URL
            return response()->json([
                'success' => true,
                'user' => $user,
                'redirect_to' => route('home')
            ]);
        } catch (Exception $e) {
            Log::error("SSO Validation Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error during SSO validation.'
            ], 500);
        }
    }
}
