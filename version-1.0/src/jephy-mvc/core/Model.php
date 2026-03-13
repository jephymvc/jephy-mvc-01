<?php
namespace App\Core;
abstract class Model
{
    protected $db;
    protected $hooks;
    
    public function __construct()
    {
        $this->db = Framework::getDatabase();
        $this->hooks = Framework::getHooks();
    }
    
    protected function getTableName()
    {
        return strtolower(str_replace('Model', '', get_class($this)));
    }
}