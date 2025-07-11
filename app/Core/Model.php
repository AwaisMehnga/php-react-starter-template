<?php

namespace App\Core;

class Model
{
    protected static $table;
    protected $attributes = [];
    protected $fillable = [];

    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get all records (fake data for now)
     *
     * @return array
     */
    public static function all()
    {
        // This is a mock implementation
        // In a real app, you'd query a database
        return [];
    }

    /**
     * Find a record by ID (fake data for now)
     *
     * @param int $id
     * @return static|null
     */
    public static function find($id)
    {
        // This is a mock implementation
        // In a real app, you'd query a database
        return null;
    }

    /**
     * Create a new record
     *
     * @param array $attributes
     * @return static
     */
    public static function create($attributes = [])
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Save the model (mock implementation)
     *
     * @return bool
     */
    public function save()
    {
        // Mock implementation
        // In a real app, you'd save to database
        return true;
    }

    /**
     * Fill the model with attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get an attribute value
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     * Set an attribute value
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Check if an attribute exists
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Convert model to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert model to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
