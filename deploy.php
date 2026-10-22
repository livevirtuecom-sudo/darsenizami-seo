#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }

$repo = __DIR__;
$candidates = [
    dirname($repo, 2) . '/public_html',
    dirname($repo) . '/public_html',
    $argv[1] ?? ''
];

$public = null;
foreach ($candidates as $c) {
    if ($c && is_dir($c)) { $public = $c; break; }
}

if (!$public) {
    fwrite(STDERR, "Error: public_html directory not found.\n");
    exit(1);
}

// 1. Create a safe timestamped backup folder outside public_html
$backupDir = dirname($public) . '/dn-seo-backup-' . gmdate('Ymd-His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0700, true);
}

echo "=== DARSENIZAMI.NET SEO DEPLOYMENT ===\n";
echo "Target Web Root: $public\n";
echo "Safety Backup Location: $backupDir\n\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($repo, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$exclude = ['.git', '.gitignore', 'README.md', 'deploy.php', 'deploy.sh'];
$count = 0;
$backedUp = 0;

foreach ($iterator as $item) {
    $subPath = str_replace('\\', '/', $iterator->getSubPathname());
    $firstPart = explode('/', $subPath)[0];
    if (in_array($firstPart, $exclude, true)) continue;
    
    // Safety check: Never touch lms or online subdirectories
    if ($firstPart === 'lms' || $firstPart === 'online' || $firstPart === 'cms') continue;

    $target = $public . '/' . $subPath;
    $bTarget = $backupDir . '/' . $subPath;

    if ($item->isDir()) {
        if (!is_dir($target)) mkdir($target, 0755, true);
        if (!is_dir($bTarget)) mkdir($bTarget, 0700, true);
    } else {
        // Backup existing file before overwriting
        if (file_exists($target)) {
            $bParent = dirname($bTarget);
            if (!is_dir($bParent)) mkdir($bParent, 0700, true);
            copy($target, $bTarget);
            $backedUp++;
        }
        copy($item->getPathname(), $target);
        chmod($target, 0644);
        $count++;
    }
}

echo "SUCCESS:\n";
echo "- $backedUp existing files safely backed up to $backupDir\n";
echo "- $count SEO files deployed cleanly to public_html\n";
echo "- /lms/, /online/ (WordPress), and private databases were NOT touched.\n";
