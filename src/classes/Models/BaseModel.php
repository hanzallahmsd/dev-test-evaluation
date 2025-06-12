<?php
namespace Models;

abstract class BaseModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    /**
     * Find a record by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find($id)
    {
        $stmt = db()->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all records
     * 
     * @return array
     */
    public function all()
    {
        $stmt = db()->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return int The ID of the inserted record
     */
    public function create(array $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(function($key) {
            return ":{$key}";
        }, array_keys($data)));
        
        $stmt = db()->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return db()->lastInsertId();
    }
    
    /**
     * Update a record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        $setClause = implode(', ', array_map(function($key) {
            return "{$key} = :{$key}";
        }, array_keys($data)));
        
        $data[$this->primaryKey] = $id;
        
        $stmt = db()->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}");
        return $stmt->execute($data);
    }
    
    /**
     * Delete a record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = db()->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Find records by a specific field
     * 
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findBy($field, $value)
    {
        $stmt = db()->prepare("SELECT * FROM {$this->table} WHERE {$field} = :value");
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find a single record by a specific field
     * 
     * @param string $field
     * @param mixed $value
     * @return array|null
     */
    public function findOneBy($field, $value)
    {
        $stmt = db()->prepare("SELECT * FROM {$this->table} WHERE {$field} = :value LIMIT 1");
        $stmt->execute(['value' => $value]);
        return $stmt->fetch();
    }
}
