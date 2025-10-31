<?php

declare(strict_types=1);

// 查找并加载 autoloader
$possibleAutoloaders = [
    __DIR__ . '/../vendor/autoload.php',         // 当前包的 vendor
    __DIR__ . '/../../../vendor/autoload.php',   // monorepo 根目录的 vendor
    __DIR__ . '/../../../../vendor/autoload.php', // 其他可能的位置
];

$autoloaderFound = false;
foreach ($possibleAutoloaders as $autoloader) {
    if (file_exists($autoloader)) {
        require_once $autoloader;
        $autoloaderFound = true;
        break;
    }
}

if (!$autoloaderFound) {
    echo "错误：未找到 Composer autoloader。请先运行 'composer install'。\n";
    exit(1);
}

// 设置错误报告级别
error_reporting(E_ALL);

// 设置时区
date_default_timezone_set('UTC');
