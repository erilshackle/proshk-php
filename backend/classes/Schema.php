<?php


class Schema
{
    protected static string $mode = 'mysql';
    protected string $table;
    protected ?string $id = null;
    protected array $columns = [];
    protected array $constraints = [];
    protected static bool $foreignKeyEnabled = false;
    protected static bool $uuidEnabled = false;

    protected array $dataTypes = [
        'id' => [
            'mysql' => 'INT PRIMARY KEY',
            'sqlite' => 'INTEGER PRIMARY KEY',
            'pgsql' => 'INTEGER PRIMARY KEY'
        ],
        'autoincrement' => [
            'mysql' => 'INT PRIMARY KEY AUTO_INCREMENT',
            'sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'pgsql' => 'INTEGER GENERATED ALWAYS AS IDENTITY'
        ],
        'uuid' => [
            'mysql' => 'CHAR(36)',
            'sqlite' => 'TEXT',
            'pgsql' => 'UUID'
        ],
        'string' => [
            'mysql' => 'VARCHAR(255)',
            'sqlite' => 'TEXT',
            'pgsql' => 'VARCHAR(255)'
        ],
        'varchar' => [
            'mysql' => 'VARCHAR(255)',
            'sqlite' => 'TEXT',
            'pgsql' => 'VARCHAR(255)'
        ],
        'boolean' => [
            'mysql' => 'TINYINT(1)',
            'sqlite' => 'BOOLEAN',
            'pgsql' => 'BOOLEAN'
        ],
        'bool' => [
            'mysql' => 'TINYINT(1)',
            'sqlite' => 'BOOLEAN',
            'pgsql' => 'BOOLEAN'
        ],
        'timestamp' => [
            'mysql' => 'TIMESTAMP',
            'sqlite' => 'DATETIME',
            'pgsql' => 'TIMESTAMP'
        ],
        'serial' => 'SERIAL',
        'bigserial' => 'BIGSERIAL',
        'nvarchar' => 'NVARCHAR(100)',
        'tinyint' => 'TINYINT(1)',
        'decimal' => 'DECIMAL(10, 2)',

    ];

    protected array $defaultTypes = [
        'NULL',
        'TRUE',
        'FALSE',
        'CURRENT_TIMESTAMP',
        'CURRENT_DATE',
        'CURRENT_TIME',
        'NOW()',
        'CURRENT_USER',
        'UUID()', // Postgres
        'UUID' // MySQL
    ];

    protected array $sqlTypes = [
        'mysql' => [
            'primaryKey' => 'PRIMARY KEY',
            'autoIncrement' => 'AUTO_INCREMENT',
            'onupdate_timestamp' => 'ON UPDATE CURRENT_TIMESTAMP',
            'enum' => 'ENUM(:values)',
            'string' => 'VARCHAR(255)',
            'default_uuid' => 'DEFAULT (UUID())',
        ],
        'sqlite' => [
            'primaryKey' => 'PRIMARY KEY',
            'autoIncrement' => 'AUTOINCREMENT',
            'onupdate_timestamp' => '',
            'enum' => 'TEXT CHECK(:column IN(:values))',
            'string' => 'TEXT',
            'default_uuid' => 'DEFAULT (lower(hex(randomblob(16))))',
        ],
        'pgsql' => [
            'primaryKey' => 'PRIMARY KEY',
            'autoIncrement' => 'SERIAL',
            'onupdate_timestamp' => '',
            'enum' => 'TEXT CHECK(:column IN(:values))',
            'string' => 'VARCHAR(255)',
            'default_uuid' => 'DEFAULT uuid_generate_v4()',
        ]
    ];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function getTable(): array
    {
        return [$this->table, $this->id];
    }

    public static function setType(string $mode = 'mysql'): void
    {
        self::$mode = in_array($mode, ['mysql', 'sqlite', 'pgsql'] + PDO::getAvailableDrivers()) ? $mode : 'mysql';
    }

    public static function create($table, ?callable $callback): self
    {
        $schema = new self($table);
        // if ($callback !== null)
        $callback($schema);
        if ($schema->id === null) {
            throw new Exception("$table Table has no definition of PRIMARY KEY.");
        }
        return $schema;
    }

    public function column(string $name, string $type, bool $nullable = true, mixed $default = null, ?string $check = null): self
    {
        $type = $this->dataTypes[strtolower($type)] ?? $type;

        if (is_array($type) && array_key_exists(self::$mode, $type)) {
            $type = $type[self::$mode];
        }

        $column = $name . ' ' . strtoupper($type);

        if (!$nullable) {
            $column .= " NOT NULL";
        }

        if (!is_null($default)) {
            if (!in_array(strtoupper($default), $this->defaultTypes)) {
                $default = is_numeric($default) ? $default : "'{$default}'";
            } else {
                $default = strtoupper($default);
            }
            $column .= " DEFAULT " . $default;
        }

        if ($check) {
            $column .= " CHECK ($check)";
        }

        $this->columns[] = trim($column);
        return $this;
    }

    public function primaryKey(string|array $column): self
    {
        if ($this->id !== null)
            throw new RuntimeException("Primary key already set for table {$this->table}");

        if (is_array($column)) {
            $column = implode(', ', $column);
        }
        $this->id = $column;
        $this->constraints[] = "PRIMARY KEY ($column)";
        return $this;
    }

    public function foreignKey(string $column, string $referencesTable, string $referencesColumn, string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE'): self
    {
        $this->constraints[] = "FOREIGN KEY ($column) REFERENCES $referencesTable($referencesColumn) ON DELETE $onDelete ON UPDATE $onUpdate";
        return $this;
    }

    public function index(string $column, string $indexName = ''): self
    {
        $indexName = $indexName ?: "idx_{$this->table}_{$column}";
        $this->constraints[] = "INDEX $indexName ($column)";
        return $this;
    }

    public function unique(string|array $column, bool $indexName = false): self
    {
        if (is_array($column)) {
            $column = implode(', ', $column);
        }
        $indexName = $indexName ? "uniq_{$this->table}_{$column}" : '';
        $this->constraints[] = "UNIQUE $indexName($column)";
        return $this;
    }

    public function column_primary(string $name, string $typeKey = 'autoincrement'): self
    {
        if ($this->id !== null)
            throw new RuntimeException("Primary key already set for table {$this->table}");

        if ($typeKey === (string) true || $typeKey === (string) false) {
            $typeKey = $typeKey == true ? 'autoincrement' : 'int';
        }

        $type = $this->dataTypes[$typeKey] ?? $typeKey;
        if (is_array($type) && array_key_exists(self::$mode, $type)) {
            $type = $type[self::$mode];
        } else {
            $type .= ' ' . $this->sqlTypes[self::$mode]['primaryKey'] ?? 'PRIMARY KEY';
        }
        $type .=  ' ' . ($this->sqlTypes[self::$mode]["default_$typeKey"] ?? '');
        $this->columns[] = trim("$name " . strtoupper($type));
        $this->id = $name;
        return $this;
    }

    public function column_foreign(string $column, Schema|string $referencesTable, ?string $referencesColumn = 'id'): self
    {
        $this->columns[] = "$column INT";
        if (is_object($referencesTable)) {
            [$referencesTable, $referencesColumn] = $referencesTable->getTable();
        }
        $this->foreignKey($column, $referencesTable, $referencesColumn);
        return $this;
    }

    public function column_enum(string $column, array $values, $default = null): self
    {
        $values = implode("', '", $values);
        $enum = str_replace(':values', "'$values'", $this->sqlTypes[self::$mode]['enum']);
        $enum = str_replace(':column', $column, $enum);
        $this->columns[] = "$column $enum" . ($default ? " DEFAULT '$default'" : '');
        return $this;
    }

    public function uuid(string $name, bool $primaryKey = true, bool $generatedByDefault = true)
    {
        $type = $this->dataTypes['uuid'][self::$mode] ?? 'CHAR(36)';
        // $pk = !$primaryKey ? '' : ($this->sqlTypes[self::$mode]['primaryKey'] ?? 'PRIMARY KEY');
        $default = !$generatedByDefault ? '' : ($this->sqlTypes[self::$mode]['default_uuid'] ?? '');
        if ($primaryKey) {
            $this->primaryKey($name);
        }
        $column = preg_replace('/\s+/', ' ', "$name " . strtoupper($type) . " $default");
        $this->columns[] = trim($column);
        return $this;
    }

    public function timestamps(): self
    {
        $type = $this->dataType['timestamp'][self::$mode] ?? 'TIMESTAMP';
        $onupdate = $this->sqlTypes[self::$mode]['onupdate_timestamp'] ?? ' ON UPDATE CURRENT_TIMESTAMP';
        $this->columns[] = "created_at $type DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at $type DEFAULT CURRENT_TIMESTAMP$onupdate";
        return $this;
    }

    public function softDeletes(): self
    {
        $this->column('deleted_at', 'timestamp', true, 'null');
        return $this;
    }

    /** @internal  */
    public function getSQL(): string
    {
        $SPACE = ",\n\t";
        $sql = '';

        if (self::$mode === 'sqlite' && !self::$foreignKeyEnabled) {
            $sql .= "PRAGMA foreign_keys = ON;\n\n";
            self::$foreignKeyEnabled = true;
        } else if (self::$mode === 'pgsql' && !self::$uuidEnabled) {
            $sql .= "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";\n\n";
            self::$uuidEnabled = true;
        }

        $sql .= "CREATE TABLE IF NOT EXISTS {$this->table} (\n\t" . implode($SPACE, $this->columns);

        if (!empty($this->constraints)) {
            $sql .= $SPACE . implode($SPACE, $this->constraints);
        }

        $sql .= "\n);";

        return $sql;
    }

    public function __toString()
    {
        return $this->getSQL();
    }
}
