<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use RuntimeException;

class LineLoginService
{
    private string $channelId;
    private string $channelSecret;
    private string $callbackUrl;

    public function __construct()
    {
        // 1. Fetch settings from DB
        $pdo = Database::getInstance();
        $settingsRaw = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('LINE_CHANNEL_ID', 'LINE_CHANNEL_SECRET')")->fetchAll();
        $settings = [];
        foreach ($settingsRaw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // 2. Assign keys (Fallback to ENV if DB is empty for backward compatibility)
        $this->channelId     = !empty($settings['LINE_CHANNEL_ID']) ? $settings['LINE_CHANNEL_ID'] : ($_ENV['LINE_CHANNEL_ID'] ?? '');
        $this->channelSecret = !empty($settings['LINE_CHANNEL_SECRET']) ? $settings['LINE_CHANNEL_SECRET'] : ($_ENV['LINE_CHANNEL_SECRET'] ?? '');

        // 3. Set callbackUrl rigidly to avoid any mismatch with LINE Developer Console
        $this->callbackUrl = 'https://panlingyi.tw/api/line-callback.php';
    }

    /**
     * ?–å? LINE Login ?ˆæ?ç¶²å?
     */
    public function getLoginUrl(string $state, ?string $customCallbackUrl = null): string
    {
        if ($this->channelId === '') {
            error_log('LINE_CHANNEL_ID is not configured');
            // Return to homepage as fallback if not setup
            return '/';
        }

        $params = [
            'response_type' => 'code',
            'client_id'     => $this->channelId,
            'redirect_uri'  => $customCallbackUrl ?? $this->callbackUrl,
            'state'         => $state,
            'scope'         => 'profile openid',
            'bot_prompt'    => 'aggressive' // å¼·åˆ¶å¼•å?? å…¥å®˜æ–¹å¸³è?å¥½å? (Link OA)
        ];

        return 'https://access.line.me/oauth2/v2.1/authorize?' . http_build_query($params);
    }

    /**
     * ??Authorization Code äº¤æ? Access Token
     */
    public function getAccessToken(string $code, ?string $customCallbackUrl = null): ?string
    {
        $url = 'https://api.line.me/oauth2/v2.1/token';
        $data = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $customCallbackUrl ?? $this->callbackUrl,
            'client_id'     => $this->channelId,
            'client_secret' => $this->channelSecret,
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            error_log('Failed to fetch LINE Access Token');
            return null;
        }

        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }

    /**
     * ?–å?ä½¿ç”¨?…ç? Profile (?…å« userId, displayName, pictureUrl)
     */
    public function getUserProfile(string $accessToken): ?array
    {
        $url = 'https://api.line.me/v2/profile';

        $options = [
            'http' => [
                'header' => "Authorization: Bearer {$accessToken}\r\n",
                'method' => 'GET',
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            error_log('Failed to fetch LINE User Profile');
            return null;
        }

        $result = json_decode($response, true);
        
        // Ensure userId exists to consider it a success
        if (isset($result['userId'])) {
            return $result;
        }
        
        return null;
    }
}
