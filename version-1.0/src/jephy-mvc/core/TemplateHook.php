<?php
namespace App\Core;
class TemplateHook
{
    private $hooks;
    
    public function __construct(HookManager $hooks)
    {
        $this->hooks = $hooks;
    }
    
    public function display($hookName, $params = [])
    {
        // Execute the template hook
        $results = $this->hooks->execWithReturn($hookName, $params);
        
        // Return concatenated results
        return implode('', $results);
    }
}