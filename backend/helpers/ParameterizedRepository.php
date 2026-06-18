<?php

/**
 * ParameterizedRepository.php — Parameterization / Generics
 *
 * Generic repository base class yang dapat dipakai oleh semua model
 * tanpa perlu menulis ulang logika CRUD yang sama.
 * Menggunakan parameterisasi (table name, primary key, allowed fields)
 * sebagai pengganti pendekatan generics di PHP.
 *
 * Teknik Konstruksi: Parameterization / Generics (Jovanov & Fatan)
 */
class ParameterizedRepository
{
    protected \PDO $conn;
    protected string $table;
    protected string $primaryKey;
    protected array  $fillable; // kolom yang boleh diisi

    /**
     * @param \PDO   $conn       Koneksi database PDO
     * @param string $table      Nama tabel
     * @param string $primaryKey Nama kolom primary key (default: 'id')
     * @param array  $fillable   Daftar kolom yang boleh diisi saat insert/update
     */
    public function __construct(\PDO $conn, string $table, string $primaryKey = 'id', array $fillable = [])
    {
        DbC::requireNonEmpty($table, 'table');
        DbC::requireNonEmpty($primaryKey, 'primaryKey');

        $this->conn       = $conn;
        $this->table      = $table;
        $this->primaryKey = $primaryKey;
        $this->fillable   = $fillable;
    }

    /**
     * Ambil semua record dari tabel.
     *
     * @param string $orderBy Kolom untuk ORDER BY (default primary key DESC)
     * @return array
     */
    public function findAll(string $orderBy = ''): array
    {
        $order = $orderBy ?: "{$this->primaryKey} DESC";
        $stmt  = $this->conn->prepare("SELECT * FROM `{$this->table}` ORDER BY {$order}");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Ambil satu record berdasarkan primary key.
     *
     * @param int $id Nilai primary key
     * @return array|false
     */
    public function findById(int $id): array|false
    {
        DbC::requireValidId($id);
        $stmt = $this->conn->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Insert record baru. Hanya kolom yang ada di $fillable yang diproses.
     *
     * @param array $data Associative array ['column' => value]
     * @return int|false Last insert ID atau false jika gagal
     */
    public function insert(array $data): int|false
    {
        $filtered = $this->filterFillable($data);
        DbC::require(!empty($filtered), "Data insert tidak boleh kosong setelah difilter oleh fillable");

        $cols   = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($filtered)));
        $params = implode(', ', array_map(fn($c) => ":{$c}", array_keys($filtered)));
        $bound  = [];
        foreach ($filtered as $col => $val) {
            $bound[":{$col}"] = $val;
        }

        $stmt = $this->conn->prepare("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$params})");
        if ($stmt->execute($bound)) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update record berdasarkan primary key.
     *
     * @param int   $id   Nilai primary key
     * @param array $data Data yang akan diperbarui
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        DbC::requireValidId($id);
        $filtered = $this->filterFillable($data);
        DbC::require(!empty($filtered), "Data update tidak boleh kosong setelah difilter oleh fillable");

        $sets  = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($filtered)));
        $bound = [];
        foreach ($filtered as $col => $val) {
            $bound[":{$col}"] = $val;
        }
        $bound[':pk'] = $id;

        $stmt = $this->conn->prepare(
            "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = :pk"
        );
        return $stmt->execute($bound);
    }

    /**
     * Hapus record berdasarkan primary key.
     *
     * @param int $id Nilai primary key
     * @return bool
     */
    public function delete(int $id): bool
    {
        DbC::requireValidId($id);
        $stmt = $this->conn->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Hitung jumlah record.
     *
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM `{$this->table}`");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Filter data agar hanya kolom dalam $fillable yang lolos.
     */
    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data; // tanpa pembatasan jika fillable kosong
        }
        return array_filter(
            $data,
            fn($key) => in_array($key, $this->fillable, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
?>
