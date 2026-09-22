<?php

$zipFile = __DIR__.'/../jmos-latest.zip';
if (file_exists($zipFile)) {
    @unlink($zipFile);
}

$zip = new ZipArchive;
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "Failed to create zip archive.\n";
    exit(1);
}

$baseDir = realpath(__DIR__.'/..');

$folders = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage'];
foreach ($folders as $folder) {
    $folderPath = $baseDir.DIRECTORY_SEPARATOR.$folder;
    if (! is_dir($folderPath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $realPath = $item->getRealPath();
        $relPath = str_replace('\\', '/', substr($realPath, strlen($baseDir) + 1));

        // Skip node_modules, .git, vendor, cache files and locked storage
        if (str_contains($relPath, 'node_modules') || str_contains($relPath, 'vendor') || str_contains($relPath, '.git') || str_contains($relPath, 'storage/logs') || str_contains($relPath, 'storage/framework')) {
            continue;
        }

        if ($item->isDir()) {
            $zip->addEmptyDir($relPath);
        } else {
            $content = @file_get_contents($realPath);
            if ($content !== false) {
                $zip->addFromString($relPath, $content);
            }
        }
    }
}

$standaloneFiles = ['.env.example', 'composer.json', 'package.json', 'README.md', 'vite.config.js', 'artisan'];
foreach ($standaloneFiles as $f) {
    $filePath = $baseDir.DIRECTORY_SEPARATOR.$f;
    if (file_exists($filePath)) {
        // Read file contents to avoid file locking on active processes
        $content = @file_get_contents($filePath);
        if ($content !== false) {
            $zip->addFromString($f, $content);
        }
    }
}

$zip->close();
echo 'JMOS archive created successfully: '.filesize($zipFile)." bytes\n";
