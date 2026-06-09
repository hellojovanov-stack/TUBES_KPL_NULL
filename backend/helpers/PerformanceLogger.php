<?php

/**
 * PerformanceLogger.php — Performance Logging Helper
 *
 * Menyimpan hasil pengukuran performa ke file JSON di folder logs/,
 * dan menyediakan laporan ringkasan untuk ditampilkan.
 *
 * Teknik Konstruksi: Code Reuse / Library (Steven)
 * Bergantung pada: Performance.php, DbC.php
 */
class PerformanceLogger
{
    private string $logDir;
    private string $logFile;

    public function __construct(string $logDir = '')
    {
        $this->logDir  = $logDir ?: __DIR__ . '/../../logs';
        $this->logFile = $this->logDir . '/performance_' . date('Y-m-d') . '.json';

        // Buat folder logs jika belum ada
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Jalankan benchmark + simpan ke log.
     *
     * @param string   $label      Label pengukuran
     * @param callable $fn         Fungsi yang diukur
     * @param int      $iterations Jumlah iterasi
     * @return array Hasil pengukuran
     */
    public function run(string $label, callable $fn, int $iterations = 10): array
    {
        DbC::requireNonEmpty($label, 'label');

        $result = Performance::measure($fn, $iterations);
        $result['label']     = $label;
        $result['timestamp'] = date('Y-m-d H:i:s');

        $this->appendLog($result);

        return $result;
    }

    /**
     * Jalankan banyak benchmark sekaligus dan log semua.
     *
     * @param array<string, callable> $cases
     * @param int                     $iterations
     * @return array<string, array>
     */
    public function runAll(array $cases, int $iterations = 10): array
    {
        $results = Performance::benchmark($cases, $iterations);

        foreach ($results as $label => $result) {
            $result['label']     = $label;
            $result['timestamp'] = date('Y-m-d H:i:s');
            $this->appendLog($result);
        }

        return $results;
    }

    /**
     * Baca semua log hari ini.
     *
     * @return array
     */
    public function readLogs(): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }
        $content = file_get_contents($this->logFile);
        return json_decode($content, true) ?? [];
    }

    /**
     * Cetak laporan ringkasan ke string.
     *
     * @return string
     */
    public function getSummary(): string
    {
        $logs = $this->readLogs();
        if (empty($logs)) {
            return "Belum ada log performa untuk hari ini.";
        }

        $lines = ["=== Performance Report — " . date('Y-m-d') . " ==="];
        foreach ($logs as $entry) {
            $lines[] = sprintf(
                "[%s] %-35s | avg: %7.4f ms | iter: %d",
                $entry['timestamp'] ?? '—',
                $entry['label']     ?? '—',
                $entry['avg_ms']    ?? 0,
                $entry['iterations'] ?? 0
            );
        }
        return implode("\n", $lines);
    }

    /**
     * Append satu entri ke file log JSON.
     */
    private function appendLog(array $entry): void
    {
        $logs   = $this->readLogs();
        $logs[] = $entry;
        file_put_contents($this->logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }
}
?>
