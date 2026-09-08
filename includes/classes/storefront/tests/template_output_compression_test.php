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
    define('DIR_FS_INC', $repositoryRoot . '/inc/');
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

    $parentCss = 'templates/parent/css/parent.css';
    $childCss = 'templates/child/css/child.css';
    $parentAsset = 'templates/parent/fonts/parent.woff2';
    $childAsset = 'templates/child/css/images/child.svg';
    writeOutputTestFile(DIR_FS_CATALOG . $parentAsset, 'parent-font');
    writeOutputTestFile(DIR_FS_CATALOG . $childAsset, 'child-image');
    writeOutputTestFile(
        DIR_FS_CATALOG . $parentCss,
        '.parent{src:url(../fonts/parent.woff2?v=1#font)} .data{src:url(data:image/svg+xml;base64,PHN2Zz4=)}'
    );
    writeOutputTestFile(
        DIR_FS_CATALOG . $childCss,
        '.child{background:url(images/child.svg)} .external{src:url(https://cdn.example/font.woff2)} .absolute{src:url(/images/root.svg)} .fragment{filter:url(#icon)}'
    );

    $cssTarget = 'templates/' . CURRENT_TEMPLATE . '/css/combined.min.css';
    $cssResult = combine_files(array($parentCss, $childCss), $cssTarget, true);
    $combinedCss = file_get_contents(DIR_FS_CATALOG . $cssTarget);
    assertOutput(
        count($cssResult) === 1 && str_starts_with($cssResult[0], $cssTarget . '?v='),
        'Das kombinierte CSS muss als Child-Ausgabe ausgeliefert werden.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, '../../parent/fonts/parent.woff2?v=1#font'),
        'Das kombinierte CSS muss Parent-URLs relativ zur Child-Ausgabe umschreiben.'
    );
    assertOutput(
        realpath(dirname(DIR_FS_CATALOG . $cssTarget) . '/../../parent/fonts/parent.woff2') === realpath(DIR_FS_CATALOG . $parentAsset),
        'Die umgeschriebene Parent-URL muss auf das physische Parent-Asset zeigen.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, 'url(images/child.svg)'),
        'Das kombinierte CSS muss Child-URLs relativ zur Child-Ausgabe erhalten.'
    );
    assertOutput(
        realpath(dirname(DIR_FS_CATALOG . $cssTarget) . '/images/child.svg') === realpath(DIR_FS_CATALOG . $childAsset),
        'Die Child-URL muss auf das physische Child-Asset zeigen.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, 'url(data:image/svg+xml;base64,PHN2Zz4=)'),
        'Eingebettete data:-URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, 'url(https://cdn.example/font.woff2)'),
        'Externe URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, 'url(/images/root.svg)'),
        'Absolute URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, 'url(#icon)'),
        'Fragment-URLs dürfen nicht verändert werden.'
    );

    echo sprintf("Template-Komprimierung: %d Assertions erfolgreich.\n", $assertions);
} finally {
    removeOutputTestDirectory($temporaryDirectory);
}
