<?php
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [
    'PHP 8.1+' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'PDO extension' => extension_loaded('pdo'),
    'PDO MySQL driver' => extension_loaded('pdo_mysql'),
    'Fileinfo extension' => extension_loaded('fileinfo'),
    'mbstring extension' => extension_loaded('mbstring'),
    'Visitor page exists' => is_file($root . '/public/index.php'),
    'Upload endpoint exists' => is_file($root . '/public/upload.php'),
    'Slideshow exists' => is_file($root . '/public/slideshow.php'),
    'MySQL schema exists' => is_file($root . '/database/schema.sql'),
    'Assumptions documented' => is_file($root . '/ASSUMPTIONS.md'),
    'Architecture documented' => is_file($root . '/ARCHITECTURE.md'),
];
$failed = false;
foreach ($checks as $label => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$ok) $failed = true;
}
exit($failed ? 1 : 0);
