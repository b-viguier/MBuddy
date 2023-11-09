<?php

declare(strict_types=1);

const ROOT = __DIR__ . '/../';


$dstFile = ROOT . $_POST['dst'];
$dstDir = dirname($dstFile);
if (!is_dir($dstDir)) {
    mkdir($dstDir, recursive: true);
}
move_uploaded_file($_FILES['file']['tmp_name'], $dstFile);
echo 'OK';
