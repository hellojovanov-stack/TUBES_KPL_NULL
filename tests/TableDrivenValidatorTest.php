<?php

/**
 * tests/TableDrivenValidatorTest.php — Unit Test untuk TableDrivenValidator
 *
 * Memverifikasi bahwa tabel aturan validasi bekerja dengan benar
 * untuk semua entitas yang didefinisikan.
 */

runSection('TableDrivenValidatorTest — Struktur validate()', function () {
    $result = TableDrivenValidator::validate('obat', [
        'nama_obat' => 'Test', 'stok' => 0, 'harga' => 0,
    ]);

    assertTrue(isset($result['valid']),  'validate() mengembalikan key "valid"');
    assertTrue(isset($result['errors']), 'validate() mengembalikan key "errors"');
    assertTrue(is_bool($result['valid']), '"valid" adalah boolean');
    assertTrue(is_array($result['errors']), '"errors" adalah array');
});

runSection('TableDrivenValidatorTest — Entitas tidak dikenal', function () {
    $result = TableDrivenValidator::validate('entitas_tidak_ada', ['field' => 'value']);
    assertTrue($result['valid'], 'Entitas tidak dikenal tetap valid (tidak ada rule = lolos)');
    assertEquals(0, count($result['errors']), 'Tidak ada error untuk entitas tidak dikenal');
});

runSection('TableDrivenValidatorTest — Semua rule types', function () {
    // required
    $r = TableDrivenValidator::validate('kategori', ['nama_kategori' => '']);
    assertFalse($r['valid'], 'Rule "required" bekerja untuk string kosong');

    // maxLength
    $r = TableDrivenValidator::validate('kategori', ['nama_kategori' => str_repeat('X', 101)]);
    assertFalse($r['valid'], 'Rule "maxLength" bekerja untuk string terlalu panjang');

    // min
    $r = TableDrivenValidator::validate('obat', ['nama_obat' => 'X', 'stok' => -5, 'harga' => 0]);
    assertFalse($r['valid'], 'Rule "min" bekerja untuk nilai di bawah minimum');

    // minLength
    $r = TableDrivenValidator::validate('user', ['username' => 'a', 'password' => '1']);
    assertFalse($r['valid'], 'Rule "minLength" bekerja untuk password terlalu pendek');

    // numeric
    $r = TableDrivenValidator::validate('transaksi', [
        'id_obat'   => 1,
        'jumlah'    => 1,
        'sub_total' => 'abc_bukan_angka',
    ]);
    assertFalse($r['valid'], 'Rule "numeric" bekerja untuk nilai non-numerik');
});

runSection('TableDrivenValidatorTest — validateOrFail() melempar exception', function () {
    $threw = false;
    try {
        TableDrivenValidator::validateOrFail('kategori', ['nama_kategori' => '']);
    } catch (InvalidArgumentException $e) {
        $threw = true;
        assertTrue(strlen($e->getMessage()) > 0, 'Exception memiliki pesan yang tidak kosong');
    }
    assertTrue($threw, 'validateOrFail() melempar InvalidArgumentException saat gagal');

    // Tidak melempar untuk data valid
    $threw = false;
    try {
        TableDrivenValidator::validateOrFail('kategori', ['nama_kategori' => 'Valid Name']);
    } catch (InvalidArgumentException $e) {
        $threw = true;
    }
    assertFalse($threw, 'validateOrFail() tidak melempar exception untuk data valid');
});

runSection('TableDrivenValidatorTest — Multiple errors dalam satu call', function () {
    $r = TableDrivenValidator::validate('obat', [
        'nama_obat' => '',   // required gagal
        'stok'      => -1,   // min gagal
        'harga'     => -500, // min gagal
    ]);
    assertFalse($r['valid'], 'Validasi gagal ketika banyak rule dilanggar');
    assertGreaterThan(1, count($r['errors']), 'Semua error dilaporkan sekaligus (> 1 error)');
    echo "    ℹ️  " . count($r['errors']) . " error terdeteksi sekaligus\n";
});
?>
