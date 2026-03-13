<?php
namespace App\Core;
class HookManager
{
    private $hooks = [];
    private $disabledHooks = [];
    
    public function registerHook($hookName, $callback, $priority = 10)
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        
        $this->hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        
        // Sort by priority
        usort($this->hooks[$hookName], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    public function exec($hookName, $params = [])
    {
        if (in_array($hookName, $this->disabledHooks) || !isset($this->hooks[$hookName])) {
            return $params;
        }
        
        foreach ($this->hooks[$hookName] as $hook) {
            $result = call_user_func_array($hook['callback'], [$params]);
            
            // If hook returns something, use it as modified params
            if ($result !== null) {
                $params = $result;
            }
        }
        
        return $params;
    }
    
    public function execWithReturn($hookName, $params = [])
    {
        $results = [];
        
        if (in_array($hookName, $this->disabledHooks) || !isset($this->hooks[$hookName])) {
            return $results;
        }
        
        foreach ($this->hooks[$hookName] as $hook) {
            $result = call_user_func_array($hook['callback'], [$params]);
            if ($result !== null) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
}