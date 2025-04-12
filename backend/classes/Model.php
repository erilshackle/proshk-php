<?php

/**
 * Class Model
 * 
 * Base abstract model for handling database interactions.
 */
abstract class Model
{
    /** @var string The table associated with the model */
    protected static $table;
    /** @var string The primary key for the model */
    protected static $primaryKey = 'id';
    /** @var array The attributes that are mass assignable */
    protected static $fillable = [];
    /** @var array The model's attributes */
    protected array $attributes = [];

    /**
     * Model constructor.
     *
     * @param array $load Initial attribute values.
     */
    public function __construct(array $load = [])
    {
        foreach ($load as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * Get an attribute.
     *
     * @param string $key Attribute name.
     * @return mixed|null Attribute value.
     */
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute.
     *
     * @param string $key Attribute name.
     * @param mixed $value Attribute value.
     */
    public function __set($key, $value)
    {
        if (in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get all records.
     *
     * @return array List of all records.
     */
    public static function all(): array
    {
        $db = DB::getInstance();
        return $db->query("SELECT * FROM " . static::$table);
    }

    /**
     * Find a record by primary key.
     *
     * @param mixed $id Primary key value.
     * @return static|null Model instance or null.
     */
    public static function find(int|string|float|null|bool $id): ?self
    {
        $result = self::query(static::$primaryKey . " = ?", [$id]);
        return $result ? new static($result[0]) : null;
    }

    /**
     * Save the model instance to database.
     *
     * @return bool True on success, false on failure.
     */
    public function save(): bool
    {
        try {
            return isset($this->attributes[static::$primaryKey]) ? $this->update() : $this->create();
        } catch (Exception $e) {
            error_log("Model Save error: " . $e->getMessage());
            return false;
        }
    }

    protected function create(): bool
    {
        $db = DB::getInstance();
        $id = $db->insert(static::$table, $this->attributes);
        if ($id) {
            $this->attributes[static::$primaryKey] = $id;
            return true;
        }
        return false;
    }

    protected function update(): bool
    {
        $db = DB::getInstance();
        $id = $this->attributes[static::$primaryKey];
        $data = array_filter($this->attributes, fn($key) => in_array($key, static::$fillable), ARRAY_FILTER_USE_KEY);
        return $db->update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }

    /**
     * Delete a record by primary key.
     *
     * @param mixed $id Primary key value.
     * @return bool True on success, false on failure.
     */
    public static function delete(int|string $id): bool
    {
        try {
            $db = DB::getInstance();
            return $db->delete(static::$table, static::$primaryKey . " = ?", [$id]);
        } catch (Exception $e) {
            error_log("Model Delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert the model to an array.
     *
     * @return array Model attributes.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Execute a custom query on the table.
     *
     * @param string $where SQL WHERE clause.
     * @param array $params Query parameters.
     * @return array Query result.
     */
    protected static function query(string $where, array $params): ?array
    {
        try {
            $db = DB::getInstance();
            return $db->query("SELECT * FROM " . static::$table . " WHERE " . $where, $params);
        } catch (Exception $e) {
            error_log("Model Query error: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Query the database with a where clause.
     *
     * @param string $column Column name.
     * @param mixed $value Value.
     * @param string $operator SQL operator.
     * @return array|null Result array or null on failure.
     */
    public static function where(string $column, string|int|float|bool|null $value, string $operator = '='): ?array
    {
        if (!in_array(strtoupper($operator), ['=', '>', '<', '>=', '<=', 'LIKE', 'IS', 'NOT', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
            return null;
        }
        return self::query("{$column} {$operator} ?", [$value]);
    }


    /**
     * Get the first record matching the where clause.
     *
     * @param string $column Column name.
     * @param mixed $value Value.
     * @param string $operator SQL operator.
     * @return static|null Model instance or null.
     */
    public static function firstWhere(string $column, string|int|float|bool|null $value, string $operator = '='): ?self
    {
        $result = self::where($column, $value, $operator);
        return $result ? new static($result[0]) : null;
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $relatedModel Related model class.
     * @param string $foreignKey Foreign key in the related model.
     * @param string|null $localKey Local key in the current model.
     * @return array Related model instances.
     */
    public function hasOne(string $relatedModel, string $foreignKey, ?string $localKey = null)
    {
        $localKey = $localKey ?? static::$primaryKey;
        return $relatedModel::firstWhere($foreignKey, $this->attributes[$localKey], '=');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $relatedModel Related model class.
     * @param string $foreignKey Foreign key in the related model.
     * @param string|null $localKey Local key in the current model.
     * @return array Related model instances.
     */
    public function hasMany(string $relatedModel, string $foreignKey, ?string $localKey = null): array
    {
        $localKey = $localKey ?? static::$primaryKey;
        return $relatedModel::where($foreignKey, $this->attributes[$localKey], '=');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $relatedModel Related model class.
     * @param string $foreignKey Foreign key in the related model.
     * @param string|null $ownerKey Owner key in the current model.
     * @return array Related model instances.
     */
    public function belongsTo(string $relatedModel, string $foreignKey, ?string $ownerKey = null)
    {
        $ownerKey = $ownerKey ?? static::$primaryKey;
        return $relatedModel::find($this->attributes[$foreignKey]);
    }
}
