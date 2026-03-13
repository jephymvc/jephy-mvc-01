<?php
namespace App\Core;
abstract class EntityAlt
{
    protected $table;
    protected $primaryKey 	= 'id';
    protected $attributes 	= [];
    protected $original 	= [];
    protected $timestamps 	= true;
    protected $db;
    protected $hooks;
    
    public function __construct($attributes = [])
    {
        $this->db 		= Database::getInstance();
        $this->hooks 	= Framework::getHooks();
        
        if (empty($this->table)) {
            $className = (new ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
        
        $this->fill($attributes);
    }
    
    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
                $this->attributes[$key] = $value;
            }
        }
        $this->original = $this->attributes;
    }
    
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return null;
    }
    
    public function __set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        $this->attributes[$key] = $value;
    }
    
    public function save()
    {
        // Execute before save hook
        $this->hooks->exec('beforeSave', [
            'entity' => $this,
            'attributes' => $this->attributes
        ]);
        
        if ($this->isNew()) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }
    
    public function insert()
    {
        if ($this->timestamps) {
            $this->attributes['created_at'] = date('Y-m-d H:i:s');
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // Execute before insert hook
        $result = $this->hooks->exec('beforeInsert', [
            'entity' => $this,
            'attributes' => $this->attributes
        ]);
        
        if ($result === false) {
            return false;
        }
        
        $columns 		= implode( ', ', array_keys( $this->attributes ) );
        $placeholders 	= ':' . implode( ', :', array_keys( $this->attributes ) );		        
        $sql 			= "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";        
        $stmt 			= $this->db->query( $sql, $this->attributes );
        
        if ($stmt) {
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            $this->original = $this->attributes;
            
            // Execute after insert hook
            $this->hooks->exec('afterInsert', [
                'entity' => $this,
                'id' => $this->attributes[$this->primaryKey]
            ]);
            
            return true;
        }
        
        return false;
    }
    
    public function update()
    {
        if ($this->timestamps) {
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // Execute before update hook
        $result = $this->hooks->exec('beforeUpdate', [
            'entity' => $this,
            'attributes' => $this->attributes,
            'original' => $this->original
        ]);
        
        if ($result === false) {
            return false;
        }
        
        $updates = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $updates[] = "{$key} = :{$key}";
            }
        }
        
        $setClause = implode(', ', $updates);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        
        $params = $this->attributes;
        $params['id'] = $this->original[$this->primaryKey];
        
        $stmt = $this->db->query($sql, $params);
        
        if ($stmt) {
            $this->original = $this->attributes;
            
            // Execute after update hook
            $this->hooks->exec('afterUpdate', [
                'entity' => $this,
                'id' => $this->attributes[$this->primaryKey]
            ]);
            
            return true;
        }
        
        return false;
    }
    
    public function delete()
    {
        if ($this->isNew()) {
            return false;
        }
        
        // Execute before delete hook
        $result = $this->hooks->exec('beforeDelete', [
            'entity' => $this,
            'id' => $this->attributes[$this->primaryKey]
        ]);
        
        if ($result === false) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $this->attributes[$this->primaryKey]]);
        
        if ($stmt) {
            // Execute after delete hook
            $this->hooks->exec('afterDelete', [
                'entity' => $this,
                'id' => $this->attributes[$this->primaryKey]
            ]);
            
            return true;
        }
        
        return false;
    }
    
    public function isNew()
    {
        return empty($this->original[$this->primaryKey]);
    }
    
    public function toArray()
    {
        return $this->attributes;
    }
    
    public static function find($id)
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        $stmt = $instance->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result) {
            return new static($result);
        }
        
        return null;
    }
    
    public static function findBy( $column, $value )
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :{$column} LIMIT 1";
        $stmt = $instance->db->query( $sql, [ $column => $value ] );
        $result = $stmt->fetch();
        
        if ($result) {
            return new static($result);
        }
        
        return null;
    }
    
    public static function where($column, $operator, $value = null)
    {
        $instance = new static();
        
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        return (new QueryBuilder($instance->table))
            ->where($column, $operator, $value)
            ->setEntity(get_called_class());
    }
    
    public static function all()
    {
        $instance = new static();
        return (new QueryBuilder($instance->table))
            ->setEntity(get_called_class())
            ->get();
    }
}


