<?php

$baseDir = realpath(__DIR__.'/..');

// 1. Resolve Application Version
$version = 'v2.5.1';
$appConfig = @file_get_contents($baseDir.'/config/app.php');
if ($appConfig && preg_match("/'version'\s*=>\s*env\(['\"]APP_VERSION['\"],\s*['\"]([^'\"]+)['\"]\)/", $appConfig, $matches)) {
    $version = trim($matches[1]);
}

if (isset($argv[1]) && ! empty($argv[1])) {
    $version = trim($argv[1]);
}

if (! str_starts_with($version, 'v')) {
    $version = 'v'.$version;
}

$versionedZipFile = $baseDir."/jmos-{$version}.zip";
$latestZipFile = $baseDir.'/jmos-latest.zip';

if (file_exists($versionedZipFile)) {
    @unlink($versionedZipFile);
}
if (file_exists($latestZipFile)) {
    @unlink($latestZipFile);
}

$zip = new ZipArchive;
if ($zip->open($versionedZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "Failed to create zip archive: {$versionedZipFile}\n";
    exit(1);
}

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

// Also copy to jmos-latest.zip for compatibility
@copy($versionedZipFile, $latestZipFile);

echo "JMOS Versioned Archive Created:\n";
echo "  - File: jmos-{$version}.zip (".filesize($versionedZipFile)." bytes)\n";
echo '  - File: jmos-latest.zip ('.filesize($latestZipFile)." bytes)\n";
