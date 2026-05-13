<?php

require_once __DIR__ . '/../helpers/Performance.php';
require_once __DIR__ . '/../helpers/PerformanceLogger.php';
require_once __DIR__ . '/../models/Obat.php';
require_once __DIR__ . '/../config/Database.php';

class PerformanceTest {
    
    private $obatModel;
    private $testResults = [];
    private $db;
    
    public function __construct() {
        $this->obatModel = new Obat();
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Test response time under 100ms
    public function testResponseTime() {
        echo "\n📈 TEST 1: RESPONSE TIME (< 100ms)\n";
        echo str_repeat("-", 50) . "\n";
        
        $start = Performance::start();
        $data = $this->obatModel->getAll();
        $executionTime = Performance::end($start);
        
        $status = $executionTime < 0.1 ? "✅ PASS" : "❌ FAIL";
        $timeMs = $executionTime * 1000;
        
        echo "GetAll execution time: " . round($timeMs, 2) . " ms\n";
        echo "Status: {$status}\n";
        echo "Threshold: 100ms\n\n";
        
        $this->testResults['response_time'] = [
            'passed' => $executionTime < 0.1,
            'time_ms' => round($timeMs, 2),
            'threshold_ms' => 100
        ];
    }
    
    // Test memory usage under 2MB
    public function testMemoryUsage() {
        echo "\n💾 TEST 2: MEMORY USAGE (< 2MB)\n";
        echo str_repeat("-", 50) . "\n";
        
        $startMemory = memory_get_usage();
        $data = $this->obatModel->getAll();
        $endMemory = memory_get_usage();
        
        $memoryUsed = $endMemory - $startMemory;
        $memoryKb = round($memoryUsed / 1024, 2);
        
        $status = $memoryUsed < 2 * 1024 * 1024 ? "✅ PASS" : "❌ FAIL";
        
        echo "Memory used: {$memoryKb} KB\n";
        echo "Status: {$status}\n";
        echo "Threshold: 2048 KB\n\n";
        
        $this->testResults['memory_usage'] = [
            'passed' => $memoryUsed < 2 * 1024 * 1024,
            'memory_kb' => $memoryKb,
            'threshold_kb' => 2048
        ];
    }
    
    // Test concurrent requests simulation
    public function testConcurrentRequests($requests = 10) {
        echo "\n🔄 TEST 3: CONCURRENT REQUESTS ({$requests} requests)\n";
        echo str_repeat("-", 50) . "\n";
        
        $start = Performance::start();
        
        for ($i = 0; $i < $requests; $i++) {
            $this->obatModel->getAll();
        }
        
        $totalTime = Performance::end($start);
        $avgTime = $totalTime / $requests;
        $avgTimeMs = $avgTime * 1000;
        
        $status = $avgTime < 0.05 ? "✅ EXCELLENT" : ($avgTime < 0.1 ? "⚠️ GOOD" : "❌ SLOW");
        
        echo "Total time: " . round($totalTime * 1000, 2) . " ms\n";
        echo "Average per request: " . round($avgTimeMs, 2) . " ms\n";
        echo "Status: {$status}\n\n";
        
        $this->testResults['concurrent_requests'] = [
            'total_requests' => $requests,
            'total_time_ms' => round($totalTime * 1000, 2),
            'avg_time_ms' => round($avgTimeMs, 2),
            'status' => $status
        ];
    }
    
    // Test database query performance
    public function testQueryPerformance() {
        echo "\n🗄️ TEST 4: DATABASE QUERY PERFORMANCE\n";
        echo str_repeat("-", 50) . "\n";
        
        $queries = [
            'SELECT * FROM obat' => "SELECT * FROM obat LIMIT 10",
            'SELECT with WHERE' => "SELECT * FROM obat WHERE id = 1",
            'SELECT with LIKE' => "SELECT * FROM obat WHERE nama_obat LIKE '%a%' LIMIT 10"
        ];
        
        foreach ($queries as $name => $query) {
            $start = Performance::start();
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $time = Performance::end($start);
            $timeMs = $time * 1000;
            
            $status = $timeMs < 50 ? "✅" : ($timeMs < 100 ? "⚠️" : "❌");
            
            echo "{$status} {$name}: " . round($timeMs, 2) . " ms (rows: " . count($results) . ")\n";
            
            $this->testResults['queries'][$name] = [
                'time_ms' => round($timeMs, 2),
                'rows_returned' => count($results),
                'status' => $status
            ];
        }
        
        echo "\n";
    }
    
    // Test load under different data sizes - FIXED (tanpa getById)
    public function testLoadScaling() {
        echo "\n📊 TEST 5: LOAD SCALING\n";
        echo str_repeat("-", 50) . "\n";
        
        $testSizes = [10, 50, 100];
        
        foreach ($testSizes as $size) {
            $start = Performance::start();
            
            // Simulate loading $size records dengan SELECT langsung
            for ($i = 0; $i < $size; $i++) {
                $query = "SELECT * FROM obat WHERE id = 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $stmt->fetch();
            }
            
            $time = Performance::end($start);
            $timeMs = $time * 1000;
            
            echo "Size {$size}: " . round($timeMs, 2) . " ms (avg " . round($timeMs / $size, 2) . " ms/record)\n";
            
            $this->testResults['load_scaling'][$size] = [
                'total_time_ms' => round($timeMs, 2),
                'avg_per_record_ms' => round($timeMs / $size, 2)
            ];
        }
        
        echo "\n";
    }
    
    // Test API endpoint performance
    public function testApiPerformance() {
        echo "\n🌐 TEST 6: API ENDPOINT PERFORMANCE\n";
        echo str_repeat("-", 50) . "\n";
        
        $endpoints = [
            'GET obat' => '/TUBES_KPL_NULL/backend/routes/obat.php?action=search&keyword=a',
            'GET transaksi' => '/TUBES_KPL_NULL/backend/routes/transaksi.php'
        ];
        
        foreach ($endpoints as $name => $url) {
            $start = Performance::start();
            
            // Simulate API call
            $ch = curl_init("http://localhost{$url}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $time = Performance::end($start);
            $timeMs = $time * 1000;
            
            $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");
            
            echo "{$status} {$name}: " . round($timeMs, 2) . " ms (HTTP {$httpCode})\n";
            
            $this->testResults['api_performance'][$name] = [
                'time_ms' => round($timeMs, 2),
                'http_code' => $httpCode,
                'status' => $status
            ];
        }
        
        echo "\n";
    }
    
    // Generate final report
    public function generateReport() {
        echo "\n";
        echo "+==================================================================+\n";
        echo "║                    FINAL PERFORMANCE TEST REPORT                 ║\n";
        echo "+==================================================================+\n";
        
        $totalPassed = 0;
        $totalTests = 0;
        
        foreach ($this->testResults as $category => $result) {
            if (isset($result['passed'])) {
                $totalTests++;
                if ($result['passed']) $totalPassed++;
            }
        }
        
        // Check query performance for pass/fail
        if (isset($this->testResults['queries'])) {
            foreach ($this->testResults['queries'] as $query) {
                $totalTests++;
                if ($query['time_ms'] < 100) $totalPassed++;
            }
        }
        
        $passRate = ($totalPassed / max($totalTests, 1)) * 100;
        
        echo sprintf("║ %-66s ║\n", "Overall Pass Rate: " . round($passRate, 1) . "% ({$totalPassed}/{$totalTests})");
        echo "+==================================================================+\n";
        // Response time result
        if (isset($this->testResults['response_time'])) {
            $icon = $this->testResults['response_time']['passed'] ? "✅" : "❌";
            $timeMs = $this->testResults['response_time']['time_ms'];
            echo sprintf("║ %s Response Time    : %5.2f ms (threshold: 100ms)        ║\n", $icon, $timeMs);
        }
        
        // Memory usage result
        if (isset($this->testResults['memory_usage'])) {
            $icon = $this->testResults['memory_usage']['passed'] ? "✅" : "❌";
            $memoryKb = $this->testResults['memory_usage']['memory_kb'];
            echo sprintf("║ %s Memory Usage     : %5.2f KB (threshold: 2048 KB)      ║\n", $icon, $memoryKb);
        }
        
        // Concurrent requests
        if (isset($this->testResults['concurrent_requests'])) {
            $avgMs = $this->testResults['concurrent_requests']['avg_time_ms'];
            $statusIcon = $this->testResults['concurrent_requests']['status'] === "✅ EXCELLENT" ? "✅" : "⚠️";
            echo sprintf("║ %s Concurrent (10)   : %5.2f ms avg per request           ║\n", $statusIcon, $avgMs);
        }
        echo "+==================================================================+\n";
        
        // Query results summary
        if (isset($this->testResults['queries'])) {
            echo "║ 📊 Query Performance:                                              ║\n";
            foreach ($this->testResults['queries'] as $name => $query) {
                $icon = $query['status'] === "✅" ? "✅" : ($query['status'] === "⚠️" ? "⚠️" : "❌");
                $shortName = strlen($name) > 35 ? substr($name, 0, 32) . "..." : $name;
                echo sprintf("║    %s %-35s : %5.2f ms                         ║\n", $icon, $shortName, $query['time_ms']);
            }
        }
        
        echo "+==================================================================+\n";
        
        // Save to file
        $this->saveReport();
    }
    
    private function saveReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $this->testResults,
            'server_info' => [
                'php_version' => phpversion(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'database' => 'MySQL'
            ]
        ];
        
        $dir = __DIR__ . '/../logs';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $filename = $dir . '/performance_report_' . date('Ymd_His') . '.json';
        file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT));
        echo "\n📁 Report saved to: logs/" . basename($filename) . "\n";
    }
    
    // Run all tests
    public function runAll() {
        echo "\n";
        echo "====================================================================\n";
        echo "║              PERFORMANCE TESTING SUITE v2.0                      ║\n";
        echo "====================================================================\n";
        
        $this->testResponseTime();
        $this->testMemoryUsage();
        $this->testConcurrentRequests(10);
        $this->testQueryPerformance();
        $this->testLoadScaling();
        $this->testApiPerformance();
        $this->generateReport();
        
        return $this->testResults;
    }
}

// Run the tests if executed directly
if (php_sapi_name() === 'cli' || basename($_SERVER['PHP_SELF']) === 'PerformanceTest.php') {
    $test = new PerformanceTest();
    $test->runAll();
}