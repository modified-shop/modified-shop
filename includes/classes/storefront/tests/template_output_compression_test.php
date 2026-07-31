<?php

$assertions = 0;
$temporaryDirectory = sys_get_temp_dir() . '/modified-template-output-' . bin2hex(random_bytes(8));
$repositoryRoot = dirname(__DIR__, 4);

function assertOutput(bool $condition, string $message): void
{
    global $assertions;
    ++$assertions;

    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function writeOutputTestFile(string $path, string $contents): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException(sprintf('Testverzeichnis "%s" konnte nicht angelegt werden.', $directory));
    }

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException(sprintf('Testdatei "%s" konnte nicht geschrieben werden.', $path));
    }
}

function removeOutputTestDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($directory);
}

try {
    define('DIR_FS_CATALOG', $temporaryDirectory . '/');
    define('DIR_FS_EXTERNAL', $repositoryRoot . '/includes/external/');
    define('CURRENT_TEMPLATE', 'child');

    $source = 'templates/parent/assets/source.js';
    writeOutputTestFile(DIR_FS_CATALOG . $source, 'const inherited = true;');

    $outputs = array(
        'stylesheet.min.css',
        'css/tpl_plugins.min.css',
        'javascript/tpl_plugins.min.js',
    );

    foreach ($outputs as $logicalOutput) {
        writeOutputTestFile(DIR_FS_CATALOG . 'templates/parent/' . $logicalOutput, 'parent-output');
    }

    require $repositoryRoot . '/templates/tpl_modified_nova/source/inc/combine_files.inc.php';

    foreach ($outputs as $logicalOutput) {
        $target = 'templates/' . CURRENT_TEMPLATE . '/' . $logicalOutput;
        $result = combine_files(array($source), $target);

        assertOutput(is_file(DIR_FS_CATALOG . $target), sprintf('Die fehlende Child-Ausgabe "%s" muss erzeugt werden.', $logicalOutput));
        assertOutput(filesize(DIR_FS_CATALOG . $target) > 0, sprintf('Die Child-Ausgabe "%s" darf nicht leer sein.', $logicalOutput));
        assertOutput(count($result) === 1 && str_starts_with($result[0], $target . '?v='), sprintf('Die erzeugte Child-Ausgabe "%s" muss ausgeliefert werden.', $logicalOutput));
        assertOutput(file_get_contents(DIR_FS_CATALOG . 'templates/parent/' . $logicalOutput) === 'parent-output', sprintf('Die Parent-Ausgabe "%s" darf nicht verändert werden.', $logicalOutput));
    }

    echo sprintf("Template-Komprimierung: %d Assertions erfolgreich.\n", $assertions);
} finally {
    removeOutputTestDirectory($temporaryDirectory);
}
