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
    protected $column = 'id';

    /**
     * ORMModel constructor.
     */
    public function __construct() {
        $this->db = new DB();
    }

    /**
     * Set the column.
     *
     * @param string $column
     */
    public function setColumn(string $column): void {
        $this->column = $column;
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
     * @param mixed $columnName The column to search for.
     * @return array|null The found record as an array, or null if not found.
     */
    public function find($columnName): ?array {
        $query = "SELECT * FROM {$this->table} WHERE {$this->column} = ?";
        return $this->db->sql2array($query, [$columnName])[0] ?? null;
    }

    /**
     * Create a new record.
     *
     * @param array $data The data to be inserted into the database table.
     * @return void
     */
    public function create(array $data): void {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $this->db->sql2db($query, array_values($data));
    }

    /**
     * Update a record.
     *
     * @param mixed $columnName The value of the column to match for the update.
     * @param array $data The data to update the record with.
     * @return void
     */
    public function update($columnName, array $data): void {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $query = "UPDATE {$this->table} SET {$setClause} WHERE {$this->column} = ?";
        $this->db->sql2db($query, array_merge(array_values($data), [$columnName]));
    }

    /**
     * Insert a new record.
     *
     * @param array $data The data to be inserted.
     * @return void
     */
    public function insert(array $data): void {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $this->db->sql2db($query, array_values($data));
    }

    /**
     * Get the count of records in the table.
     *
     * @return int
     */
    public function count(): int {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->sql2array($query);

        return isset($result[0]['count']) ? (int)$result[0]['count'] : 0;
    }

    /**
     * Delete a record by its ID.
     *
     * @param mixed $columnName The value of the column to match for deletion.
     * @return void
     */
    public function delete($columnName): void {
        $query = "DELETE FROM {$this->table} WHERE {$this->column} = ?";
        $this->db->sql2db($query, [$columnName]);
    }
}
?>
