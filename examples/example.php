<?php

/**
 * @TODO
 * 
 * extract the glyphicons to a separate package
 * extract flags to a separate package
 * specify the font.yml format
 */
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$config = require_once realpath(__DIR__ . '/config.php') ?: [];

$target = 'build'; // target folder for the generated CSS and fonts
$theme = 'default'; // CSS filename

// local fonts source files
if (!\is_dir(__DIR__ . '/fonts/')) {
    \mkdir(__DIR__ . '/fonts/', 0777, true);
}

// local less files
if (!\is_dir(__DIR__ . '/less/')) {
    \mkdir(__DIR__ . '/less/', 0777, true);
}

// used during process to store temporary files
if (!\is_dir(__DIR__ . '/tmp/')) {
    \mkdir(__DIR__ . '/tmp/', 0777, true);
}

// target folders
if (!\is_dir(__DIR__ . '/' . $target . '/')) {
    \mkdir(__DIR__ . '/' . $target . '/', 0777, true);
}
if (!\is_dir(__DIR__ . '/' . $target . '/css/')) {
    \mkdir(__DIR__ . '/' . $target . '/css/', 0777, true);
}
if (!\is_dir(__DIR__ . '/' . $target . '/fonts/')) {
    \mkdir(__DIR__ . '/' . $target . '/fonts/', 0777, true);
}
if (!\is_dir(__DIR__ . '/' . $target . '/images/')) {
    \mkdir(__DIR__ . '/' . $target . '/images/', 0777, true);
}

require_once __DIR__ . '/src/MyCssMaker.php';

$output = new \JDZ\Output\Output();
$output->setVerbosity(\JDZ\Output\Output::VERBOSITY_ALL);

$nodejsBinPath = realpath(__DIR__ . '/../node_modules/.bin/') . DIRECTORY_SEPARATOR;

$less = new MyCssMaker($output, $nodejsBinPath);

try {
    $less->setBuildPaths(realpath(__DIR__ . '/'), $target);

    $less->addLessFiles($config['less'] ?: []);
    $less->addLocalFonts($config['fonts'] ?: []);
    $less->addGlyphicons($config['glyphicons'] ?: []);
    $less->addFlags($config['flags'] ?: []);

    $less->process($theme);
} catch (\Throwable $e) {
    echo (string)$e;
}

$output->toFile(realpath(__DIR__) . '/dump.txt');
