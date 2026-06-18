<?php

require_once __DIR__ . '/../controllers/PerformanceTestController.php';
require_once __DIR__ . '/../helpers/PerformanceLogger.php';

$controller = new PerformanceTestController();
$action = $_GET['action'] ?? 'report';

switch ($action) {
    case 'run':
        // Run all tests and return JSON
        header('Content-Type: application/json');
        $results = $controller->runAllTests();
        echo json_encode($results, JSON_PRETTY_PRINT);
        break;
        
    case 'report':
        // Display HTML report
        $controller->runAllTests();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Performance Testing Report</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    background: #1e1e2e;
                    color: #cdd6f4;
                    padding: 20px;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                .header {
                    background: #313244;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
                .test-card {
                    background: #181825;
                    border-radius: 10px;
                    margin-bottom: 15px;
                    overflow: hidden;
                }
                .test-header {
                    background: #45475a;
                    padding: 12px 20px;
                    font-weight: bold;
                    cursor: pointer;
                }
                .test-body {
                    padding: 20px;
                    display: none;
                }
                .test-body.show {
                    display: block;
                }
                .metric {
                    display: inline-block;
                    background: #313244;
                    padding: 5px 10px;
                    border-radius: 5px;
                    margin: 5px;
                    font-size: 12px;
                }
                .good { color: #a6e3a1; }
                .warning { color: #f9e2af; }
                .bad { color: #f38ba8; }
                pre {
                    background: #11111b;
                    padding: 15px;
                    border-radius: 8px;
                    overflow-x: auto;
                }
                button {
                    background: #89b4fa;
                    color: #1e1e2e;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: bold;
                    margin-right: 10px;
                }
                button:hover {
                    background: #b4befe;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚡ Performance Testing Report</h1>
                    <p>Generated: <?= date('Y-m-d H:i:s') ?></p>
                    <button onclick="runTests()">🔄 Run Tests Again</button>
                    <button onclick="exportJSON()">📥 Export JSON</button>
                </div>
                
                <div id="reportContent">
                    <?php PerformanceLogger::displayReport(); ?>
                </div>
                
                <div id="detailedReport"></div>
            </div>
            
            <script>
                async function runTests() {
                    document.getElementById('reportContent').innerHTML = '<div style="text-align:center">Running tests... ⏳</div>';
                    
                    const response = await fetch('?action=run');
                    const data = await response.json();
                    
                    displayDetailedReport(data);
                }
                
                function displayDetailedReport(data) {
                    let html = '<h2>📊 Detailed Analysis</h2>';
                    
                    for (const [testName, result] of Object.entries(data.results)) {
                        html += `
                            <div class="test-card">
                                <div class="test-header" onclick="toggleTest(this)">
                                    📈 ${testName}
                                </div>
                                <div class="test-body">
                                    <pre>${JSON.stringify(result, null, 2)}</pre>
                                </div>
                            </div>
                        `;
                    }
                    
                    html += `
                        <div class="test-card">
                            <div class="test-header" onclick="toggleTest(this)">
                                📋 Summary
                            </div>
                            <div class="test-body">
                                <pre>${JSON.stringify(data.summary, null, 2)}</pre>
                            </div>
                        </div>
                        <div class="test-card">
                            <div class="test-header" onclick="toggleTest(this)">
                                💡 Recommendations
                            </div>
                            <div class="test-body">
                                <ul>
                                    ${data.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('detailedReport').innerHTML = html;
                }
                
                function toggleTest(element) {
                    const body = element.nextElementSibling;
                    body.classList.toggle('show');
                }
                
                function exportJSON() {
                    fetch('?action=run')
                        .then(res => res.json())
                        .then(data => {
                            const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `performance_test_${Date.now()}.json`;
                            a.click();
                            URL.revokeObjectURL(url);
                        });
                }
            </script>
        </body>
        </html>
        <?php
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
}