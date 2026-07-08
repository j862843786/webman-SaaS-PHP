<?php

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/create-shards.php <base_table> <modulo> [schema_sql]\n");
    exit(1);
}

$baseTable = $argv[1];
$modulo = max(1, (int) $argv[2]);
$schemaSql = $argv[3] ?? null;
$width = max(2, strlen((string) ($modulo - 1)));

for ($bucket = 0; $bucket < $modulo; $bucket++) {
    $table = sprintf('%s_%0' . $width . 'd', $baseTable, $bucket);
    if ($schemaSql) {
        echo str_replace('{table}', $table, $schemaSql) . ";\n";
        continue;
    }

    echo sprintf("CREATE TABLE IF NOT EXISTS `%s` LIKE `%s`;\n", $table, $baseTable);
}
