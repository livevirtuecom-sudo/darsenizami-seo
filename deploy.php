#!/usr/bin/env php
<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }

$repo = __DIR__;
$candidates = [
    '/home/sites/38b/e/ebb1a48b11/public_html',
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

// 1. Restore original server .htaccess from backup and apply StackCDN-safe SEO rewrites
$siteRoot = dirname($public);
$backups = glob($siteRoot . '/dn-seo-backup-*');
if ($backups) {
    rsort($backups);
    $latestBackup = $backups[0];
    if (file_exists($latestBackup . '/.htaccess')) {
        echo "Found original .htaccess in $latestBackup, restoring server rules...\n";
        $origHtaccess = file_get_contents($latestBackup . '/.htaccess');
        
        // Strip any previously added SEO block
        $cleanOrig = preg_replace('/# ---- DARSENIZAMI SEO RULES START ----.*?# ---- DARSENIZAMI SEO RULES END ----\s*/s', '', $origHtaccess);
        
        // Safe SEO rules WITHOUT %{HTTPS} != on to prevent StackCDN proxy loop
        $seoHeader = "# ---- DARSENIZAMI SEO RULES START ----\n"
            . "<IfModule mod_rewrite.c>\n"
            . "RewriteEngine On\n"
            . "RewriteBase /\n"
            . "# Force Non-WWW\n"
            . "RewriteCond %{HTTP_HOST} ^www\\.darsenizami\\.net$ [NC]\n"
            . "RewriteRule ^(.*)$ https://darsenizami.net/$1 [L,R=301]\n"
            . "# Block leaked archive\n"
            . "RewriteRule ^dn-lms-hosting-upload\\.zip$ - [G,L]\n"
            . "# Subdirectory pass-through\n"
            . "RewriteRule ^online(/.*)?$ - [L]\n"
            . "RewriteRule ^lms(/.*)?$ - [L]\n"
            . "# Canonical aliases\n"
            . "RewriteRule ^index\\.html$ https://darsenizami.net/ [R=301,L,NE]\n"
            . "RewriteRule ^alimah-course/index\\.html$ https://darsenizami.net/alimah-course/ [R=301,L,NE]\n"
            . "RewriteRule ^faq/?$ https://darsenizami.net/faq.html [R=301,L,NE]\n"
            . "RewriteRule ^contact/?$ https://darsenizami.net/contact.html [R=301,L,NE]\n"
            . "RewriteRule ^courses/?$ https://darsenizami.net/dars-e-nizami-course.html [R=301,L,NE]\n"
            . "RewriteRule ^darul-ifta/?$ https://darsenizami.net/darulifta/ [R=301,L,NE]\n"
            . "</IfModule>\n"
            . "# ---- DARSENIZAMI SEO RULES END ----\n\n";
            
        file_put_contents($public . '/.htaccess', $seoHeader . $cleanOrig);
        chmod($public . '/.htaccess', 0644);
        echo "Restored original server .htaccess and applied CDN-safe SEO rules.\n";
    }
}

// 2. Deploy HTML files, robots.txt, sitemaps
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($repo, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$exclude = ['.git', '.gitignore', '.gitattributes', '.cpanel.yml', 'README.md', 'deploy.php', 'deploy.sh', '.htaccess'];
$count = 0;

foreach ($iterator as $item) {
    $subPath = str_replace('\\', '/', $iterator->getSubPathname());
    $firstPart = explode('/', $subPath)[0];
    if (in_array($firstPart, $exclude, true)) continue;
    if ($firstPart === 'lms' || $firstPart === 'online' || $firstPart === 'cms') continue;

    $target = $public . '/' . $subPath;
    if ($item->isDir()) {
        if (!is_dir($target)) mkdir($target, 0755, true);
    } else {
        copy($item->getPathname(), $target);
        chmod($target, 0644);
        $count++;
    }
}

echo "SUCCESS: Restored live routing and synced $count SEO pages.\n";
