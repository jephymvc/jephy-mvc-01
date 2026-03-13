<?php
use Core\Framework;

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        $config = Framework::getConfig();
        return $config->get($key, $default);
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        return Framework::url($path);
    }
}

if (!function_exists('route')) {
    function route($name, $params = [])
    {
        return Framework::route($name, $params);
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        return Framework::asset($path);
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn( $sessionKey )
    {
        return isset( $_SESSION[ "user_session" ][ $sessionKey ] );
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302)
    {
        if (is_string($url) && strpos($url, '/') === 0) {
            $url = url($url);
        } elseif (is_string($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
            // Assume it's a route name
            $url = route($url);
        }
        
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $referer);
        exit;
    }
}

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}


if (!function_exists('rate_limit')) {
    /**
     * Check rate limit manually
     */
    function rate_limit($key, $maxAttempts, $decayMinutes = 1)
    {
        $fullKey = 'rate_limit:' . $key;
        $attempts = $_SESSION[$fullKey] ?? [];
        
        // Clean old attempts
        $currentTime = time();
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $decayMinutes) {
            return ($currentTime - $timestamp) < ($decayMinutes * 60);
        });
        
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        $attempts[] = $currentTime;
        $_SESSION[$fullKey] = $attempts;
        
        return [
            'remaining' => max(0, $maxAttempts - count($attempts)),
            'retry_after' => empty($attempts) ? 0 : (min($attempts) + ($decayMinutes * 60) - $currentTime)
        ];
    }
}

if (!function_exists('clear_rate_limit')) {
    /**
     * Clear rate limit for a key
     */
    function clear_rate_limit($key)
    {
        $fullKey = 'rate_limit:' . $key;
        unset($_SESSION[$fullKey]);
    }
}

if (!function_exists('get_rate_limit_info')) {
    /**
     * Get rate limit information
     */
    function get_rate_limit_info($key, $maxAttempts, $decayMinutes = 1)
    {
        $fullKey = 'rate_limit:' . $key;
        $attempts = $_SESSION[$fullKey] ?? [];
        
        // Clean old attempts
        $currentTime = time();
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $decayMinutes) {
            return ($currentTime - $timestamp) < ($decayMinutes * 60);
        });
        
        $_SESSION[$fullKey] = array_values($attempts);
        
        $remaining = max(0, $maxAttempts - count($attempts));
        $retryAfter = empty($attempts) ? 0 : (min($attempts) + ($decayMinutes * 60) - $currentTime);
        
        return [
            'attempts' => count($attempts),
            'remaining' => $remaining,
            'retry_after' => max(0, $retryAfter),
            'limit' => $maxAttempts,
            'reset' => empty($attempts) ? $currentTime : (min($attempts) + ($decayMinutes * 60))
        ];
    }
}