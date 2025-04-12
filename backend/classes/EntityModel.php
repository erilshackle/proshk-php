<?php

/**
 * Class EntityModel
 *
 * Provides static methods to perform CRUD operations on a given table.
 */
final class EntityModel
{
    protected static array $instances = [];
    protected string $table;
    protected string $primaryKey;
    protected array $rules = [];
    protected array $errors = [];

    public function __construct(string $table, string $primaryKey = 'id', $rules = [])
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->rules =  $rules;
    }

    public function all(): array
    {
        return $this->findAll();
    }

    public function find(int|string|array|object $identity): ?array
    {
        $id = $this->getIdentityId($identity);
        $results = $this->query("{$this->primaryKey} = ? LIMIT 1", [$id]);
        return $results[0] ?? null;
    }

    public function findOne(string $where, array $params = []): ?array
    {
        $results = $this->query("{$where} LIMIT 1", $params);
        return $results[0] ?? null;
    }

    public function findAll(?string $where = null, array $params = []): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        return DB::getInstance()->query($sql, $params);
    }

    public function create(array $data): int|bool
    {
        if (!$this->validate($data)) return false;
        return (int) DB::getInstance()->insert($this->table, $data);
    }

    public function update(int|string|array|object $identity, array $data): bool
    {
        if (!$this->validate($data)) return false;
        $id = $this->getIdentityId($identity);
        return DB::getInstance()->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function delete(int|string|array|object $identity): bool
    {
        $id = $this->getIdentityId($identity);
        return DB::getInstance()->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function query(string $where, array $params = []): array
    {
        $data = DB::getInstance()->query("SELECT * FROM {$this->table} WHERE $where", $params);
        return $data ?: [];
    }



    public function references($identity, string $foreignTable, string $foreignKey): ?array
    {
        $id = $this->getIdentityId($identity);
        if ($identity === null) {
            return null;
        }
        return DB::getInstance()->query("SELECT * FROM {$foreignTable} WHERE {$foreignKey} = ?", [$id]);
    }

    protected function exists(string $field, $value): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $field = ?";
        $result = DB::getInstance()->query($sql, [$value]);
        return $result ? ($result[0]['total'] > 0) : false;
    }

    public function paginate(int $page = 1, int $perPage = 10, string $where = "", array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = DB::getInstance()->query($sql, $params);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $countSql .= " WHERE {$where}";
        }
        $totalResult = DB::getInstance()->query($countSql, $params);
        $total = $totalResult[0]['total'] ?? 0;

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ];
    }

    public function validate(array $data, ?array &$errors = null): bool
    {
        $ruleSet = $this->rules;

        if (empty($ruleSet)) {
            return true;
        }

        $validators = [
            'required' => fn($attribute, $value) => empty($value) ? "Field $attribute is required." : null,
            'email' => fn($attribute, $value) => !filter_var($value, FILTER_VALIDATE_EMAIL) ? "Field $attribute must be a valid e-mail." : null,
            'min' => fn($attribute, $value, $param) => (strlen($value) < (int) $param) ? "Field $attribute must have at least $param characters." : null,
            'max' => fn($attribute, $value, $param) => (strlen($value) > (int) $param) ? "Field $attribute must not be longer than $param characters." : null,
            'name' => fn($attribute, $value) => !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ'´`~^çÇ\s-]+$/", $value) ? "Field $attribute must  be a valid name." : null,
            'username' => fn($attribute, $value) => !preg_match("/^[0-9A-Za-z-\._]+$/", $value) ? "Field $attribute must  be a valid username." : null,
            'preg' => fn($attribute, $value, $param) => !preg_match($param, $value) ? "Field $attribute must match the rule." : null,
            'unique' => fn($attribute, $value) => $this->exists($attribute, $value) ? "Field $attribute already taken." : null,
        ];

        $this->errors = [];
        $firstSet = false;
        foreach ($ruleSet as $attribute => $rules) {
            $value = $data[$attribute] ?? null;

            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }
            
            foreach ($rules as $rule) {
                [$ruleName, $param] = explode(':', $rule) + [null, null];
                if (isset($validators[$ruleName])) {
                    $error = $validators[$ruleName]($attribute, $value, $param);
                    if ($error && !isset($this->errors[$attribute])) {
                        if(!$firstSet){
                            $this->errors[0] = $error;
                            $firstSet = true;
                        }
                        $this->errors[$attribute] = $error;

                    }
                }
            }
        }
        $errors = $this->errors;
        return empty($this->errors);
    }

    // helpers

    public function errors(): array
    {
        return $this->errors;
    }

    public function first_error(): ?string
    {
        return $this->errors[0] ?? null;
    }

    private function getIdentityId($identity)
    {
        if (is_array($identity)) {
            $identity = $identity[$this->primaryKey] ?? null;
        } elseif (is_object($identity)) {
            $identity = $identity->{$this->primaryKey} ?? null;
        }

        if ($identity === null) {
            throw new InvalidArgumentException("Invalid identity provided, missing {$this->primaryKey}.");
        }

        return $identity;
    }


    // register

    protected static function instantiate(string $entity): EntityModel
    {
        if (isset(self::$instances[$entity])) {
            return self::$instances[$entity];
        }

        $entities = Config::load('entities');
        if (!isset($entities[$entity])) {
            throw new Exception("Entity '{$entity}' not registered in config.");
        }

        $config = $entities[$entity];

        $instance = new self(
            $config['table'],
            $config['primary_key'] ?? 'id',
            $config['rules'] ?? []
        );

        self::$instances[$entity] = $instance;

        return $instance;
    }


    public static function new(string $entity): ?EntityModel
    {
        if (isset(self::$instances[$entity])) {
            return self::$instances[$entity];
        }

        $entities = Config::load('entities');
        if (!$entities || !isset($entities[$entity])) {
            throw new Exception("Entity {$entity} not registered.");
        }

        $data = $entities[$entity];
        $model = new self($data['table'], $data['primary_key'], $data['rules']);
        self::$instances[$entity] = $model;
        return $model;
    }
}
