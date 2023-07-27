<?php

$pdo = require 'conn.php';

class DBTable
{
    private $pdo;
    private $table;
    function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    function findAll()
    {
        $query = 'SELECT * from ' . $this->table;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function create($values)
    {
        $query = 'INSERT INTO ' . $this->table . ' (';
        $cols = [];
        $colsPlaceholder = [];
        foreach ($values as $key => $value) {
            $cols[] = '' . $key . '';
            $colsPlaceholder[] = ':' . $key;
        }

        $query .= implode(', ', $cols) . ') VALUES (' . implode(', ', $colsPlaceholder) . ')';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
    }

    function updateById($id, $values)
    {
        $query = 'UPDATE ' . $this->table . ' SET ';
        $updates = [];
        foreach ($values as $key => $value) {
            $updates[] = $key . ' = :' . $key;
        }

        $query .= implode(', ', $updates);
        $query .= ' WHERE id = :primaryKey';

        $values['primaryKey'] = $id;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
    }

    function deleteById($id)
    {
        $query = 'DELETE FROM jokes';
        $query .= ' WHERE id = :primaryKey';
        $values['primaryKey'] = $id;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
    }
}
