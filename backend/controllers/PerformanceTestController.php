<?php

session_start();

require_once __DIR__ . '/../models/Obat.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/PerformanceLogger.php';

class PerformanceTestController {
    
    private $obatModel;
    private $userModel;
    
    public function __construct() {
        $this->obatModel = new Obat();
        $this->userModel = new User();
    }
    
    // Test database connection performance
    public function testDatabaseConnection() {
        PerformanceLogger::start('db_connection');
        
        $db = new Database();
        $conn = $db->connect();
        
        PerformanceLogger::end('db_connection');
        
        return PerformanceLogger::getLog('db_connection');
    }
    
    // Test getAll performance with different limits
    public function testGetAllPerformance() {
        $results = [];
        
        // Test multiple times to get average
        for ($i = 1; $i <= 5; $i++) {
            $start = PerformanceLogger::start("getAll_run_{$i}");
            $data = $this->obatModel->getAll();
            PerformanceLogger::end("getAll_run_{$i}");
            $results[$i] = count($data);
        }
        
        return PerformanceLogger::getLogs();
    }
    
    // Test login performance
    public function testLoginPerformance() {
        $testCredentials = [
            ['admin', 'admin123'],
            ['wrong', 'password'],
            ['admin', 'wrongpass']
        ];
        
        foreach ($testCredentials as $cred) {
            $start = PerformanceLogger::start("login_{$cred[0]}");
            $result = $this->userModel->login($cred[0], $cred[1]);
            PerformanceLogger::end("login_{$cred[0]}");
        }
        
        return PerformanceLogger::getLogs();
    }
    
    // Comprehensive performance report
    public function runAllTests() {
        PerformanceLogger::enable();
        
        // Clear previous logs
        $testResults = [];
        
        // 1. Test database connection
        $testResults['db_connection'] = $this->testDatabaseConnection();
        
        // 2. Test getAll
        $testResults['getAll'] = $this->testGetAllPerformance();
        
        // 3. Test login
        $testResults['login'] = $this->testLoginPerformance();
        
        // 4. Test search
        $testResults['search'] = $this->obatModel->searchPerformanceTest();
        
        // Generate report
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $testResults,
            'summary' => $this->generateSummary(),
            'recommendations' => $this->generateRecommendations()
        ];
    }
    
    private function generateSummary() {
        $logs = PerformanceLogger::getLogs();
        $summary = [
            'total_tests' => count($logs),
            'avg_execution_time' => 0,
            'avg_memory_usage' => 0,
            'fastest_test' => ['name' => '', 'time' => PHP_FLOAT_MAX],
            'slowest_test' => ['name' => '', 'time' => 0]
        ];
        
        $totalTime = 0;
        foreach ($logs as $name => $log) {
            $totalTime += $log['execution_time'];
            if ($log['execution_time'] < $summary['fastest_test']['time']) {
                $summary['fastest_test'] = ['name' => $name, 'time' => $log['execution_time']];
            }
            if ($log['execution_time'] > $summary['slowest_test']['time']) {
                $summary['slowest_test'] = ['name' => $name, 'time' => $log['execution_time']];
            }
        }
        
        $summary['avg_execution_time'] = $totalTime / $summary['total_tests'];
        
        return $summary;
    }
    
    private function generateRecommendations() {
        $logs = PerformanceLogger::getLogs();
        $recommendations = [];
        
        foreach ($logs as $name => $log) {
            $timeMs = $log['execution_time'] * 1000;
            
            if ($timeMs > 500) {
                $recommendations[] = "⚠️ {$name} terlalu lambat ({$timeMs}ms). Pertimbangkan optimasi query.";
            } elseif ($timeMs > 100) {
                $recommendations[] = "📝 {$name} cukup lambat ({$timeMs}ms). Bisa dioptimasi.";
            } else {
                $recommendations[] = "✅ {$name} performa baik ({$timeMs}ms).";
            }
            
            if ($log['memory_used'] > 5 * 1024 * 1024) { // > 5MB
                $recommendations[] = "💾 {$name} menggunakan memory besar (" . round($log['memory_used'] / 1024 / 1024, 2) . "MB)";
            }
        }
        
        return $recommendations;
    }
}