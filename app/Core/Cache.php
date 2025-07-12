<?php

class Cache {
    private static $cacheDir;

    public static function init() {
        self::$cacheDir = __DIR__ . '/cache';
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    public static function get($key) {
        self::init();
        $file = self::$cacheDir . '/' . md5($key) . '.php';
        if (file_exists($file)) {
            return include($file);
        }
        return null;
    }

    public static function set($key, $data) {
        self::init();
        $file = self::$cacheDir . '/' . md5($key) . '.php';
        return file_put_contents($file, "<?php return " . var_export($data, true) . ";");
    }

    public static function delete($key) {
        self::init();
        $file = self::$cacheDir . '/' . md5($key) . '.php';
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public static function clear() {
        self::init();
        $files = glob(self::$cacheDir . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}
