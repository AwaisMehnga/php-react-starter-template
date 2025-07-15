<?php

namespace App\Core;

class Session
{
    private static $handler = null;
    
    /**
     * Start session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Use database session handler
            if (self::$handler === null) {
                self::$handler = new DatabaseSessionHandler();
                session_set_save_handler(self::$handler, true);
            }
            
            session_start();
        }
    }

    /**
     * Set a session value
     */
    public static function put($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     */
    public static function forget($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     */
    public static function flush()
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate($deleteOld = true)
    {
        self::start();
        session_regenerate_id($deleteOld);
    }

    /**
     * Flash data for next request
     */
    public static function flash($key, $value)
    {
        self::put('flash_' . $key, $value);
    }

    /**
     * Get and remove flash data
     */
    public static function getFlash($key, $default = null)
    {
        $flashKey = 'flash_' . $key;
        $value = self::get($flashKey, $default);
        self::forget($flashKey);
        return $value;
    }

    /**
     * Get flash data without removing it
     */
    public static function peekFlash($key, $default = null)
    {
        return self::get('flash_' . $key, $default);
    }
    
    /**
     * Get active sessions for a user
     */
    public static function getActiveSessions($userId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT id, ip_address, user_agent, last_activity
            FROM sessions 
            WHERE user_id = ? 
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Destroy all sessions for a user except current
     */
    public static function destroyOtherSessions($userId)
    {
        $db = Database::getInstance()->getConnection();
        $currentSessionId = session_id();
        $stmt = $db->prepare("DELETE FROM sessions WHERE user_id = ? AND id != ?");
        return $stmt->execute([$userId, $currentSessionId]);
    }
    
    /**
     * Get total active sessions count
     */
    public static function getActiveSessionsCount()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE last_activity > ?");
        $stmt->execute([time() - 3600]); // Active in last hour
        return $stmt->fetchColumn();
    }
    
    /**
     * Clean up expired sessions
     */
    public static function cleanup($maxlifetime = 3600)
    {
        $db = Database::getInstance()->getConnection();
        $expired = time() - $maxlifetime;
        $stmt = $db->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->execute([$expired]);
        return $stmt->rowCount();
    }
}
