<?php
namespace App\Core;

class JephyORM
{
    protected $modelClass;
    
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }
    
    public function create(array $params)
    {
        $data = $params['data'] ?? $params;
        return $this->modelClass::create($data);
    }
    
    public function findFirst(array $params = [])
    {
        return $this->modelClass::findFirst($params);
    }
    
    public function findMany(array $params = [])
    {
        return $this->modelClass::findMany($params);
    }
    
    public function update(array $params)
    {
        $data = $params['data'] ?? [];
        $where = $params['where'] ?? [];
        
        // First find the entity
        $entity = $this->modelClass::findFirst(['where' => $where]);
        
        if (!$entity) {
            return false;
        }
        
        // Update attributes
        foreach ($data as $key => $value) {
            $entity->$key = $value;
        }
        
        // Save changes
        return $entity->save();
    }
    
    public function delete(array $params)
    {
        
		$where = $params['where'] ?? [];        
        $entity = $this->modelClass::findFirst(['where' => $where]);
        
        if (!$entity) {
            return false;
        }
        
        return $entity->delete();
		
    }
    
    public function all(array $params = [])
    {
        return $this->findMany($params);
    }
    
    public function count(array $params = [])
    {
        return $this->modelClass::count($params);
    }
    
    public function exists(array $params = [])
    {
        return $this->modelClass::exists($params);
    }
    
    /**
     * Alias for findMany with pagination
     */
    public function paginate(int $page = 1, int $perPage = 10, array $params = [])
    {
        
		$params['skip'] = ($page - 1) * $perPage;
        $params['take'] = $perPage;        
        $data = $this->findMany($params);
        $total = $this->count(isset($params['where']) ? ['where' => $params['where']] : []);        
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' 		=> $data,
            'total' 	=> $total,
            'page' 		=> $page,
            'per_page' 	=> $perPage,
            'pages' 	=> $totalPages,
            'has_next' 	=> $page < $totalPages,
            'has_prev' 	=> $page > 1,
            'next_page' => $page < $totalPages ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null
        ];
		
    }
}
