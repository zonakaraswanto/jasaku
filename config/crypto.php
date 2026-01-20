<?php
function app_key() { static $k; if (!$k) { $k = hash('sha256', getenv('APP_KEY') ?: 'changeme', true); } return $k; }
function b64u($d) { return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); }
function b64ud($d) { return base64_decode(strtr($d, '-_', '+/')); }
function enc_token($arr) {
    $payload = json_encode($arr);
    if (function_exists('openssl_encrypt') && function_exists('random_bytes')) {
        $iv = random_bytes(16);
        $ct = openssl_encrypt($payload, 'AES-256-CBC', app_key(), OPENSSL_RAW_DATA, $iv);
        return b64u($iv . $ct);
    }
    $sig = hash_hmac('sha256', $payload, app_key(), true);
    return b64u($sig . $payload);
}
function dec_token($t) {
    $raw = b64ud($t);
    if ($raw === false) return null;
    if (function_exists('openssl_decrypt')) {
        if (strlen($raw) < 17) return null;
        $iv = substr($raw, 0, 16);
        $ct = substr($raw, 16);
        $pt = openssl_decrypt($ct, 'AES-256-CBC', app_key(), OPENSSL_RAW_DATA, $iv);
        if ($pt === false) return null;
        $arr = json_decode($pt, true);
        return is_array($arr) ? $arr : null;
    }
    if (strlen($raw) < 33) return null;
    $sig = substr($raw, 0, 32);
    $payload = substr($raw, 32);
    $calc = hash_hmac('sha256', $payload, app_key(), true);
    if (!hash_equals($sig, $calc)) return null;
    $arr = json_decode($payload, true);
    return is_array($arr) ? $arr : null;
}
