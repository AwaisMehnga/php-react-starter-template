<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Tool extends Model
{
    protected static $table = 'tools';
    
    protected $fillable = [
        'name', 'slug', 'description', 'category_id', 'icon', 'url', 
        'is_external', 'tags', 'is_featured', 'is_active'
    ];

    /**
     * Get all active tools
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
     * Get featured tools
     *
     * @return array
     */
    public static function featured()
    {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table . " WHERE is_featured = 1 AND is_active = 1 ORDER BY usage_count DESC");
        
        return array_map(function($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Find tool by ID
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
     * Find tool by slug
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
     * Get tools by category
     *
     * @param int $categoryId
     * @return array
     */
    public static function byCategory($categoryId)
    {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table . " WHERE category_id = ? AND is_active = 1", [$categoryId]);
        
        return array_map(function($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Increment usage count
     *
     * @return void
     */
    public function incrementUsage()
    {
        $db = Database::getInstance();
        $db->query("UPDATE " . static::$table . " SET usage_count = usage_count + 1 WHERE id = ?", [$this->getAttribute('id')]);
    }

    /**
     * Get category relationship
     *
     * @return Category|null
     */
    public function getCategory()
    {
        $categoryId = $this->getAttribute('category_id');
        return $categoryId ? Category::find($categoryId) : null;
    }

    /**
     * Get tags as array
     *
     * @return array
     */
    public function getTags()
    {
        $tags = $this->getAttribute('tags');
        return $tags ? json_decode($tags, true) : [];
    }

    private function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
}
