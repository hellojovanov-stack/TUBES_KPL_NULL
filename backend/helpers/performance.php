<?php

class Performance {

    public static function start() {
        return microtime(true);
    }
    public static function end($startTime) {
        $endTime = microtime(true);
        return round(($endTime - $startTime), 5);
    }
    public static function measure($callback) {
        $start = self::start();
        $result = $callback();
        $time = self::end($start);
        
        return [
            'result' => $result,
            'time_seconds' => $time,
            'time_ms' => $time * 1000
        ];
    }
    public static function benchmark($callback, $runs = 10) {
        $times = [];
        
        for ($i = 0; $i < $runs; $i++) {
            $start = self::start();
            $callback();
            $times[] = self::end($start);
        }
        
        return [
            'min' => min($times) * 1000,
            'max' => max($times) * 1000,
            'avg' => (array_sum($times) / $runs) * 1000,
            'runs' => $runs
        ];
    }
}