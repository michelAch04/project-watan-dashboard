<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $username;
    private $password;
    private $sender;
    private $apiUrl = 'https://www.bestsmsbulk.com/bestsmsbulkapi/sendSmsAPI.php';
    private $proxyUrl = 'http://18.223.29.194/send-sms';

    public function __construct()
    {
        $this->username = config('services.bestsms.username', 'eliasbarbour');
        $this->password = config('services.bestsms.password', 'Eli321!3');
        $this->sender = config('services.bestsms.sender', 'PWJBEIL');
    }

    /**
     * Send SMS to Lebanese mobile number
     * 
     * @param string $mobile Lebanese mobile number
     * @param string $message Message content
     * @return string Response from SMS service
     */
    public function sendOTP($mobile, $message)
    {
        return true;
        try {
            // Remove any 961 prefix if it exists
            $mobile = preg_replace('/^961/', '', $mobile);
            
            // Build SMS URL with parameters
            $smsUrl = $this->apiUrl . '?' . http_build_query([
                'username' => $this->username,
                'password' => $this->password,
                'senderid' => $this->sender,
                'destination' => '961' . $mobile,
                'message' => $message,
            ]);

            // Log the request
            Log::info('SMS Request', [
                'mobile' => $mobile,
                'message' => $message,
                'url' => $smsUrl
            ]);

            // Call Node.js proxy
            $response = Http::post($this->proxyUrl, [
                'url' => $smsUrl
            ]);

            if (!$response->successful()) {
                Log::error('SMS proxy request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            Log::info('SMS Response', [
                'body' => $response->body()
            ]);

            return $response->body();

                   
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format Lebanese mobile number to international format
     * 
     * @param string $mobile
     * @return string
     */
    private function formatLebaneseMobile($mobile)
    {
        // Remove spaces, dashes, and leading zeros
        $mobile = preg_replace('/[\s\-\+]/', '', $mobile);
        
        // If starts with 0, remove it
        if (substr($mobile, 0, 1) === '0') {
            $mobile = substr($mobile, 1);
        }
        
        // If doesn't start with 961, add it
        if (substr($mobile, 0, 3) !== '961') {
            $mobile = '961' . $mobile;
        }
        
        return $mobile;
    }

    /**
     * Generate 6-digit OTP
     * 
     * @return string
     */
    public static function generateOTP()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}