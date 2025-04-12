<?php

/**
 * Database management class using PDO with singleton pattern.
 */
class DB
{
    /** @var DB|null Singleton instance of the class. */
    private static ?DB $instance = null;

    /** @var PDO PDO instance for database operations. */
    private PDO $pdo;

    /** @var bool Transaction state flag. */
    private bool $transaction = false;

    /** @var bool Transaction state flag. */
    private string $driver = '';

    /**
     * Private constructor to prevent multiple instances.
     *
     * @param array $config Database configuration settings.
     */
    private function __construct(array $config)
    {
        try {
            $dsn = $this->getDsn($config);
            $this->pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true,
            ]);
        } catch (PDOException $e) {
            throw new Exception($this->translateError($e));
        }
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @return DB The singleton instance.
     */
    public static function getInstance(): DB
    {
        if (self::$instance === null) {
            self::$instance = new DB(Config::get('database'));
        }
        return self::$instance;
    }

    /**
     * Generates the Data Source Name (DSN) string based on the configuration.
     *
     * @param array $config Database configuration settings.
     * @return string The formatted DSN string.
     * @throws Exception If the database driver is not supported.
     */
    private function getDsn(array $config): string
    {
        $driver = $config['driver'];
        $host = $config['host'];
        $database = $config['database'];
        $charset = $config['charset'];
        $collation = $config['collation'];
        $port = $config['port'];
        $file = $config['file'];
        $options = $config['options'] ?? [];

        $dsnTemplates = [
            'mysql' => "mysql:host={$host};dbname={$database};charset={$charset}" .
                ($collation ? ";collation={$collation}" : '') . ($port ? ";port={$port}" : ''),
            'pgsql' => "pgsql:host={$host};dbname={$database};charset={$charset}" .
                ($port ? ";port={$port}" : ''),
            'sqlsrv' => "sqlsrv:Server={$host};Database={$database}" .
                ($port ? ";port={$port}" : ''),
            'sqlite' => "sqlite:" . ROOT . "/{$file}",
        ];

        if (!isset($dsnTemplates[$driver]) && !str_contains($driver, ':')) {
            throw new Exception("Unsupported database driver: {$this->driver}");
        }

        $dsn = $dsnTemplates[$driver] ?? $driver;
        $this->driver = preg_replace('/^(\w+):.*$/', '$1', $dsn);
        return !empty($options) ? $dsn . ';' . http_build_query($options, '', ';') : $dsn;
    }

    /**
     * @return string the db type connection, or driver, name
     * example: mysql, sqllite, pgsql.
     */
    public function type()
    {
        return $this->driver;
    }

    /**
     * Executes a SQL query with parameters and returns the result set.
     *
     * @param string $sql The SQL query.
     * @param array $params Query parameters.
     * @return mixed The result set.
     */
    public function query(string $sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("DB Exception: " . $e->getMessage());
            throw new PDOException(self::translateError($e));
            // throw new PDOException($e->getMessage());
        }
    }

    private static function bindParams($stmt, $params)
    {
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $stmt->bindValue($key + 1, $value);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
    }

    /**
     * Executes a SQL statement without returning a result set.
     *
     * @param string $sql The SQL query.
     * @param array $params Query parameters.
     * @return mixed True on success, false on failure.
     */
    public function execute(string $sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("DB Exception: " . $e->getMessage());
            $error = self::translateError($e);
            // throw new PDOException($e->getMessage());
            throw new PDOException(self::translateError($e));
        }
    }

    /**
     * Inserts a new record into the database.
     *
     * @param string $table The table name.
     * @param array $data Associative array of column => value.
     * @return string The last inserted ID.
     */
    public function insert(string $table, array $data): string|bool
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        if ($this->execute($sql, array_values($data))) {
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * Updates records in the database.
     *
     * @param string $table The table name.
     * @param array $data Associative array of column => value.
     * @param string $where The WHERE clause.
     * @param array $whereParams Parameters for the WHERE clause.
     * @return bool True on success, false on failure.
     */
    public function update(string $table, array $data, string $where, array $whereParams): bool
    {
        $setClause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        return $this->execute($sql, array_merge(array_values($data), $whereParams));
    }

    /**
     * Deletes records from the database.
     *
     * @param string $table The table name.
     * @param string $where The WHERE clause.
     * @param array $whereParams Parameters for the WHERE clause.
     * @return bool True on success, false on failure.
     */
    public function delete(string $table, string $where, array $whereParams): bool
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->execute($sql, $whereParams);
    }

    /**
     * Starts a database transaction.
     */
    public function beginTransaction(): void
    {
        if (!$this->transaction) {
            $this->pdo->beginTransaction();
            $this->transaction = true;
        }
    }

    /**
     * Commits the current transaction.
     */
    public function commit(): void
    {
        if ($this->transaction) {
            $this->pdo->commit();
            $this->transaction = false;
        }
    }

    /**
     * Rolls back the current transaction.
     */
    public function rollback(): void
    {
        if ($this->transaction) {
            $this->pdo->rollBack();
            $this->transaction = false;
        }
    }

    /**
     * Executes a function within a transaction.
     *
     * @param callable $callback The function to execute.
     * @return mixed The result of the callback function.
     * @throws Exception Rolls back the transaction if an error occurs.
     */
    public function onTransaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Retrieves the last inserted ID.
     *
     * @return string The last inserted ID.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Executes an SQL statement and returns the number of affected rows.
     * @param string $sql The SQL query.
     * @return int The number of affected rows.
     * @throws PDOException If the query fails.
     */
    public function sqlexec($sql)
    {
        return $this->pdo->exec($sql);
    }

    /**
     * Translates a PDOException into a human-readable error message.
     *
     * This function attempts to provide more context for database errors by:
     * - Extracting specific details from the error message, such as table or column names.
     * - Mapping SQLSTATE codes to descriptive error messages.
     * - Appending additional information, such as the offending SQL query snippet.
     *
     * @param PDOException $e The exception thrown by PDO.
     * @param string $query Optional. The SQL query that caused the error, for context.
     * @return string A translated, human-readable error message.
     */
    private function translateError(PDOException $e)
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        $extraInfo = '';

        // Tenta extrair detalhes específicos da mensagem de erro
        if (preg_match('/Table \'([^\']+)\' doesn\'t exist/', $message, $matches)) {
            $extraInfo = "Table: `{$matches[1]}`";
        } elseif (preg_match('/Unknown column \'([^\']+)\' in/', $message, $matches)) {
            $extraInfo = "Column: `{$matches[1]}`";
        } elseif (preg_match('/Duplicate entry \'([^\']+)\' for key \'([^\']+)\'/', $message, $matches)) {
            $extraInfo = "Value: `{$matches[1]}`, Key: `{$matches[2]}`";
        } elseif (preg_match('/FOREIGN KEY \(`([^`]+)`\)/', $message, $matches)) {
            $extraInfo = "Foreign Key Constraint: `{$matches[1]}`";
        }

        // Tabela de tradução de erros SQLSTATE
        $errorMessages = [
            '08001' => 'Unable to connect to the database server. Check host and port',
            '08004' => 'Server rejected the connection. Ensure the database allows remote access',
            '28000' => 'Invalid credentials. Check username and password',
            '42S02' => 'Table not found. Ensure the table exists before querying',
            '42S22' => 'Column not found. Check column names in your query',
            '23000' => 'Integrity constraint violation. Possible duplicate entry or foreign key issue',
            '23502' => 'NOT NULL constraint violation. A required field is missing',
            '23503' => 'Foreign key violation. Ensure related records exist before inserting or deleting',
            '23505' => 'Unique constraint violation. A record with the same value already exists',
            '40001' => 'Deadlock detected. Try the transaction again later',
            '40P01' => 'Deadlock condition. Transaction rolled back',
            '22001' => 'Data too long for column. Check field length',
            '22007' => 'Invalid datetime format. Ensure date values are correctly formatted',
            '22012' => 'Division by zero error',
            'HY000' => 'General database error',
            'HY001' => 'Memory allocation error. System may be low on memory',
            'HYT00' => 'Timeout expired. The query took too long to execute'
        ];

        $translatedMessage = "$message\n-- ". $errorMessages[$code] ?? "Unexpected database error (SQLSTATE: $code).";

        // Se houver informação extra, adiciona ao erro
        if ($extraInfo) {
            $translatedMessage .= ": [$extraInfo]";
        }
        return $translatedMessage;
    }
}
