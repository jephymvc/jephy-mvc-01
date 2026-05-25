<?php
// app/core/View.php
namespace App\Core;

use App\Core\Framework;

class View
{
    private $smarty;
    private $data = [];
    private static $globalData = [];
    
    public function __construct()
    {
        $this->smarty = Framework::getSmarty();
    }
    
    /**
     * Set a view variable
     */
    public function set($key, $value)
    {
        // Handle dot notation for nested arrays
        if (strpos($key, '.') !== false) {
            $this->setNested($key, $value);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Set nested value using dot notation
     */
    private function setNested($key, $value)
    {
        $parts = explode('.', $key);
        $current = &$this->data;
        
        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }
    
    /**
     * Set multiple view variables
     */
    public function setMultiple(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }
    
    /**
     * Set a global variable (available in all views)
     */
    public static function setGlobal($key, $value)
    {
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $current = &self::$globalData;
            
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    if (!isset($current[$part]) || !is_array($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
        } else {
            self::$globalData[$key] = $value;
        }
    }
    
    /**
     * Get all data for this view
     */
    public function getData()
    {
        return array_merge(self::$globalData, $this->data);
    }
    
    /**
     * Render a template
     */
    public function render($template)
    {
        // Merge global and local data
        $allData = $this->getData();
        
        // Assign all data to Smarty
        foreach ($allData as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        
        // Normalize template path
        $templatePath = $this->normalizePath($template);
        
        // Execute beforeRender hook
        $hooks = Framework::getHooks();
        $hooks->exec('beforeRender', [
            'template' => $templatePath,
            'data' => $allData,
            'smarty' => $this->smarty
        ]);
        
        // Return rendered template
        return $this->smarty->fetch($templatePath);
    }
    
    /**
     * Normalize template path
     */
    private function normalizePath($template)
    {
        // If already has .tpl, return as is
        if (substr($template, -4) === '.tpl') {
            return $template;
        }
        
        // Replace dots with directory separators
        $template = str_replace('.', '/', $template);
        
        // Add .tpl extension
        return $template . '.tpl';
    }
    
    /**
     * Display a template directly
     */
    public function display($template)
    {
        echo $this->render($template);
    }
    
    /**
     * Check if a view exists
     */
    public function exists($template)
    {
        $templatePath = $this->normalizePath($template);
        $templateDirs = $this->smarty->getTemplateDir();
        
        foreach ($templateDirs as $dir) {
            if (file_exists($dir . $templatePath)) {
                return true;
            }
        }
        
        return false;
    }
}