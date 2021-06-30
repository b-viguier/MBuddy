<?php

if($argc !== 2) {
    die("Usage: {$argv[0]} <src-folder>\n");
}
$root = $argv[1];
$outDir = __DIR__ . '/../web/scores/';

foreach (glob($root . '[0-9][0-9]*') as $folder) {
    $files = glob($folder . "/*.mscz");
    if(count($files)!== 1) {
        echo "Skip $folder\n";
        continue;
    }
    $file = $files[0];
    $id = substr(basename($folder), 0, 2);
    passthru("mscore \"$file\" -o $outDir$id.png -r 150");
    passthru("mv $outDir$id-1.png $outDir$id.png");
}


