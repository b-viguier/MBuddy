<?php

if($argc !== 2) {
    die("Usage: {$argv[0]} <src-folder>\n");
}
$root = $argv[1];
$outDir = __DIR__.'/../web/scores/';

echo "Scanning $root\n";
foreach (glob($root . '/[0-9][0-9]*') ?: [] as $folder) {
    $files = glob($folder . "/*.mscz") ?: [];
    if(count($files) !== 1) {
        echo "Skip $folder\n";
        continue;
    }
    $file = $files[0];
    $name = str_replace(' ', '', basename($folder));
    echo "Exporting $name...\n";
    passthru("mscore \"$file\" -o \"$outDir$name.png\" -r 150");
    passthru("mv \"$outDir$name-1.png\" \"$outDir$name.png\" ");
}
echo "Done!\n";
