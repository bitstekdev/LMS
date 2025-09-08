<?php

namespace App\Services;

use Exception;

class ZoomMeetingService
{
    public static function createToken(): ?string
    {
        $clientId = get_settings('zoom_client_id');
        $clientSecret = get_settings('zoom_client_secret');
        $accountId = get_settings('zoom_account_id');

        $url = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$accountId}";
        $authHeader = 'Basic '.base64_encode("{$clientId}:{$clientSecret}");

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Authorization: '.$authHeader],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $json = json_decode($response, true);

                return $json['access_token'] ?? null;
            }

            logger()->error("Zoom Token Request Failed: [{$httpCode}] {$response}");
        } catch (Exception $e) {
            logger()->error('Zoom Token Error: '.$e->getMessage());
        }

        return null;
    }

    public static function createMeeting(string $topic, int|string $time, int $duration): string
    {
        $token = self::createToken();
        if (! $token) {
            return json_encode([
                'code' => 401,
                'message' => 'Unable to authenticate with Zoom',
            ]);
        }

        $zoomEmail = get_settings('zoom_account_email');
        $endpoint = 'https://api.zoom.us/v2/users/me/meetings';

        $data = [
            'topic' => $topic,
            'schedule_for' => $zoomEmail,
            'type' => 2,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($time)),
            'duration' => $duration,
            'timezone' => get_settings('timezone') ?? 'Asia/Dhaka',
            'settings' => [
                'approval_type' => 2,
                'join_before_host' => true,
                'jbh_time' => 0,
            ],
        ];

        return self::sendZoomRequest($endpoint, 'POST', $data, $token);
    }

    public static function updateMeeting(string $topic, string $time, string $meetingId): string
    {
        $token = self::createToken();
        if (! $token) {
            return json_encode([
                'code' => 401,
                'message' => 'Unable to authenticate with Zoom',
            ]);
        }

        $endpoint = "https://api.zoom.us/v2/meetings/{$meetingId}";

        $data = [
            'topic' => $topic,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($time)),
        ];

        return self::sendZoomRequest($endpoint, 'PATCH', $data, $token);
    }

    public static function deleteMeeting(string $meetingId): string
    {
        $token = self::createToken();
        if (! $token) {
            return json_encode([
                'code' => 401,
                'message' => 'Unable to authenticate with Zoom',
            ]);
        }

        $endpoint = "https://api.zoom.us/v2/meetings/{$meetingId}";

        return self::sendZoomRequest($endpoint, 'DELETE', [], $token);
    }

    private static function sendZoomRequest(string $url, string $method, array $data, string $token): string
    {
        $ch = curl_init($url);
        $payload = in_array($method, ['POST', 'PATCH']) ? json_encode($data) : null;

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function config(): array
    {
        return [
            'clientId' => get_settings('zoom_client_id'),
            'clientSecret' => get_settings('zoom_client_secret'),
            'accountId' => get_settings('zoom_account_id'),
            'email' => get_settings('zoom_account_email'),
        ];
    }
}
