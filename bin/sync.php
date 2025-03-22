<?php

declare(strict_types=1);

const DST_HOST = 'http://192.168.1.161:8080/MBuddy/web/sync.php';
const ROOT = __DIR__.'/../';
const SRC = [
    'composer.json',
    'composer.lock',
    '.env',
    'phpwin.json',
    'web/',
    'src/',
    'public/',
    'config/',
    'templates/',
    'packages/',
];


function fileIterator(): iterable
{
    foreach (SRC as $src) {
        if (str_ends_with($src, '/')) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    ROOT.$src,
                    FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS,
                ),
            );
            foreach ($iterator as $file) {
                yield substr($file, strlen(ROOT));
            }
        } else {
            yield $src;
        }
    }
}

function filteredFileIterator(): iterable
{
    $timestampFile = new \SplFileInfo(__DIR__.'/.sync-timestamp.txt');
    $timestamp = $timestampFile->isFile() ? $timestampFile->getMTime() : 0;

    foreach (fileIterator() as $file) {
        $fileInfo = new \SplFileInfo(ROOT.$file);
        if ($fileInfo->getMTime() > $timestamp) {
            yield $file;
        }
    }

    touch($timestampFile->getPathname());
}


$ch = curl_init();
foreach (filteredFileIterator() as $file) {
    echo "$file\t";
    curl_setopt_array($ch, [
        \CURLOPT_SSL_VERIFYPEER => FALSE,
        \CURLOPT_URL => DST_HOST,
        \CURLOPT_POST => true,
        \CURLOPT_RETURNTRANSFER => true,
        \CURLOPT_CONNECTTIMEOUT => 2,
        \CURLOPT_TIMEOUT => 5,
        \CURLOPT_POSTFIELDS => [
            'file' => new \CURLFile(filename: ROOT.$file, posted_filename: $file),
            'dst' => $file,
        ],
    ]);
    $result = curl_exec($ch);
    if ($statusCode = curl_errno($ch)) {
        $result .= "\n".curl_strerror($statusCode);
    } else {
        $statusCode = 'HTTP'.curl_getinfo($ch, \CURLINFO_HTTP_CODE);
    }

    if ($statusCode === 'HTTP200') {
        echo "✅\n";
    } else {
        echo "❌ ($statusCode)\n$result\n\n";
        break;
    }
}
curl_close($ch);
