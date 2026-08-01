<?php

use Modified\Storefront\Template\CatalogCssFileRewriter;
use Modified\Storefront\Template\CssUrlRewriter;

$assertions = 0;
$temporaryDirectory = sys_get_temp_dir() . '/modified-template-output-' . bin2hex(random_bytes(8));
$repositoryRoot = dirname(__DIR__, 4);

require_once $repositoryRoot . '/includes/classes/storefront/bootstrap.php';

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
    define('DIR_WS_CATALOG', '/catalog/');
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
    writeOutputTestFile(
        DIR_FS_CATALOG . $parentCss,
        '.parent{src:url("../css/fonts/parent.woff2?v=1#font")} .data{src:url(data:image/svg+xml;base64,PHN2Zz4=)}'
    );
    writeOutputTestFile(
        DIR_FS_CATALOG . $childCss,
        '.child{background:url(images/child.svg)} .external{src:url(https://cdn.example/font.woff2)} .absolute{src:url(/images/root.svg)} .fragment{filter:url(#icon)}'
    );

    $rewriter = CatalogCssFileRewriter::fromGlobals();
    $rewrittenParentCss = $rewriter->rewriteFile($parentCss);
    assertOutput(
        str_contains($rewrittenParentCss, 'url("/catalog/templates/parent/css/fonts/parent.woff2?v=1#font")'),
        'Relative Parent-URLs müssen gegen den physischen Parent-Quellpfad aufgelöst werden.'
    );
    assertOutput(
        str_contains($rewrittenParentCss, 'url(data:image/svg+xml;base64,PHN2Zz4=)'),
        'Eingebettete data:-URLs dürfen nicht verändert werden.'
    );

    $rewrittenChildCss = $rewriter->rewriteFile($childCss);
    assertOutput(
        str_contains($rewrittenChildCss, 'url(/catalog/templates/child/css/images/child.svg)'),
        'Relative Child-URLs müssen gegen den physischen Child-Quellpfad aufgelöst werden.'
    );
    assertOutput(
        str_contains($rewrittenChildCss, 'url(https://cdn.example/font.woff2)'),
        'Externe URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        str_contains($rewrittenChildCss, 'url(/images/root.svg)'),
        'Absolute URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        str_contains($rewrittenChildCss, 'url(#icon)'),
        'Fragment-URLs dürfen nicht verändert werden.'
    );
    assertOutput(
        str_contains(
            (new CatalogCssFileRewriter(new CssUrlRewriter(), DIR_FS_CATALOG, '/'))->rewriteFile($parentCss),
            'url("/templates/parent/css/fonts/parent.woff2?v=1#font")'
        ),
        'Ein Shop in der Katalogwurzel muss root-relative CSS-URLs behalten.'
    );
    assertOutput(
        (new CssUrlRewriter())->rewrite(
            '.asset{src:url(../fonts/font.woff2)}',
            'https://cdn.example/assets/css/main.css'
        ) === '.asset{src:url(https://cdn.example/assets/fonts/font.woff2)}',
        'Der allgemeine CSS-Rewriter muss ohne Shop- oder Dateisystemkontext arbeiten.'
    );

    $cssTarget = 'templates/' . CURRENT_TEMPLATE . '/css/combined.min.css';
    $cssResult = combine_files(array($parentCss, $childCss), $cssTarget, true);
    $combinedCss = file_get_contents(DIR_FS_CATALOG . $cssTarget);
    assertOutput(
        count($cssResult) === 1 && str_starts_with($cssResult[0], $cssTarget . '?v='),
        'Das kombinierte CSS muss als Child-Ausgabe ausgeliefert werden.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, '/catalog/templates/parent/css/fonts/parent.woff2?v=1#font'),
        'Das kombinierte CSS muss umgeschriebene Parent-URLs enthalten.'
    );
    assertOutput(
        is_string($combinedCss) && str_contains($combinedCss, '/catalog/templates/child/css/images/child.svg'),
        'Das kombinierte CSS muss umgeschriebene Child-URLs enthalten.'
    );

    echo sprintf("Template-Komprimierung: %d Assertions erfolgreich.\n", $assertions);
} finally {
    removeOutputTestDirectory($temporaryDirectory);
}
