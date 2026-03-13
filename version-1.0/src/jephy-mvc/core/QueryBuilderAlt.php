<?php
namespace App\Core;
/**
 * Raw SQL expression wrapper
 */
class RawExpression
{
	private string $expression;
	
	public function __construct(string $expression)
	{
		$this->expression = $expression;
	}
	
	public function getExpression(): string
	{
		return $this->expression;
	}
}


class QueryBuilderAlt
{
    private $table;
    private $entityClass;
    private $db;
    private $wheres = [];
    private $orderBy = [];
    private $limit = null;
    private $offset = null;
    private $joins = [];
    private $selects = ['*'];
    private $insertData = [];
    private $updateData = [];
    private $sets = []; // For method chaining UPDATE
    
    public function __construct($table)
    {
        $this->table = $table;
        $this->db = Database::getInstance();
    }
    
    // =============================================
    // SELECT & READ METHODS (EXISTING)
    // =============================================
    
    public function setEntity($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }
    
    public function select($columns)
    {
        if (is_array($columns)) {
            $this->selects = $columns;
        } else {
            $this->selects = func_get_args();
        }
        return $this;
    }
    
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'type' => 'AND'
        ];
        
        return $this;
    }
    
    public function orWhere($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'type' => 'OR'
        ];
        
        return $this;
    }
    
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }
    
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function join($table, $first, $operator, $second, $type = 'INNER')
    {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => $type
        ];
        
        return $this;
    }
    
    public function get()
    {
        $sql = $this->buildSelect();
        $params = $this->getWhereParams();
        
        $stmt = $this->db->query($sql, $params);
        $results = $stmt->fetchAll();
        
        if ($this->entityClass) {
            return array_map(function($item) {
                return new $this->entityClass($item);
            }, $results);
        }
        
        return $results;
    }
    
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
		
		#	echo '<pre>';
		#	print_r( $results );
		#	echo '</pre>';
		
        return !empty($results) ? $results[0] : null;
    }
    
    public function count()
    {
        $this->selects = ['COUNT(*) as count'];
        $result = $this->first();
		return $result ? $result['count'] : 0;
    }
    
    // =============================================
    // ENHANCED UPDATE METHODS (NEW WITH METHOD CHAINING)
    // =============================================
    
    /**
     * Set a column value (method chaining for UPDATE)
     */
    public function set($column, $value)
    {
        $this->sets[$column] = $value;
        return $this;
    }
    
    /**
     * Set multiple column values (method chaining)
     */
    public function setMultiple(array $data)
    {
        foreach ($data as $column => $value) {
            $this->sets[$column] = $value;
        }
        return $this;
    }
    
    /**
     * Increment a column value
     */
    public function increment($column, $amount = 1)
    {
        $this->sets[$column] = new RawExpression("{$column} + {$amount}");
        return $this;
    }
    
    /**
     * Decrement a column value
     */
    public function decrement($column, $amount = 1)
    {
        $this->sets[$column] = new RawExpression("{$column} - {$amount}");
        return $this;
    }
    
    /**
     * Set raw SQL expression
     */
    public function setRaw($column, $expression)
    {
        $this->sets[$column] = new RawExpression($expression);
        return $this;
    }
    
    /**
     * Execute UPDATE with method chaining
     */
    public function update()
    {
        // Use sets array if populated, otherwise use updateData array
        $data = !empty($this->sets) ? $this->sets : $this->updateData;
        
        if (empty($data)) {
            throw new RuntimeException('No data to update. Use set(), setMultiple(), or pass data array.');
        }
        
        $sql = $this->buildUpdateWithSets($data);
        $params = array_merge($this->getUpdateParamsFromSets($data), $this->getWhereParams());
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Execute UPDATE with array (compatible with your existing code)
     */
    public function updateArray(array $data)
    {
        $this->updateData = $data;
        $sql = $this->buildUpdate();
        $params = array_merge($this->getUpdateParams(), $this->getWhereParams());
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Alternative UPDATE (your existing updateAlt method)
     */
    public function updateAlt($data)
    {
        $sets = [];
        $params = $this->getWhereParams();
        
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :set_{$key}";
            $params["set_{$key}"] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        return $this->db->query($sql, $params)->rowCount();
    }
    
    /**
     * Batch update multiple records
     */
    public function updateBatch(array $data, $keyColumn = 'id')
    {
        if (empty($data)) {
            return 0;
        }
        
        $sql = $this->buildBatchUpdate($data, $keyColumn);
        $params = $this->getBatchUpdateParams($data, $keyColumn);
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * UPDATE with JOIN support
     */
    public function updateJoin($table, $first, $operator, $second)
    {
        $data = !empty($this->sets) ? $this->sets : $this->updateData;
        
        if (empty($data)) {
            throw new RuntimeException('No data to update. Use set() or setMultiple() first.');
        }
        
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => 'INNER'
        ];
        
        $sql = $this->buildUpdateWithJoin($data);
        $params = array_merge($this->getUpdateParamsFromSets($data), $this->getWhereParams());
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // =============================================
    // INSERT METHODS (EXISTING)
    // =============================================
    
    public function insert(array $data)
    {
        $this->insertData 	= $data;
        $sql 				= $this->buildInsert();
        $params 			= $this->getInsertParams();
        
        $stmt = $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function insertBatch(array $data)
    {
        if (empty($data)) {
            return 0;
        }
        
        $sql = $this->buildBatchInsert($data);
        $params = $this->getBatchInsertParams($data);
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // =============================================
    // DELETE & UPSERT METHODS (EXISTING)
    // =============================================
    
    public function delete()
    {
        $sql = $this->buildDelete();
        $params = $this->getWhereParams();
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function upsert(array $insertData, array $updateData = null)
    {
        $this->insertData = $insertData;
        $this->updateData = $updateData ?? $insertData;
        
        $sql = $this->buildUpsert();
        $params = array_merge(
            $this->getInsertParams(),
            $this->getUpdateParams()
        );
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // =============================================
    // BUILD METHODS
    // =============================================
    
    private function buildSelect()
    {
        $select = implode(', ', $this->selects);
        $sql = "SELECT {$select} FROM {$this->table}";
        
        // Build joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Build where clauses
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        // Build order by
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        
        // Build limit and offset
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }
        
        return $sql;
    }
    
    private function buildWheres()
    {
        $clauses = [];
        
        foreach ($this->wheres as $index => $where) {
            $clause = $index === 0 ? '' : " {$where['type']} ";
            $clause .= "{$where['column']} {$where['operator']} :where_{$index}";
            $clauses[] = $clause;
        }
        
        return implode('', $clauses);
    }
    
    private function buildInsert()
    {
        $columns = implode(', ', array_keys($this->insertData));
        $placeholders = implode(', ', array_map(function($key) {
            return ":insert_{$key}";
        }, array_keys($this->insertData)));
        
        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }
    
    private function buildBatchInsert(array $data)
    {
        $columns = implode(', ', array_keys($data[0]));
        $rows = [];
        $paramIndex = 0;
        
        foreach ($data as $rowIndex => $row) {
            $placeholders = [];
            foreach ($row as $key => $value) {
                $placeholders[] = ":batch_{$rowIndex}_{$key}";
            }
            $rows[] = '(' . implode(', ', $placeholders) . ')';
        }
        
        return "INSERT INTO {$this->table} ({$columns}) VALUES " . implode(', ', $rows);
    }
    
    private function buildUpdate()
    {
        $sets = [];
        foreach ($this->updateData as $key => $value) {
            $sets[] = "{$key} = :update_{$key}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        return $sql;
    }
    
    private function buildUpdateWithSets(array $sets)
    {
        $setClauses = [];
        foreach ($sets as $key => $value) {
            if ($value instanceof RawExpression) {
                $setClauses[] = "{$value->getExpression()}";
            } else {
                $setClauses[] = "{$key} = :set_{$key}";
            }
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }
        
        return $sql;
    }
    
    private function buildUpdateWithJoin(array $sets)
    {
        $setClauses = [];
        foreach ($sets as $key => $value) {
            if ($value instanceof RawExpression) {
                $setClauses[] = "{$this->table}.{$value->getExpression()}";
            } else {
                $setClauses[] = "{$this->table}.{$key} = :set_{$key}";
            }
        }
        
        $sql = "UPDATE {$this->table}";
        
        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        $sql .= " SET " . implode(', ', $setClauses);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        return $sql;
    }
    
    private function buildBatchUpdate(array $data, $keyColumn)
    {
        $cases = [];
        $ids = [];
        
        foreach ($data as $index => $row) {
            $id = $row[$keyColumn];
            $ids[] = $id;
            
            foreach ($row as $column => $value) {
                if ($column !== $keyColumn) {
                    if (!isset($cases[$column])) {
                        $cases[$column] = [];
                    }
                    $cases[$column][] = "WHEN {$keyColumn} = :id_{$index} THEN :value_{$index}_{$column}";
                }
            }
        }
        
        $sql = "UPDATE {$this->table} SET ";
        
        $setClauses = [];
        foreach ($cases as $column => $whenClauses) {
            $setClauses[] = "{$column} = CASE " . implode(' ', $whenClauses) . " ELSE {$column} END";
        }
        
        $sql .= implode(', ', $setClauses);
        $sql .= " WHERE {$keyColumn} IN (" . implode(', ', array_fill(0, count($ids), '?')) . ")";
        
        return $sql;
    }
    
    private function buildDelete()
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        return $sql;
    }
    
    private function buildUpsert()
    {
        $insertSql = $this->buildInsert();
        
        $updates = [];
        foreach ($this->updateData as $key => $value) {
            $updates[] = "{$key} = VALUES({$key})";
        }
        
        return $insertSql . " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
    }
    
    // =============================================
    // PARAMETER METHODS
    // =============================================
    
    private function getWhereParams()
    {
        $params = [];
        
        foreach ($this->wheres as $index => $where) {
            $params["where_{$index}"] = $where['value'];
        }
        
        return $params;
    }
    
    private function getInsertParams()
    {
        $params = [];
        foreach ($this->insertData as $key => $value) {
            $params["insert_{$key}"] = $value;
        }
        return $params;
    }
    
    private function getBatchInsertParams(array $data)
    {
        $params = [];
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $key => $value) {
                $params["batch_{$rowIndex}_{$key}"] = $value;
            }
        }
        return $params;
    }
    
    private function getUpdateParams()
    {
        $params = [];
        foreach ($this->updateData as $key => $value) {
            $params["update_{$key}"] = $value;
        }
        return $params;
    }
    
    private function getUpdateParamsFromSets(array $sets)
    {
        $params = [];
        foreach ($sets as $key => $value) {
            if (!($value instanceof RawExpression)) {
                $params["set_{$key}"] = $value;
            }
        }
        return $params;
    }
    
    private function getBatchUpdateParams(array $data, $keyColumn)
    {
        $params = [];
        $ids = [];
        
        foreach ($data as $index => $row) {
            $ids[] = $row[$keyColumn];
            
            foreach ($row as $column => $value) {
                if ($column !== $keyColumn) {
                    $params["value_{$index}_{$column}"] = $value;
                }
            }
            $params["id_{$index}"] = $row[$keyColumn];
        }
        
        // Add IDs to params array
        foreach ($ids as $id) {
            $params[] = $id;
        }
        
        return $params;
    }
    
    // =============================================
    // HELPER CLASSES
    // =============================================
    

}



#	/**
#	 * Database singleton class
#	 */
#	class Database
#	{
#	    private static $instance = null;
#	    private $pdo;
#	    
#	    private function __construct()
#	    {
#	        // Configure your database connection here
#	        $config = [
#	            'host' => 'localhost',
#	            'dbname' => 'your_database',
#	            'username' => 'your_username',
#	            'password' => 'your_password',
#	            'charset' => 'utf8mb4'
#	        ];
#	        
#	        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
#	        
#	        try {
#	            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
#	                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
#	                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
#	                PDO::ATTR_EMULATE_PREPARES => false
#	            ]);
#	        } catch (PDOException $e) {
#	            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
#	        }
#	    }
#	    
#	    public static function getInstance(): self
#	    {
#	        if (self::$instance === null) {
#	            self::$instance = new self();
#	        }
#	        return self::$instance;
#	    }
#	    
#	    public function query(string $sql, array $params = []): PDOStatement
#	    {
#	        $stmt = $this->pdo->prepare($sql);
#	        $stmt->execute($params);
#	        return $stmt;
#	    }
#	    
#	    public function lastInsertId(): string
#	    {
#	        return $this->pdo->lastInsertId();
#	    }
#	}