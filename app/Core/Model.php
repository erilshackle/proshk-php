<?php

namespace App\Core;

use DB;
use Exception;

/**
 * Class BaseModel
 * 
 * Base abstract model for handling database interactions.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected array $attributes = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        if (in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
        }
    }

    public static function all(): array
    {
        return self::hydrate(DB::getInstance()->query("SELECT * FROM " . static::$table));
    }

    public static function find(int|string $id): ?self
    {
        return self::where(static::$primaryKey, $id)->first();
    }

    public static function where(string $column, string $value, string $operator = '='): QueryBuilder
    {
        $builder = new QueryBuilder(static::$table, static::class);
        return $builder->where($column, $value, $operator);
    }

    public function save(): bool
    {
        return isset($this->attributes[static::$primaryKey]) ? $this->update() : $this->create();
    }

    private function create(): bool
    {
        $db = DB::getInstance();
        $id = $db->insert(static::$table, $this->attributes);
        if ($id) {
            $this->attributes[static::$primaryKey] = $id;
            return true;
        }
        return false;
    }

    private function update(): bool
    {
        $db = DB::getInstance();
        $id = $this->attributes[static::$primaryKey];
        return $db->update(static::$table, $this->attributes, static::$primaryKey . " = ?", [$id]);
    }

    public static function delete(int|string $id): bool
    {
        return DB::getInstance()->delete(static::$table, static::$primaryKey . " = ?", [$id]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function hasOne(string $relatedModel, string $foreignKey, ?string $localKey = null)
    {
        $localKey = $localKey ?? static::$primaryKey;
        return $relatedModel::where($foreignKey, $this->attributes[$localKey])->first();
    }

    public function hasMany(string $relatedModel, string $foreignKey, ?string $localKey = null): array
    {
        $localKey = $localKey ?? static::$primaryKey;
        return $relatedModel::where($foreignKey, $this->attributes[$localKey])->get();
    }

    public function belongsTo(string $relatedModel, string $foreignKey, ?string $ownerKey = null)
    {
        $ownerKey = $ownerKey ?? static::$primaryKey;
        return $relatedModel::where($ownerKey, $this->attributes[$foreignKey])->first();
    }

    protected static function query(string $where, array $params = []): array
    {
        $records = DB::getInstance()->query("SELECT * FROM " . static::$table . " WHERE " . $where, $params);
        return self::hydrate($records);
    }

    private static function hydrate(array $data): array
    {
        return array_map(fn($item) => new static($item), $data);
    }
}

/**
 * QueryBuilder Helper for building SQL conditions dynamically
 */
final class QueryBuilder
{
    private string $table;
    private string $model;
    private array $where = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private array $params = [];

    public function __construct(string $table, string $model)
    {
        $this->table = $table;
        $this->model = $model;
    }

    public function where(string $column, string $value, mixed $operator = '='): self
    {
        if (!in_array(strtoupper($operator), ['=', '>', '<', '>=', '<=', 'LIKE', 'IS', 'NOT', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
            throw new Exception("Invalid Operator in Query Model " . $this->model);
        }
        $this->where[] = "$column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(?int $count): self
    {
        $this->limit = $count;
        return $this;
    }

    // execeution

    public function get(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(", ", $this->orderBy);
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }
        $results = DB::getInstance()->query($sql, $this->params);
        return array_map(fn($item) => new $this->model($item), $results);
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }
        $result = DB::getInstance()->query($sql, $this->params);
        return $result[0]['count'] ?? 0;
    }

    public function first(): ?Model
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function all()
    {
        $this->limit(null);
        return $this->get();
    }
}
