<?php
namespace App\Core;

class HookManager
{
    private $hooks = [];
    private $disabledHooks = [];
    
    /**
     * Register a hook
     */
    public function registerHook($hookName, $callback, $priority = 10)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Hook callback must be callable for hook: {$hookName}");
        }
        
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        
        $this->hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => (int)$priority
        ];
        
        // Sort by priority (lower number = higher priority)
        usort($this->hooks[$hookName], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    /**
     * Execute hooks - pass arguments as array
     */
    public function exec($hookName, $params = [])
    {
        if (in_array($hookName, $this->disabledHooks) || !isset($this->hooks[$hookName])) {
            return $params;
        }
        
        $modifiedParams = $params;
        
        foreach ($this->hooks[$hookName] as $hook) {
            // Always pass $params as a single array argument
            $result = call_user_func($hook['callback'], $modifiedParams);
            
            // If hook returns an array, use it as new parameters
            if (is_array($result)) {
                $modifiedParams = $result;
            }
            // If hook returns a non-null scalar, replace params
            elseif ($result !== null) {
                $modifiedParams = $result;
            }
        }
        
        return $modifiedParams;
    }
    
    /**
     * Execute hooks with multiple arguments
     * Use this when you need to pass multiple separate arguments
     */
    public function fire($hookName, ...$args)
    {
        if (in_array($hookName, $this->disabledHooks) || !isset($this->hooks[$hookName])) {
            return;
        }
        
        foreach ($this->hooks[$hookName] as $hook) {
            call_user_func_array($hook['callback'], $args);
        }
    }
    
    /**
     * Execute hooks and collect their return values
     */
    public function execWithReturn($hookName, $params = [])
    {
        $results = [];
        
        if (in_array($hookName, $this->disabledHooks) || !isset($this->hooks[$hookName])) {
            return $results;
        }
        
        foreach ($this->hooks[$hookName] as $hook) {
            // Pass parameters as single array argument
            $result = call_user_func($hook['callback'], $params);
            
            if ($result !== null) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    public function hasHooks($hookName)
    {
        return isset($this->hooks[$hookName]) && !empty($this->hooks[$hookName]);
    }
}