<?php

/**
 * tests/PerformanceTest.php — Performance Testing
 *
 * Mengukur waktu eksekusi operasi-operasi utama pada model menggunakan
 * Performance::measure() dan PerformanceLogger.
 */

runSection('PerformanceTest — Performance::measure() helper', function () {
    // Test bahwa measure() mengembalikan struktur yang benar
    $result = Performance::measure(function () {
        // Simulasi operasi ringan
        $sum = 0;
        for ($i = 0; $i < 1000; $i++) $sum += $i;
    }, 5);

    assertTrue(isset($result['avg_ms']),    'measure() mengembalikan avg_ms');
    assertTrue(isset($result['total_ms']),  'measure() mengembalikan total_ms');
    assertTrue(isset($result['min_ms']),    'measure() mengembalikan min_ms');
    assertTrue(isset($result['max_ms']),    'measure() mengembalikan max_ms');
    assertTrue(isset($result['iterations']),'measure() mengembalikan iterations');
    assertEquals(5, $result['iterations'],  'iterations tersimpan dengan benar');
    assertTrue($result['avg_ms'] >= 0,      'avg_ms adalah nilai non-negatif');
    assertTrue($result['min_ms'] <= $result['max_ms'], 'min_ms <= max_ms');
});

runSection('PerformanceTest — Benchmark getAll() Kategori', function () {
    $kategori = new Kategori();

    $result = Performance::measure(function () use ($kategori) {
        $kategori->getAll();
    }, 10);

    assertTrue($result['avg_ms'] < 500, "getAll(kategori) rata-rata < 500ms (aktual: {$result['avg_ms']}ms)");
    echo "    ℹ️  Kategori::getAll() avg: {$result['avg_ms']}ms (10 iterasi)\n";
});

runSection('PerformanceTest — Benchmark getAll() Obat', function () {
    $obat = new Obat();

    $result = Performance::measure(function () use ($obat) {
        $obat->getAll();
    }, 10);

    assertTrue($result['avg_ms'] < 500, "getAll(obat) rata-rata < 500ms (aktual: {$result['avg_ms']}ms)");
    echo "    ℹ️  Obat::getAll() avg: {$result['avg_ms']}ms (10 iterasi)\n";
});

runSection('PerformanceTest — Benchmark getAll() Supplier', function () {
    $supplier = new Supplier();

    $result = Performance::measure(function () use ($supplier) {
        $supplier->getAll();
    }, 10);

    assertTrue($result['avg_ms'] < 500, "getAll(supplier) rata-rata < 500ms (aktual: {$result['avg_ms']}ms)");
    echo "    ℹ️  Supplier::getAll() avg: {$result['avg_ms']}ms (10 iterasi)\n";
});

runSection('PerformanceTest — Benchmark getAll() RiwayatTransaksi', function () {
    $riwayat = new RiwayatTransaksi();

    $result = Performance::measure(function () use ($riwayat) {
        $riwayat->getAll();
    }, 10);

    assertTrue($result['avg_ms'] < 500, "getAll(riwayat) rata-rata < 500ms (aktual: {$result['avg_ms']}ms)");
    echo "    ℹ️  RiwayatTransaksi::getAll() avg: {$result['avg_ms']}ms (10 iterasi)\n";
});

runSection('PerformanceTest — Benchmark Perbandingan (Validate vs Query)', function () {
    $results = Performance::benchmark([
        'TableDrivenValidator::validate(obat)' => function () {
            TableDrivenValidator::validate('obat', [
                'nama_obat' => 'Paracetamol', 'stok' => 10, 'harga' => 5000,
            ]);
        },
        'DbC::requireNonEmpty()' => function () {
            try { DbC::requireNonEmpty('test', 'field'); } catch (\Throwable $e) {}
        },
    ], 100);

    foreach ($results as $name => $r) {
        echo "    ℹ️  {$name}: avg {$r['avg_ms']}ms\n";
    }
    assertTrue(true, 'Benchmark perbandingan berhasil dijalankan');
});

runSection('PerformanceTest — PerformanceLogger menyimpan ke file', function () {
    $logger = new PerformanceLogger(__DIR__ . '/../logs');

    $result = $logger->run('unit_test_ping', function () {
        // no-op
    }, 3);

    assertTrue(isset($result['avg_ms']),   'PerformanceLogger::run() mengembalikan avg_ms');
    assertTrue($result['iterations'] === 3,'PerformanceLogger::run() mencatat 3 iterasi');

    $logs = $logger->readLogs();
    assertNotEmpty($logs, 'File log berhasil dibuat dan bisa dibaca');
    echo "    ℹ️  " . count($logs) . " entri log tersimpan hari ini\n";
});
?>
