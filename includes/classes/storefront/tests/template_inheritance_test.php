<?php

use Modified\Storefront\Template\Exception\InvalidTemplatePathException;
use Modified\Storefront\Template\Exception\ParentTemplateNotFoundException;
use Modified\Storefront\Template\Exception\TemplateFileNotFoundException;
use Modified\Storefront\Template\Exception\TemplateInheritanceCycleException;
use Modified\Storefront\Template\Exception\TemplateManifestInvalidException;
use Modified\Storefront\Template\TemplateChainResolver;
use Modified\Storefront\Template\TemplateFileResolver;
use Modified\Storefront\Template\TemplateId;
use Modified\Storefront\Template\TemplateManifestRepository;
use Modified\Storefront\Template\TemplateRuntime;
use Modified\Storefront\Template\TemplateUrlGenerator;

require_once dirname(__DIR__) . '/bootstrap.php';

$assertions = 0;
$temporaryDirectory = sys_get_temp_dir() . '/modified-template-pr1-' . bin2hex(random_bytes(8));
$templatesDirectory = $temporaryDirectory . '/templates';

function assertSameValue($expected, $actual, string $message): void
{
    global $assertions;
    ++$assertions;

    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            "%s\nErwartet: %s\nErhalten: %s",
            $message,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

function assertInstanceOfClass(string $expectedClass, $actual, string $message): void
{
    global $assertions;
    ++$assertions;

    if (!$actual instanceof $expectedClass) {
        throw new RuntimeException(sprintf(
            '%s Erwartet wurde %s, erhalten wurde %s.',
            $message,
            $expectedClass,
            is_object($actual) ? get_class($actual) : gettype($actual)
        ));
    }
}

function assertThrowsException(string $exceptionClass, callable $operation, string $message): void
{
    global $assertions;
    ++$assertions;

    try {
        $operation();
    } catch (Throwable $exception) {
        if ($exception instanceof $exceptionClass) {
            return;
        }

        throw new RuntimeException(sprintf(
            '%s Erwartet wurde %s, erhalten wurde %s: %s',
            $message,
            $exceptionClass,
            get_class($exception),
            $exception->getMessage()
        ), 0, $exception);
    }

    throw new RuntimeException(sprintf('%s Es wurde keine Ausnahme ausgelöst.', $message));
}

function writeTestFile(string $path, string $contents): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException(sprintf('Testverzeichnis "%s" konnte nicht angelegt werden.', $directory));
    }

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException(sprintf('Testdatei "%s" konnte nicht geschrieben werden.', $path));
    }
}

function removeTestDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($directory);
}

function xtc_href_link(): string
{
    return '';
}

try {
    mkdir($templatesDirectory, 0777, true);

    mkdir($templatesDirectory . '/without-manifest');
    $repository = new TemplateManifestRepository($templatesDirectory);
    $emptyManifest = $repository->get(new TemplateId('without-manifest'));
    assertSameValue(null, $emptyManifest->parent(), 'Ein fehlendes Manifest muss ein leeres Manifest ohne Parent liefern.');
    assertSameValue([], $emptyManifest->rawData(), 'Ein fehlendes Manifest muss leere Rohdaten liefern.');

    mkdir($templatesDirectory . '/invalid-manifest');
    writeTestFile($templatesDirectory . '/invalid-manifest/template.json', '{"parent":');
    assertThrowsException(
        TemplateManifestInvalidException::class,
        static fn () => $repository->get(new TemplateId('invalid-manifest')),
        'Ein syntaktisch ungültiges vorhandenes Manifest muss abgelehnt werden.'
    );

    mkdir($templatesDirectory . '/missing-parent');
    writeTestFile($templatesDirectory . '/missing-parent/template.json', '{"parent":"does-not-exist"}');
    assertThrowsException(
        ParentTemplateNotFoundException::class,
        static fn () => (new TemplateChainResolver($repository))->resolve(new TemplateId('missing-parent')),
        'Ein deklarierter, aber nicht vorhandener Parent muss abgelehnt werden.'
    );

    mkdir($templatesDirectory . '/cycle-a');
    mkdir($templatesDirectory . '/cycle-b');
    writeTestFile($templatesDirectory . '/cycle-a/template.json', '{"parent":"cycle-b"}');
    writeTestFile($templatesDirectory . '/cycle-b/template.json', '{"parent":"cycle-a"}');
    assertThrowsException(
        TemplateInheritanceCycleException::class,
        static fn () => (new TemplateChainResolver($repository))->resolve(new TemplateId('cycle-a')),
        'Ein Parent-Zyklus muss abgelehnt werden.'
    );

    mkdir($templatesDirectory . '/current');
    mkdir($templatesDirectory . '/parent-b');
    mkdir($templatesDirectory . '/parent-a');
    writeTestFile(
        $templatesDirectory . '/current/template.json',
        '{"parent":"parent-b","ui":{"engine":"future-engine"},"unknown":{"preserved":true}}'
    );
    writeTestFile($templatesDirectory . '/parent-b/template.json', '{"parent":"parent-a"}');
    writeTestFile($templatesDirectory . '/parent-a/template.json', '{}');
    writeTestFile($templatesDirectory . '/current/index.html', 'current');
    writeTestFile($templatesDirectory . '/parent-a/inherited.html', 'inherited');
    writeTestFile($templatesDirectory . '/parent-a/continuation.html', 'parent-a');
    writeTestFile($templatesDirectory . '/current/continuation.html', 'current');
    writeTestFile($templatesDirectory . '/parent-a/img/logo.png', 'image');
    writeTestFile($templatesDirectory . '/parent-a/img/stars_5.png', 'stars');
    writeTestFile($templatesDirectory . '/parent-a/stylesheet.css', 'css');
    writeTestFile($templatesDirectory . '/parent-a/stylesheet.min.css', 'minified-css');
    writeTestFile(
        $templatesDirectory . '/current/template-asset.html',
        '{template_asset path=\'img/logo.png\'}|{template_asset path="img/stars_`$rating`.png"}|{if $smarty.const.COMPRESS_STYLESHEET == \'true\'}{template_asset path=\'stylesheet.min.css\'}{else}{template_asset path=\'stylesheet.css\'}{/if}'
    );
    writeTestFile($templatesDirectory . '/parent-a/source/boxes/categories.php', 'categories');
    writeTestFile(
        $templatesDirectory . '/current/layout.html',
        '{extends file="parent:layout.html"}{block name="body"}child-{$smarty.block.parent}{/block}'
    );
    writeTestFile($templatesDirectory . '/parent-a/layout.html', '{block name="body"}base{/block}');
    writeTestFile($temporaryDirectory . '/outside.html', 'outside');
    symlink($temporaryDirectory . '/outside.html', $templatesDirectory . '/current/outside.html');

    $manifest = $repository->get(new TemplateId('current'));
    assertSameValue(
        ['preserved' => true],
        $manifest->section('unknown'),
        'Unbekannte Manifestabschnitte müssen unverändert erhalten bleiben.'
    );
    assertSameValue(
        ['engine' => 'future-engine'],
        $manifest->section('ui'),
        'PR1 muss den UI-Abschnitt erhalten, ohne ihn zu interpretieren.'
    );

    $chain = (new TemplateChainResolver($repository))->resolve(new TemplateId('current'));
    assertSameValue(
        ['current', 'parent-b', 'parent-a'],
        $chain->names(),
        'Die mehrstufige Template-Kette muss vom Child zum ältesten Parent verlaufen.'
    );

    $resolver = new TemplateFileResolver($chain, $repository);
    assertSameValue(
        'current',
        $resolver->resolve('index.html')->sourceTemplate()->value(),
        'Die erste vorhandene Template-Datei muss gewinnen.'
    );
    assertSameValue(
        'parent-a',
        $resolver->resolve('inherited.html')->sourceTemplate()->value(),
        'Eine Datei muss über mehrere fehlende Zwischenvarianten hinweg geerbt werden.'
    );
    assertSameValue(null, $resolver->find('optional.html'), 'Ein optionaler Miss muss null liefern.');
    assertThrowsException(
        TemplateFileNotFoundException::class,
        static fn () => $resolver->resolve('required.html'),
        'Ein strikter Miss muss eine spezifische Ausnahme auslösen.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->resolve('../outside.php'),
        'Pfadtraversierung muss abgelehnt werden.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->resolve('outside.html'),
        'Ein Symlink darf die Auflösung nicht aus dem Template-Verzeichnis herausführen.'
    );

    $continued = $resolver->resolveAfter(
        'continuation.html',
        $templatesDirectory . '/current/continuation.html'
    );
    assertSameValue(
        'parent-a',
        $continued->sourceTemplate()->value(),
        'resolveAfter() muss einen fehlenden direkten Parent überspringen.'
    );

    $runtime = new TemplateRuntime(
        $chain,
        $resolver,
        new TemplateUrlGenerator('/base/', '/catalog/', 'https://shop.example/catalog/')
    );
    TemplateRuntime::install($runtime);

    assertSameValue(
        realpath($templatesDirectory) . '/parent-a/inherited.html',
        Template::path('inherited.html'),
        'Die Fassade muss einen verpflichtenden absoluten Pfad liefern.'
    );
    assertSameValue(
        realpath($templatesDirectory) . '/parent-a/source/boxes/',
        Template::path('source/boxes/'),
        'Ein angeforderter Verzeichnispfad muss seinen abschließenden Slash behalten.'
    );
    assertSameValue(
        realpath($templatesDirectory) . '/parent-a/source/boxes/categories.php',
        Template::path('source/boxes/') . 'categories.php',
        'Ein aufgelöster Verzeichnispfad muss mit einem Dateinamen verkettet werden können.'
    );
    assertSameValue(
        'parent-a/inherited.html',
        Template::resolve('inherited.html'),
        'Die Fassade muss eine durch Smarty verwendbare Referenz liefern.'
    );
    assertSameValue(
        '/base/templates/parent-a/img/logo.png',
        Template::url('img/logo.png'),
        'Relative URL und Dateisystempfad müssen getrennte Ergebnisarten bleiben.'
    );
    assertSameValue(
        '/catalog/templates/parent-a/img/logo.png',
        Template::catalogUrl('img/logo.png'),
        'Die Katalog-URL muss ihre eigene Basis verwenden.'
    );
    assertSameValue(
        'https://shop.example/catalog/templates/parent-a/img/logo.png',
        Template::absoluteUrl('img/logo.png'),
        'Die absolute URL muss Schema und Host enthalten.'
    );
    assertSameValue(
        ['current', 'parent-b', 'parent-a'],
        Template::chain(),
        'Die Fassade muss die wirksame Template-Kette bereitstellen.'
    );

    define('DIR_FS_CATALOG', $temporaryDirectory . '/');
    define('DIR_FS_EXTERNAL', dirname(__DIR__, 4) . '/includes/external/');
    define('CURRENT_TEMPLATE', 'current');
    define('COMPRESS_STYLESHEET', 'true');
    define('RUN_MODE_INSTALLER', true);
    require dirname(__DIR__, 4) . '/includes/external/smarty/smarty_4/Smarty.class.php';

    $smarty = new Smarty();
    $smarty->assign('rating', 5);
    assertSameValue(
        '/base/templates/parent-a/img/logo.png|/base/templates/parent-a/img/stars_5.png|/base/templates/parent-a/stylesheet.min.css',
        $smarty->fetch(Template::resolve('template-asset.html')),
        'template_asset muss statische, dynamische und bedingte Assetpfade über die Template-Kette auflösen.'
    );
    assertSameValue(
        'child-base',
        $smarty->fetch(Template::resolve('layout.html')),
        'Die parent:-Resource muss einen fehlenden direkten Parent überspringen und an den Kern delegieren.'
    );

    TemplateRuntime::reset();
    assertSameValue(
        false,
        TemplateRuntime::get() === $runtime,
        'Nach reset() darf die zuvor installierte Runtime nicht weiterverwendet werden.'
    );

    echo sprintf("Template-Vererbung: %d Assertions erfolgreich.\n", $assertions);
} finally {
    TemplateRuntime::reset();
    removeTestDirectory($temporaryDirectory);
}
