<?php

declare(strict_types=1);

const DST_HOST = 'http://192.168.1.25:8080/MBuddy/web/sync.php';
const ROOT = __DIR__ . '/../';
const SRC = [
    'composer.json',
    'composer.lock',
    'web/',
];


function fileIterator(): iterable
{
    foreach (SRC as $src) {
        if(str_ends_with($src, '/')) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                ROOT . $src,
                FilesystemIterator::CURRENT_AS_PATHNAME|FilesystemIterator::SKIP_DOTS,
            ));
            foreach ($iterator as $file) {
                yield substr($file, strlen(ROOT));
            }
        } else {
            yield $src;
        }
    }
}


foreach (fileIterator() as $file) {
    echo $file . PHP_EOL;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => DST_HOST,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'file' => new \CURLFile(filename: ROOT . $file, posted_filename: $file),
            'dst' => $file,
        ],
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    echo $result . PHP_EOL;
}
