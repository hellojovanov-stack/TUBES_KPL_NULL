<?php

/**
 * Performance.php — Performance Measurement Helper
 *
 * Mengukur waktu eksekusi fungsi/callable menggunakan microtime().
 * Digunakan oleh PerformanceLogger dan tests/PerformanceTest.php.
 *
 * Teknik Konstruksi: Code Reuse / Library (Steven)
 */
class Performance
{
    /**
     * Ukur waktu eksekusi sebuah callable dalam milidetik.
     *
     * @param callable $fn         Fungsi yang diukur
     * @param int      $iterations Jumlah pengulangan untuk rata-rata
     * @return array{iterations: int, total_ms: float, avg_ms: float, min_ms: float, max_ms: float}
     */
    public static function measure(callable $fn, int $iterations = 1): array
    {
        DbC::require($iterations > 0, "iterations harus > 0");

        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $fn();
            $end   = microtime(true);
            $times[] = ($end - $start) * 1000; // convert to ms
        }

        $total = array_sum($times);

        return [
            'iterations' => $iterations,
            'total_ms'   => round($total, 4),
            'avg_ms'     => round($total / $iterations, 4),
            'min_ms'     => round(min($times), 4),
            'max_ms'     => round(max($times), 4),
        ];
    }

    /**
     * Benchmark beberapa fungsi sekaligus dan bandingkan hasilnya.
     *
     * @param array<string, callable> $cases      Map nama => callable
     * @param int                     $iterations Jumlah pengulangan tiap fungsi
     * @return array<string, array>               Hasil per nama fungsi
     */
    public static function benchmark(array $cases, int $iterations = 10): array
    {
        DbC::require(!empty($cases), "cases tidak boleh kosong");

        $results = [];
        foreach ($cases as $name => $fn) {
            $results[$name] = self::measure($fn, $iterations);
        }
        return $results;
    }

    /**
     * Format hasil benchmark menjadi string yang mudah dibaca.
     *
     * @param array $results Hasil dari measure() atau benchmark()
     * @return string
     */
    public static function format(array $results): string
    {
        // Detect apakah ini hasil benchmark (nested) atau measure (flat)
        $isNested = isset(array_values($results)[0]['avg_ms']);

        if ($isNested) {
            $lines = [];
            foreach ($results as $name => $r) {
                $lines[] = sprintf(
                    "%-30s | avg: %7.4f ms | min: %7.4f ms | max: %7.4f ms | iter: %d",
                    $name, $r['avg_ms'], $r['min_ms'], $r['max_ms'], $r['iterations']
                );
            }
            return implode("\n", $lines);
        }

        return sprintf(
            "total: %.4f ms | avg: %.4f ms | min: %.4f ms | max: %.4f ms | iter: %d",
            $results['total_ms'], $results['avg_ms'],
            $results['min_ms'],   $results['max_ms'], $results['iterations']
        );
    }
}
?>
