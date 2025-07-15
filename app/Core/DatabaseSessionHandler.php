<?php

namespace App\Core;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    
    public function close(): bool
    {
        return true;
    }
    
    public function read($sessionId): string
    {
        try {
            $stmt = $this->db->prepare("SELECT payload FROM sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                // Update last activity
                $this->updateLastActivity($sessionId);
                return $result['payload'];
            }
            
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
    
    public function write($sessionId, $sessionData): bool
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $lastActivity = time();
            
            $stmt = $this->db->prepare("
                INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    user_id = VALUES(user_id),
                    ip_address = VALUES(ip_address),
                    user_agent = VALUES(user_agent),
                    payload = VALUES(payload),
                    last_activity = VALUES(last_activity)
            ");
            
            return $stmt->execute([
                $sessionId,
                $userId,
                $ipAddress,
                $userAgent,
                $sessionData,
                $lastActivity
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function destroy($sessionId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
            return $stmt->execute([$sessionId]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function gc($maxlifetime): int
    {
        try {
            $expired = time() - $maxlifetime;
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_activity < ?");
            $stmt->execute([$expired]);
            return $stmt->rowCount();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function updateLastActivity($sessionId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE sessions SET last_activity = ? WHERE id = ?");
            $stmt->execute([time(), $sessionId]);
        } catch (\Exception $e) {
            // Ignore update errors
        }
    }
}
