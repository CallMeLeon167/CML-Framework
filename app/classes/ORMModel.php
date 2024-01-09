<?php

namespace CML\Classes;

/**
 * Class ORMModel
 * 
 * The ORMModel class provides a basic model for interacting with a database table.
 *
 * @author CallMeLeon <kontakt@callmeleon.de>
 * @see https://docs.callmeleon.de/cml#ormmodel
 */
class ORMModel {
    protected $table;
    protected $db;

    /**
     * ORMModel constructor.
     */
    public function __construct() {
        $this->db = new DB();
    }

    /**
     * Get all records from the table.
     *
     * @return array
     */
    public function all(): array {
        $query = "SELECT * FROM {$this->table}";
        return $this->db->sql2array($query);
    }

    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->sql2array($query, [$id])[0] ?? null;
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return void
     */
    public function create(array $data): void {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $this->db->sql2db($query, array_values($data));
    }

    /**
     * Update a record by its ID.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function update(int $id, array $data): void {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $query = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        $this->db->sql2db($query, array_merge(array_values($data), [$id]));
    }

    /**
     * Delete a record by its ID.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->sql2db($query, [$id]);
    }
}
?>
