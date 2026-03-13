<?php
namespace App\Hooks;
use App\Core\Framework;
use App\Core\HookManager;
class LoggingHooks
{
    public function registerHooks($hooks)
    {
        $hooks->registerHook('afterAddComment', [$this, 'logComment']);
        $hooks->registerHook('onError', [$this, 'logError']);
    }
    
    public function logComment($params)
    {
        error_log("User {$params['user_id']} commented on post {$params['post_id']}");
        return $params; // Don't modify, just log
    }
    
    public function logError($params)
    {
        error_log("Error: {$params['exception']->getMessage()}");
        return $params;
    }
}