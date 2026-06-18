<?php

/**
 * TableDrivenValidator.php — Table-Driven Construction
 * Teknik Konstruksi: Table-Driven Construction 
 */
class TableDrivenValidator
{
    /**
     * Tabel aturan validasi untuk masing-masing entitas.
     * Format: ['field' => string, 'rule' => string, 'param' => mixed, 'message' => string]
     */
    private static array $ruleTables = [
        'obat' => [
            ['field' => 'nama_obat', 'rule' => 'required',  'param' => null, 'message' => 'Nama obat tidak boleh kosong'],
            ['field' => 'nama_obat', 'rule' => 'maxLength', 'param' => 255,  'message' => 'Nama obat maksimal 255 karakter'],
            ['field' => 'stok',      'rule' => 'min',        'param' => 0,    'message' => 'Stok tidak boleh negatif'],
            ['field' => 'harga',     'rule' => 'min',        'param' => 0,    'message' => 'Harga tidak boleh negatif'],
            ['field' => 'harga',     'rule' => 'numeric',    'param' => null, 'message' => 'Harga harus berupa angka'],
        ],
        'kategori' => [
            ['field' => 'nama_kategori', 'rule' => 'required',  'param' => null, 'message' => 'Nama kategori tidak boleh kosong'],
            ['field' => 'nama_kategori', 'rule' => 'maxLength', 'param' => 100,  'message' => 'Nama kategori maksimal 100 karakter'],
        ],
        'supplier' => [
            ['field' => 'nama_supplier', 'rule' => 'required',  'param' => null, 'message' => 'Nama supplier tidak boleh kosong'],
            ['field' => 'nama_supplier', 'rule' => 'maxLength', 'param' => 255,  'message' => 'Nama supplier maksimal 255 karakter'],
            ['field' => 'telepon',       'rule' => 'maxLength', 'param' => 50,   'message' => 'Nomor telepon maksimal 50 karakter'],
        ],
        'transaksi' => [
            ['field' => 'id_obat',   'rule' => 'min',     'param' => 1, 'message' => 'id_obat harus valid (> 0)'],
            ['field' => 'jumlah',    'rule' => 'min',     'param' => 1, 'message' => 'Jumlah minimal 1'],
            ['field' => 'sub_total', 'rule' => 'min',     'param' => 0, 'message' => 'Sub total tidak boleh negatif'],
            ['field' => 'sub_total', 'rule' => 'numeric', 'param' => null, 'message' => 'Sub total harus berupa angka'],
        ],
        'user' => [
            ['field' => 'username', 'rule' => 'required',  'param' => null, 'message' => 'Username tidak boleh kosong'],
            ['field' => 'username', 'rule' => 'maxLength', 'param' => 100,  'message' => 'Username maksimal 100 karakter'],
            ['field' => 'password', 'rule' => 'required',  'param' => null, 'message' => 'Password tidak boleh kosong'],
            ['field' => 'password', 'rule' => 'minLength', 'param' => 4,    'message' => 'Password minimal 4 karakter'],
        ],
    ];

    /**
     * Validasi data berdasarkan tabel aturan entitas tertentu.
     *
     * @param string $entity Nama entitas ('obat', 'kategori', 'supplier', 'transaksi', 'user')
     * @param array  $data   Data input yang akan divalidasi
     * @return array{valid: bool, errors: string[]}
     */
    public static function validate(string $entity, array $data): array
    {
        if (!isset(self::$ruleTables[$entity])) {
            return ['valid' => true, 'errors' => []];
        }

        $errors = [];

        foreach (self::$ruleTables[$entity] as $rule) {
            $field   = $rule['field'];
            $type    = $rule['rule'];
            $param   = $rule['param'];
            $message = $rule['message'];
            $value   = $data[$field] ?? null;

            $failed = match ($type) {
                'required'  => empty($value) && $value !== '0' && $value !== 0,
                'maxLength' => isset($value) && strlen((string)$value) > $param,
                'minLength' => !isset($value) || strlen((string)$value) < $param,
                'min'       => !isset($value) || (float)$value < $param,
                'max'       => isset($value) && (float)$value > $param,
                'numeric'   => isset($value) && !is_numeric($value),
                default     => false,
            };

            if ($failed) {
                $errors[] = $message;
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validasi dan lempar exception jika gagal.
     * @throws InvalidArgumentException jika ada rule yang gagal
     */
    public static function validateOrFail(string $entity, array $data): void
    {
        $result = self::validate($entity, $data);
        if (!$result['valid']) {
            throw new InvalidArgumentException(implode('; ', $result['errors']));
        }
    }
}
?>
