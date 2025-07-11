<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Category extends Model
{
    protected static $table = 'categories';
    
    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active'
    ];

    /**
     * Get all active categories
     *
     * @return array
     */
    public static function all()
    {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table . " WHERE is_active = 1 ORDER BY sort_order, name");
        
        return array_map(function($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Find category by ID
     *
     * @param int $id
     * @return static|null
     */
    public static function find($id)
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        return $result ? new static($result) : null;
    }

    /**
     * Find category by slug
     *
     * @param string $slug
     * @return static|null
     */
    public static function findBySlug($slug)
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE slug = ?", [$slug]);
        return $result ? new static($result) : null;
    }

    /**
     * Get parent category
     *
     * @return static|null
     */
    public function getParent()
    {
        $parentId = $this->getAttribute('parent_id');
        return $parentId ? static::find($parentId) : null;
    }

    /**
     * Get child categories
     *
     * @return array
     */
    public function getChildren()
    {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table . " WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order", [$this->getAttribute('id')]);
        
        return array_map(function($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Get tools in this category
     *
     * @return array
     */
    public function getTools()
    {
        return Tool::byCategory($this->getAttribute('id'));
    }

    /**
     * Count tools in this category
     *
     * @return int
     */
    public function getToolsCount()
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT COUNT(*) as count FROM tools WHERE category_id = ? AND is_active = 1", [$this->getAttribute('id')]);
        return $result ? (int)$result['count'] : 0;
    }

    private function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
}
