#!/usr/bin/env phpdbg -qrr
<?php

declare(strict_types=1);

if (!function_exists('phpdbg_start_oplog') || !function_exists('phpdbg_end_oplog') || !function_exists('phpdbg_get_executable')) {
    print("Must be run via `phpdbg`\n");
    exit(1);
}

if (count($argv) !== 2) {
    print("Provide exactly one argument\n");
    exit(1);
}

function array_merge_numbered(array ...$arrays): array
{
    $result = array_shift($arrays);
    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            if (!array_key_exists($key, $result)) {
                continue;
            }

            $result[$key] = $value;
        }
    }
    return $result;
}

class Profiler
{
    static function profile(string $path, string ...$ignored)
    {
        static::profile_start();
        include $path;
        static::profile_end($path, ...$ignored);
    }

    static function profile_start()
    {
        phpdbg_start_oplog();
        ob_start();
    }

    static function profile_end(string $path, string ...$ignored)
    {
        if (ob_get_level() !== 0) {
            ob_end_clean();
        }

        $samples = phpdbg_end_oplog();
        $total = phpdbg_get_executable();
        $result = [];

        foreach (array_diff(array_keys($total), $ignored) as $path) {
            $result[$path] = array_merge_numbered($total[$path], $samples[$path]);
        }

        foreach ($result as $path => $coverage) {
            static::render_coverage($path, $coverage);
        }
    }

    static function render_coverage(string $path, array $coverage)
    {
        $file = fopen($path, 'r');
        assert($file !== false, "could not `fopen` {$path}");

        printf("%s\n", $path);

        $max_count = max($coverage);
        $width = ceil(log10($max_count)) + 1;

        for ($i = 1; !feof($file); $i++) {
            $line = fgets($file);
            if ($line === false) {
                break;
            }

            printf("%{$width}d| %s", $coverage[$i] ?? '', $line);
        }

        fclose($file);
    }
}

$self = realpath($argv[0]);
assert($self !== false, "could not get `realpath` for {$argv[0]}");
$path = realpath($argv[1]);
assert($path !== false, "could not get `realpath` for {$argv[1]}");

Profiler::profile($path, $self);
