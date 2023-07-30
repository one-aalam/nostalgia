<?php

namespace Zarf;

use Exception;
use PDO;

const PK = 'id';

function ll($val)
{
    print "<pre>";
    print_r($val);
    print "</pre/>";
}

abstract class Model
{
    private $pdo;
    private $table;
    private $cols;
    private $colNames;

    private $config;

    protected static $connection, $db, $primaryKey = PK;
    function __construct()
    {
        $this->config = defined('CONFIG') && CONFIG['db'] ? CONFIG['db'] : false;
        if (!$this->config) {
            throw new Exception('No DB config provided');
        }
        $this->pdo = $this->pdo ?: $this->connect();
        $this->prepare();
    }

    private function connect()
    {
        try {
            $pdo = new PDO("pgsql:host={$this->config['host']};port=5432;dbname={$this->config['db']};", $this->config['user'], $this->config['password']);
            if ($pdo) {
                // echo "Connection established successfully!";
                return $pdo;
            }
        } catch (\PDOException $e) {
            die($e->getMessage());
        } finally {
            if ($pdo) {
                $pdo = null;
            }
        }
    }

    function findMany(array $where = [])
    {
        $query = 'SELECT * from ' . $this->table;
        $where = !empty($where) ? ' WHERE ' . $this->getWhere($where) : '';
        $stmt = $this->pdo->prepare($query . $where);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function findOne($value, string $field = PK)
    {
        $query = "SELECT * from {$this->table}";
        $values = [
            ':value' => $value
        ];
        $query .= " WHERE {$field} = :value";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
        return $stmt->fetch();
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

    function update($values, $value, string $field = PK)
    {
        $query = "UPDATE {$this->table} SET ";
        $updates = [];
        foreach ($values as $key => $value) {
            $updates[] = $key . ' = :' . $key;
        }

        $query .= implode(', ', $updates);
        $query .= " WHERE {$field} = :value";
        $values = [
            ':value' => $value
        ];

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
    }

    function delete(mixed $value, string $field = PK)
    {
        $query = "DELETE FROM {$this->table} WHERE {$field} = :value";
        $values = [
            ':value' => $value
        ];

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);
    }

    private function prepare()
    {
        $reflectedCls = new \ReflectionClass(get_class($this));
        $this->table = strtolower($reflectedCls->getName()) . 's';
        $this->cols = $this->getCols();
    }

    private function getWhere(array $where): string
    {
        $defaultOp = 'and';
        if (empty($where)) return "";
        if (sizeof($where) == 1) {
            $field = array_key_first($where);
            return "{$field} = {$where[$field]}";
        }
        if (array_key_exists('and', $where) || array_key_exists('or', $where)) {
        } else {
            $whereConditions = [];
            foreach ($where as $field => $value) {
                $whereConditions[] = "{$field} = {$value}";
            }
            return implode(" {$defaultOp} ", $whereConditions);
        }
    }

    private function getCols(): array
    {

        $query = 'SELECT
           column_name,
           data_type
        FROM
           information_schema.columns
        WHERE
           table_name = ' . "'$this->table'";
        $stmt = $this->pdo->query($query);
        $stmt->execute();
        $allCols = $stmt->fetchAll();
        $cols = [];
        foreach ($allCols as $col) {
            $cols[$col['column_name']] = $col['data_type'];
        }
        return $cols;
    }
}
