#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }

$repo = __DIR__;
// StackCP repo usually in /home/sites/38b/e/ebb1a48b11/repositories/darsenizami-seo
// or /home/sites/.../darsenizami-seo
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

echo "Deploying SEO updates to $public...\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($repo, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$exclude = ['.git', '.gitignore', 'README.md', 'deploy.php', 'deploy.sh'];
$count = 0;

foreach ($iterator as $item) {
    $subPath = str_replace('\\', '/', $iterator->getSubPathname());
    $firstPart = explode('/', $subPath)[0];
    if (in_array($firstPart, $exclude, true)) continue;
    
    $target = $public . '/' . $subPath;
    if ($item->isDir()) {
        if (!is_dir($target)) mkdir($target, 0755, true);
    } else {
        copy($item->getPathname(), $target);
        chmod($target, 0644);
        $count++;
    }
}

echo "Deployment complete! Successfully updated $count SEO files in public_html.\n";
