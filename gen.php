<?php

/**
 * Focus CMS - gen.php
 */

$themesPath = realpath(__DIR__.'/Themes');
$themes = [];

if (is_dir($themesPath)) {
    foreach (new DirectoryIterator($themesPath) as $fileInfo) {
        if ($fileInfo->isDot()) continue;

        if ($fileInfo->isDir() || $fileInfo->isLink()) {
            $targetPath = $fileInfo->isLink()
                ? realpath($fileInfo->getPathname())
                : $fileInfo->getRealPath();

                $targetPath = $fileInfo->getPathname();
            $themes[] = [
                'name' => $fileInfo->getFilename(),
                'path' => $targetPath,
                'relativePath' => str_replace(realpath(__DIR__).'/', '', $targetPath)
            ];

            print_r($fileInfo);
            echo "\n\n";
            print_r($fileInfo->getPathName());
            echo "\n\n";
            print_r($fileInfo->getRealPath());
            echo "\n\n";
        }
    }
}

file_put_contents(__DIR__.'/themes.json', json_encode($themes, JSON_PRETTY_PRINT));