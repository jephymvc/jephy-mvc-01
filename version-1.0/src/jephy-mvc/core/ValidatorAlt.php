<?php
class Validator
{
    private $errors = [];
    private $data = [];
    private $rules = [];
    private $allData = []; // Store ALL original data
    
    public function __construct($data = [])
    {
        $this->data = $data;
        $this->allData = $data; // Store all data
    }
    
    public function make($data, $rules)
    {
        $this->data = $data;
        $this->allData = $data; // Store all original data
        $this->rules = $rules;
        $this->errors = [];
        
        return $this;
    }
    
    // ... existing methods ...
    
    /**
     * Get ALL data (validated + unvalidated)
     */
    public function getAllData()
    {
        return $this->allData;
    }
    
    /**
     * Get validated data only (sanitized)
     */
    public function validated()
    {
        $validated = [];
        
        foreach ($this->rules as $field => $rules) {
            if (isset($this->allData[$field])) {
                $validated[$field] = $this->sanitizeField($field, $this->allData[$field], $rules);
            }
        }
        
        return $validated;
    }
    
    /**
     * Get unvalidated data (raw, not sanitized)
     */
    public function unvalidated()
    {
        $unvalidated = [];
        
        foreach ($this->allData as $field => $value) {
            if (!isset($this->rules[$field])) {
                $unvalidated[$field] = $value;
            }
        }
        
        return $unvalidated;
    }
    
    /**
     * Get all data merged (validated + unvalidated, with sanitization where applicable)
     */
    public function all()
    {
        $all = [];
        
        // Add validated fields (sanitized)
        foreach ($this->rules as $field => $rules) {
            if (isset($this->allData[$field])) {
                $all[$field] = $this->sanitizeField($field, $this->allData[$field], $rules);
            }
        }
        
        // Add unvalidated fields (raw or basic sanitization)
        foreach ($this->allData as $field => $value) {
            if (!isset($this->rules[$field])) {
                // Apply basic sanitization to unvalidated fields for safety
                $all[$field] = $this->basicSanitize($value);
            }
        }
        
        return $all;
    }
    
    /**
     * Basic sanitization for unvalidated fields
     */
    private function basicSanitize($value)
    {
        if (is_array($value)) {
            return $this->sanitizeArray($value);
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $value = trim($value);
            $value = stripslashes($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    /**
     * Check if a specific field exists in data
     */
    public function has($field)
    {
        return isset($this->allData[$field]);
    }
    
    /**
     * Get a specific field value
     */
    public function get($field, $default = null)
    {
        return $this->allData[$field] ?? $default;
    }
    
    /**
     * Get only specific fields
     */
    public function only( $fields )
    {
        $fields = is_array($fields) ? $fields : func_get_args();
        $result = [];
        
        foreach ($fields as $field) {
            if (isset($this->allData[$field])) {
                $result[$field] = $this->allData[$field];
            }
        }
        
        return $result;
    }
    
    /**
     * Get all except specific fields
     */
    public function except($fields)
    {
        $fields = is_array($fields) ? $fields : func_get_args();
        $result = $this->allData;
        
        foreach ($fields as $field) {
            unset($result[$field]);
        }
        
        return $result;
    }
    
    /**
     * Fill missing fields with default values
     */
    public function fillDefaults($defaults)
    {
        foreach ($defaults as $field => $defaultValue) {
            if (!isset($this->allData[$field])) {
                $this->allData[$field] = $defaultValue;
            }
        }
        
        return $this;
    }
	
	
}


