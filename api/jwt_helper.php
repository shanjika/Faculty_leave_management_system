<?php
// Simple JWT implementation — no Composer needed

define('JWT_SECRET', 'leave_mgmt_secret_key_2026_secure');
define('JWT_EXPIRY', 60 * 60 * 24); // 24 hours

class JWT {

    public static function encode($payload) {
        $header  = self::base64url(json_encode(['alg'=>'HS256','typ'=>'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;
        $pay     = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$pay", JWT_SECRET, true));
        return "$header.$pay.$sig";
    }

    public static function decode($token) {
        $parts = explode('.', $token);
        if(count($parts) !== 3) return null;

        [$header, $payload, $sig] = $parts;

        // Verify signature
        $expected = self::base64url(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
        if(!hash_equals($expected, $sig)) return null;

        // Decode payload
        $data = json_decode(self::base64url_decode($payload), true);
        if(!$data) return null;

        // Check expiry
        if(isset($data['exp']) && $data['exp'] < time()) return null;

        return $data;
    }

    private static function base64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}