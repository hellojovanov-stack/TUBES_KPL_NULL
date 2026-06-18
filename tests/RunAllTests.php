<?php

/**
 * tests/RunAllTests.php — Test Runner Utama
 *
 * Menjalankan semua unit test tanpa memerlukan PHPUnit.
 * Setiap test file mendefinisikan fungsi-fungsi yang dipanggil di sini.
 *
 * Jalankan dengan: php tests/RunAllTests.php
 */

define('TESTS_ROOT', __DIR__);
define('PROJECT_ROOT', __DIR__ . '/..');

// ─── Bootstrap ────────────────────────────────────────────────────────────────

require_once PROJECT_ROOT . '/backend/helpers/DbC.php';
require_once PROJECT_ROOT . '/backend/helpers/TableDrivenValidator.php';
require_once PROJECT_ROOT . '/backend/helpers/Performance.php';
require_once PROJECT_ROOT . '/backend/helpers/PerformanceLogger.php';
require_once PROJECT_ROOT . '/backend/models/Database.php';
require_once PROJECT_ROOT . '/backend/models/Obat.php';
require_once PROJECT_ROOT . '/backend/models/Kategori.php';
require_once PROJECT_ROOT . '/backend/models/Supplier.php';
require_once PROJECT_ROOT . '/backend/models/Transaksi.php';
require_once PROJECT_ROOT . '/backend/models/RiwayatTransaksi.php';
require_once PROJECT_ROOT . '/backend/models/User.php';

// ─── Simple Test Framework ─────────────────────────────────────────────────────

$passed = 0;
$failed = 0;
$errors = [];

function assertTrue(bool $condition, string $message): void
{
    global $passed, $failed, $errors;
    if ($condition) {
        echo "\033[32m  ✓ {$message}\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m  ✗ {$message}\033[0m\n";
        $failed++;
        $errors[] = $message;
    }
}

function assertFalse(bool $condition, string $message): void
{
    assertTrue(!$condition, $message);
}

function assertEquals(mixed $expected, mixed $actual, string $message): void
{
    assertTrue($expected === $actual, "{$message} (expected: " . json_encode($expected) . ", got: " . json_encode($actual) . ")");
}

function assertNotEmpty(mixed $value, string $message): void
{
    assertTrue(!empty($value), $message);
}

function assertGreaterThan(int|float $min, int|float $actual, string $message): void
{
    assertTrue($actual > $min, "{$message} (expected > {$min}, got: {$actual})");
}

function runSection(string $title, callable $fn): void
{
    echo "\n\033[33m▶ {$title}\033[0m\n";
    echo str_repeat('─', 50) . "\n";
    try {
        $fn();
    } catch (Throwable $e) {
        global $failed, $errors;
        $failed++;
        $msg = "EXCEPTION in {$title}: " . $e->getMessage();
        echo "\033[31m  ✗ {$msg}\033[0m\n";
        $errors[] = $msg;
    }
}

// ─── Load & Run Tests ──────────────────────────────────────────────────────────

$testFiles = [
    'ObatTest.php',
    'KategoriTest.php',
    'SupplierTest.php',
    'TransaksiTest.php',
    'RiwayatTransaksiTest.php',
    'LoginTest.php',
    'PerformanceTest.php',
    'TableDrivenValidatorTest.php',
];

echo "\n\033[34m╔══════════════════════════════════════════════════╗\033[0m\n";
echo "\033[34m║        CLO2 — Unit Test Runner Apotek            ║\033[0m\n";
echo "\033[34m╚══════════════════════════════════════════════════╝\033[0m\n";

foreach ($testFiles as $file) {
    $path = TESTS_ROOT . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        echo "\n\033[35m⚠ Test file tidak ditemukan: {$file}\033[0m\n";
    }
}

// ─── Summary ──────────────────────────────────────────────────────────────────

echo "\n" . str_repeat('═', 52) . "\n";
$total  = $passed + $failed;
$status = $failed === 0 ? "\033[32mALL PASSED\033[0m" : "\033[31mFAILED\033[0m";
echo "RESULT: {$status} — {$passed}/{$total} tests passed\n";

if (!empty($errors)) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) {
        echo "  • {$e}\n";
    }
}
echo str_repeat('═', 52) . "\n";
exit($failed > 0 ? 1 : 0);
?>
