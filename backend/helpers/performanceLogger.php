<?php

class PerformanceLogger {
    private static $logs = [];
    private static $isEnabled = true;
    
    public static function enable() {
        self::$isEnabled = true;
    }
    public static function disable() {
        self::$isEnabled = false;
    }
    // Start measuring time and memory
    public static function start($operationName) {
        if (!self::$isEnabled) return null;
        
        self::$logs[$operationName] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
            'end_time' => null,
            'end_memory' => null,
            'execution_time' => null,
            'memory_used' => null
        ];
        return $operationName;
    }
    
    // End measuring
    public static function end($operationName) {
        if (!self::$isEnabled || !isset(self::$logs[$operationName])) return null;
        self::$logs[$operationName]['end_time'] = microtime(true);
        self::$logs[$operationName]['end_memory'] = memory_get_usage();
        self::$logs[$operationName]['execution_time'] = 
            self::$logs[$operationName]['end_time'] - self::$logs[$operationName]['start_time'];
        self::$logs[$operationName]['memory_used'] = 
            self::$logs[$operationName]['end_memory'] - self::$logs[$operationName]['start_memory'];
        return self::$logs[$operationName];
    }
    // Get all logs
    public static function getLogs() {
        return self::$logs;
    }
    // Get specific log
    public static function getLog($operationName) {
        return self::$logs[$operationName] ?? null;
    }
    // Display formatted report
    public static function displayReport() {
        echo "<pre>";
        echo "+==================================================================+\n";
        echo "|                    PERFORMANCE TESTING REPORT                    |\n";
        echo "+==================================================================+\n";
        
        foreach (self::$logs as $name => $log) {
            $timeMs = round($log['execution_time'] * 1000, 2);
            $memoryKb = round($log['memory_used'] / 1024, 2);
            $memoryMb = round($memoryKb / 1024, 2);
            $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");
            echo sprintf(
                "║ %s %-30s : %8.2f ms  |  %8.2f KB  (%5.2f MB) ║\n",
                $status,
                $name,
                $timeMs,
                $memoryKb,
                $memoryMb
            );
        }
        echo "+==================================================================+\n";
        echo "</pre>";
    }
    
    // Save logs to file
    public static function saveToFile($filename = "performance_log.json") {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'logs' => self::$logs
        ];
        file_put_contents(__DIR__ . '/../../logs/' . $filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}