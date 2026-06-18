<?php

/**
 * DbC.php — Design by Contract Helper
 *
 * Menyediakan mekanisme formal untuk:
 * - require()  : Precondition (kontrak masukan)
 * - ensure()   : Postcondition (kontrak keluaran)
 * - invariant(): Class invariant (konsistensi internal)
 *
 * Teknik Konstruksi: Design by Contract (DbC)
 * Digunakan oleh: semua model (Obat, Kategori, Supplier, Transaksi, RiwayatTransaksi, User)
 */
class DbC
{
    /**
     * Precondition — memastikan input memenuhi syarat SEBELUM eksekusi.
     *
     * @param bool   $condition Ekspresi boolean yang harus true
     * @param string $message   Pesan error jika kondisi gagal
     * @throws InvalidArgumentException jika kondisi false
     */
    public static function require(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InvalidArgumentException("Precondition failed: {$message}");
        }
    }

    /**
     * Postcondition — memverifikasi hasil SETELAH eksekusi.
     *
     * @param bool   $condition Ekspresi boolean yang harus true
     * @param string $message   Pesan error jika kondisi gagal
     * @throws RuntimeException jika kondisi false
     */
    public static function ensure(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException("Postcondition failed: {$message}");
        }
    }

    /**
     * Invariant — memverifikasi konsistensi state objek kapan saja.
     *
     * @param bool   $condition Ekspresi boolean yang harus selalu true
     * @param string $message   Pesan error jika invariant dilanggar
     * @throws LogicException jika kondisi false
     */
    public static function invariant(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new LogicException("Invariant violated: {$message}");
        }
    }

    /**
     * Validasi string tidak kosong (helper umum).
     */
    public static function requireNonEmpty(string $value, string $fieldName): void
    {
        self::require(trim($value) !== '', "{$fieldName} tidak boleh kosong");
    }

    /**
     * Validasi nilai positif (helper umum).
     */
    public static function requirePositive(int|float $value, string $fieldName): void
    {
        self::require($value >= 0, "{$fieldName} tidak boleh negatif (diterima: {$value})");
    }

    /**
     * Validasi ID valid (> 0).
     */
    public static function requireValidId(int $id, string $fieldName = 'id'): void
    {
        self::require($id > 0, "{$fieldName} harus berupa integer positif (diterima: {$id})");
    }
}
?>
