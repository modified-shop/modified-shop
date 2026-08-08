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
$readOnlyTemplateDirectory = null;

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

function xtc_href_link(string $page = ''): string
{
    return $page;
}

function encode_htmlspecialchars(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    writeTestFile(
        $templatesDirectory . '/parent-a/smarty/register_php_plugins.php',
        '<?php $register_php_plugins = array("defined");'
    );
    writeTestFile(
        $templatesDirectory . '/current/smarty/register_php_plugins.php',
        '<?php $register_php_plugins = array("sprintf");'
    );
    writeTestFile(
        $templatesDirectory . '/parent-a/inherited-php-plugins.html',
        '{if "MODIFIED_TEMPLATE_PLUGIN_INHERITANCE"|defined}{"%s"|sprintf:"registered"}{/if}'
    );
    writeTestFile($templatesDirectory . '/parent-a/continuation.html', 'parent-a');
    writeTestFile($templatesDirectory . '/current/continuation.html', 'current');
    mkdir($templatesDirectory . '/current/img');
    writeTestFile($templatesDirectory . '/current/img/current.png', 'current-image');
    $logoPath = $templatesDirectory . '/parent-a/img/logo.png';
    writeTestFile($logoPath, 'image');
    if (!touch($logoPath, 1700000000)) {
        throw new RuntimeException('Die feste Änderungszeit für das Test-Asset konnte nicht gesetzt werden.');
    }
    clearstatcache(true, $logoPath);
    writeTestFile($templatesDirectory . '/parent-a/img/stars_5.png', 'stars');
    writeTestFile($templatesDirectory . '/parent-a/stylesheet.css', 'css');
    writeTestFile($templatesDirectory . '/parent-a/stylesheet.min.css', 'minified-css');
    writeTestFile($templatesDirectory . '/parent-a/buttons/german/button_parent.gif', 'button');
    writeTestFile(
        $templatesDirectory . '/current/template-asset.html',
        '{template_asset path=\'img/current.png\'}|{template_asset path=\'img/logo.png\'}|{template_asset path=\'img/logo.png\' versioned=true}|{template_asset path=\'img/logo.png\' absolute=true versioned=true}|{template_asset path="img/stars_`$rating`.png"}|{if $smarty.const.COMPRESS_STYLESHEET == \'true\'}{template_asset path=\'stylesheet.min.css\'}{else}{template_asset path=\'stylesheet.css\'}{/if}'
    );
    writeTestFile($templatesDirectory . '/parent-a/source/boxes/categories.php', 'categories');
    writeTestFile($templatesDirectory . '/current/module/product_info/product_10.html', 'current-10');
    writeTestFile($templatesDirectory . '/current/module/product_info/shared.html', 'current-shared');
    writeTestFile($templatesDirectory . '/current/module/product_info/ignored.php', 'ignored');
    writeTestFile($templatesDirectory . '/parent-b/module/product_info/product_2.html', 'parent-b-2');
    writeTestFile($templatesDirectory . '/parent-b/module/product_info/shared.html', 'parent-b-shared');
    writeTestFile($templatesDirectory . '/parent-a/module/product_info/product_1.html', 'parent-a-1');
    writeTestFile($templatesDirectory . '/parent-a/module/product_info/shared.html', 'parent-a-shared');
    writeTestFile($templatesDirectory . '/current/favicons/favicon.svg', 'current-favicon');
    writeTestFile($templatesDirectory . '/parent-a/favicons/favicon.svg', 'parent-favicon');
    writeTestFile($templatesDirectory . '/parent-a/favicons/favicon.ico', 'parent-icon');
    writeTestFile($templatesDirectory . '/parent-a/favicons/apple-touch-icon.png', 'parent-apple-icon');
    writeTestFile($templatesDirectory . '/parent-a/favicons/web-app-manifest-192x192.png', 'parent-web-app-icon');
    writeTestFile($templatesDirectory . '/current/favicons/web-app-manifest-192x192.png', 'current-web-app-icon');
    $siteManifestPath = $templatesDirectory . '/parent-a/favicons/site.webmanifest';
    writeTestFile($siteManifestPath, '{}');
    if (!touch($siteManifestPath, 1)) {
        throw new RuntimeException('Die feste Änderungszeit für das Testmanifest konnte nicht gesetzt werden.');
    }
    clearstatcache(true, $siteManifestPath);
    writeTestFile($templatesDirectory . '/parent-a/favicons/mstile-150x150.png', 'parent-mstile-icon');
    writeTestFile($templatesDirectory . '/current/favicons/mstile-150x150.png', 'current-mstile-icon');
    $browserconfigPath = $templatesDirectory . '/parent-a/favicons/browserconfig.xml';
    writeTestFile($browserconfigPath, '<parent-browserconfig/>');
    if (!touch($browserconfigPath, 1)) {
        throw new RuntimeException('Die feste Änderungszeit für die Test-Browserkonfiguration konnte nicht gesetzt werden.');
    }
    clearstatcache(true, $browserconfigPath);
    writeTestFile($templatesDirectory . '/parent-a/favicons/.ignored', 'hidden');
    writeTestFile($templatesDirectory . '/parent-a/favicons/nested/ignored.png', 'nested');
    mkdir($templatesDirectory . '/current/module/unsafe_listing', 0777, true);
    symlink(
        $temporaryDirectory . '/outside.html',
        $templatesDirectory . '/current/module/unsafe_listing/outside.html'
    );
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
    assertSameValue(
        [
            realpath($templatesDirectory) . '/parent-a/module/product_info/product_1.html',
            realpath($templatesDirectory) . '/parent-b/module/product_info/product_2.html',
            realpath($templatesDirectory) . '/current/module/product_info/product_10.html',
            realpath($templatesDirectory) . '/current/module/product_info/shared.html',
        ],
        array_map(
            static fn ($file): string => $file->absolutePath(),
            $resolver->findAll('module/product_info/', 'html')
        ),
        'Eine Dateiliste muss Parent-Dateien erben, Child-Varianten bevorzugen und natürlich sortieren.'
    );
    assertSameValue(
        [],
        $resolver->findAll('module/missing/', 'html'),
        'Ein nicht vorhandenes Verzeichnis muss eine leere Dateiliste liefern.'
    );
    assertSameValue(
        [
            realpath($templatesDirectory) . '/parent-a/favicons/apple-touch-icon.png',
            realpath($templatesDirectory) . '/parent-a/favicons/browserconfig.xml',
            realpath($templatesDirectory) . '/parent-a/favicons/favicon.ico',
            realpath($templatesDirectory) . '/current/favicons/favicon.svg',
            realpath($templatesDirectory) . '/current/favicons/mstile-150x150.png',
            realpath($templatesDirectory) . '/parent-a/favicons/site.webmanifest',
            realpath($templatesDirectory) . '/current/favicons/web-app-manifest-192x192.png',
        ],
        array_map(
            static fn ($file): string => $file->absolutePath(),
            $resolver->findAll('favicons/')
        ),
        'Eine Dateiliste ohne Endungsfilter muss gemischte Dateitypen erben und gleichnamige Parent-Dateien überschreiben.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->findAll('../outside/', 'html'),
        'Auch bei Dateilisten muss Pfadtraversierung abgelehnt werden.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->findAll('module/product_info/', '../html'),
        'Eine unsichere Dateiendung muss abgelehnt werden.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->findAll('module/product_info/', ''),
        'Eine leere Dateiendung muss trotz optionalem Filter ungültig bleiben.'
    );
    assertThrowsException(
        InvalidTemplatePathException::class,
        static fn () => $resolver->findAll('module/unsafe_listing/', 'html'),
        'Eine Dateiliste darf keinem Symlink aus dem Template-Verzeichnis heraus folgen.'
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
        [
            realpath($templatesDirectory) . '/parent-a/module/product_info/product_1.html',
            realpath($templatesDirectory) . '/parent-b/module/product_info/product_2.html',
            realpath($templatesDirectory) . '/current/module/product_info/product_10.html',
            realpath($templatesDirectory) . '/current/module/product_info/shared.html',
        ],
        Template::files('module/product_info/', 'html'),
        'Die Fassade muss die absoluten Pfade der wirksamen Dateiliste liefern.'
    );
    assertSameValue(
        [
            realpath($templatesDirectory) . '/parent-a/favicons/apple-touch-icon.png',
            realpath($templatesDirectory) . '/parent-a/favicons/browserconfig.xml',
            realpath($templatesDirectory) . '/parent-a/favicons/favicon.ico',
            realpath($templatesDirectory) . '/current/favicons/favicon.svg',
            realpath($templatesDirectory) . '/current/favicons/mstile-150x150.png',
            realpath($templatesDirectory) . '/parent-a/favicons/site.webmanifest',
            realpath($templatesDirectory) . '/current/favicons/web-app-manifest-192x192.png',
        ],
        Template::files('favicons/'),
        'Die Fassade muss Dateilisten ohne Endungsfilter bereitstellen.'
    );
    assertSameValue(
        '/base/templates/parent-a/img/logo.png',
        Template::url('img/logo.png'),
        'Relative URL und Dateisystempfad müssen getrennte Ergebnisarten bleiben.'
    );
    assertSameValue(
        '/base/templates/current/',
        Template::url(''),
        'Die Template-Wurzel-URL muss immer auf das aktive Child zeigen.'
    );
    assertSameValue(
        '/base/templates/parent-a/img/logo.png?v=1700000000',
        Template::url('img/logo.png', true),
        'Die relative URL muss die Version der tatsächlich aufgelösten Datei verwenden.'
    );
    assertSameValue(
        '/catalog/templates/parent-a/img/logo.png',
        Template::catalogUrl('img/logo.png'),
        'Die Katalog-URL muss ihre eigene Basis verwenden.'
    );
    assertSameValue(
        '/catalog/templates/parent-a/img/logo.png?v=1700000000',
        Template::catalogUrl('img/logo.png', true),
        'Die versionierte Katalog-URL muss ihre eigene Basis verwenden.'
    );
    assertSameValue(
        'https://shop.example/catalog/templates/parent-a/img/logo.png',
        Template::absoluteUrl('img/logo.png'),
        'Die absolute URL muss Schema und Host enthalten.'
    );
    assertSameValue(
        'https://shop.example/catalog/templates/parent-a/img/logo.png?v=1700000000',
        Template::absoluteUrl('img/logo.png', true),
        'Die versionierte absolute URL muss Schema, Host und Dateiversion enthalten.'
    );
    assertSameValue(
        ['current', 'parent-b', 'parent-a'],
        Template::chain(),
        'Die Fassade muss die wirksame Template-Kette bereitstellen.'
    );

    define('DIR_FS_CATALOG', $temporaryDirectory . '/');
    define('DIR_FS_EXTERNAL', dirname(__DIR__, 4) . '/includes/external/');
    define('DIR_FS_INC', dirname(__DIR__, 4) . '/inc/');
    define('DIR_WS_BASE', '/base/');
    define('DIR_WS_IMAGES', 'images/');
    define('DIR_WS_THUMBNAIL_IMAGES', 'images/product_images/thumbnail_images/');
    define('CURRENT_TEMPLATE', 'current');
    define('COMPRESS_STYLESHEET', 'true');
    define('TITLE', 'Test shop');
    define('RUN_MODE_INSTALLER', true);
    $request_type = 'NONSSL';
    ob_start();
    require dirname(__DIR__, 4) . '/includes/modules/favicons.php';
    $faviconMarkup = ob_get_clean();
    assertSameValue(
        1,
        substr_count($faviconMarkup, 'templates/current/favicons/favicon.svg'),
        'Das Child-Favicon muss genau einmal ausgegeben werden.'
    );
    assertSameValue(
        0,
        substr_count($faviconMarkup, 'templates/parent-a/favicons/favicon.svg'),
        'Die gleichnamige Parent-Variante des Child-Favicons darf nicht ausgegeben werden.'
    );
    assertSameValue(
        1,
        substr_count($faviconMarkup, 'templates/parent-a/favicons/favicon.ico'),
        'Ein ausschließlich im Parent vorhandenes Favicon muss ausgegeben werden.'
    );
    assertSameValue(
        1,
        substr_count($faviconMarkup, 'templates/parent-a/favicons/apple-touch-icon.png'),
        'Ein ausschließlich im Parent vorhandenes Apple-Touch-Icon muss ausgegeben werden.'
    );
    assertSameValue(
        '{}',
        file_get_contents($siteManifestPath),
        'Das geerbte Parent-Manifest darf durch das aktive Child nicht verändert werden.'
    );
    $childManifestPath = $templatesDirectory . '/current/favicons/site.webmanifest';
    $generatedManifest = json_decode((string) file_get_contents($childManifestPath), true);
    assertSameValue(
        'templates/current/favicons/web-app-manifest-192x192.png',
        $generatedManifest['icons'][0]['src'] ?? null,
        'Das Child-Web-App-Icon muss im Child-eigenen Manifest ausgegeben werden.'
    );
    assertSameValue(
        1,
        substr_count($faviconMarkup, 'templates/current/favicons/site.webmanifest'),
        'Das generierte Child-Manifest muss verlinkt werden.'
    );
    assertSameValue(
        '<parent-browserconfig/>',
        file_get_contents($browserconfigPath),
        'Die geerbte Parent-Browserkonfiguration darf durch das aktive Child nicht verändert werden.'
    );
    $childBrowserconfigPath = $templatesDirectory . '/current/favicons/browserconfig.xml';
    assertSameValue(
        true,
        str_contains((string) file_get_contents($childBrowserconfigPath), 'templates/current/favicons/mstile-150x150.png'),
        'Die Child-eigene Browserkonfiguration muss auf das Child-Mstile verweisen.'
    );
    assertSameValue(
        1,
        substr_count($faviconMarkup, 'templates/current/favicons/browserconfig.xml'),
        'Die generierte Child-Browserkonfiguration muss verlinkt werden.'
    );

    $thinParentDirectory = $templatesDirectory . '/thin-parent';
    $readOnlyTemplateDirectory = $templatesDirectory . '/thin-child';
    mkdir($thinParentDirectory);
    mkdir($readOnlyTemplateDirectory);
    writeTestFile($thinParentDirectory . '/template.json', '{}');
    writeTestFile($readOnlyTemplateDirectory . '/template.json', '{"parent":"thin-parent"}');
    writeTestFile($thinParentDirectory . '/favicons/web-app-manifest-192x192.png', 'parent-web-app-icon');
    $thinParentManifestPath = $thinParentDirectory . '/favicons/site.webmanifest';
    writeTestFile($thinParentManifestPath, '{"parent":true}');
    writeTestFile($thinParentDirectory . '/favicons/mstile-150x150.png', 'parent-mstile-icon');
    $thinParentBrowserconfigPath = $thinParentDirectory . '/favicons/browserconfig.xml';
    writeTestFile($thinParentBrowserconfigPath, '<parent-browserconfig/>');

    $thinRepository = new TemplateManifestRepository($templatesDirectory);
    $thinChain = (new TemplateChainResolver($thinRepository))->resolve(new TemplateId('thin-child'));
    $thinRuntime = new TemplateRuntime(
        $thinChain,
        new TemplateFileResolver($thinChain, $thinRepository),
        new TemplateUrlGenerator('/base/', '/catalog/', 'https://shop.example/catalog/')
    );
    TemplateRuntime::install($thinRuntime);

    if (!chmod($readOnlyTemplateDirectory, 0555)) {
        throw new RuntimeException('Das Thin-Child-Testtemplate konnte nicht schreibgeschützt werden.');
    }
    clearstatcache(true, $readOnlyTemplateDirectory);
    assertSameValue(
        false,
        is_writable($readOnlyTemplateDirectory),
        'Das Thin-Child-Testtemplate muss für den Regressionstest schreibgeschützt sein.'
    );

    $faviconWarnings = [];
    set_error_handler(static function (int $severity, string $message) use (&$faviconWarnings): bool {
        if (($severity & error_reporting()) !== 0) {
            $faviconWarnings[] = $message;
        }

        return true;
    });
    try {
        $thinFaviconMarkup = (new FaviconRenderer(
            $temporaryDirectory,
            'Test shop',
            static fn (string $path): string => $path
        ))->render();
    } finally {
        restore_error_handler();
        chmod($readOnlyTemplateDirectory, 0755);
        TemplateRuntime::install($runtime);
    }

    assertSameValue([], $faviconWarnings, 'Ein nicht beschreibbares Thin Child darf keine PHP-Warning ausgeben.');
    assertSameValue(
        1,
        substr_count($thinFaviconMarkup, 'templates/thin-parent/favicons/site.webmanifest'),
        'Ein Thin Child ohne eigene Web-App-Icons muss auf das geerbte Parent-Manifest zurückfallen.'
    );
    assertSameValue(
        1,
        substr_count($thinFaviconMarkup, 'templates/thin-parent/favicons/browserconfig.xml'),
        'Ein Thin Child ohne eigene Mstile-Dateien muss auf die geerbte Parent-Browserkonfiguration zurückfallen.'
    );
    assertSameValue(
        false,
        is_dir($readOnlyTemplateDirectory . '/favicons'),
        'Der fehlgeschlagene Schreibversuch darf kein unvollständiges Child-Favicon-Verzeichnis hinterlassen.'
    );
    assertSameValue(
        '{"parent":true}',
        file_get_contents($thinParentManifestPath),
        'Der Read-only-Fallback darf das geerbte Parent-Manifest nicht verändern.'
    );
    assertSameValue(
        '<parent-browserconfig/>',
        file_get_contents($thinParentBrowserconfigPath),
        'Der Read-only-Fallback darf die geerbte Parent-Browserkonfiguration nicht verändern.'
    );

    $thinChildFaviconDirectory = $readOnlyTemplateDirectory . '/favicons';
    writeTestFile($thinChildFaviconDirectory . '/web-app-manifest-192x192.png', 'child-web-app-icon');
    if (!chmod($thinChildFaviconDirectory, 0555)) {
        throw new RuntimeException('Das Child-Favicon-Testverzeichnis konnte nicht schreibgeschützt werden.');
    }
    clearstatcache(true, $thinChildFaviconDirectory);
    TemplateRuntime::install($thinRuntime);
    try {
        $childSpecificFaviconMarkup = (new FaviconRenderer(
            $temporaryDirectory,
            'Test shop',
            static fn (string $path): string => $path
        ))->render();
    } finally {
        chmod($thinChildFaviconDirectory, 0755);
        TemplateRuntime::install($runtime);
    }
    assertSameValue(
        0,
        substr_count($childSpecificFaviconMarkup, 'rel="manifest"'),
        'Ein Child mit eigenem Web-App-Icon darf bei fehlgeschlagener Generierung nicht auf das Parent-Manifest zurückfallen.'
    );

    require dirname(__DIR__, 4) . '/inc/xtc_image.inc.php';
    require dirname(__DIR__, 4) . '/inc/xtc_image_button.inc.php';
    require dirname(__DIR__, 4) . '/inc/xtc_image_submit.inc.php';
    require dirname(__DIR__, 4) . '/includes/external/smarty/smarty_4/Smarty.class.php';

    $_SESSION['language'] = 'german';
    assertSameValue(
        '<img src="/base/templates/parent-a/buttons/german/button_parent.gif" alt="Parent button" />',
        xtc_image_button('button_parent.gif', 'Parent button', '', false),
        'xtc_image_button muss einen ausschließlich im Parent vorhandenen Button ohne doppelte URL-Basis ausgeben.'
    );
    assertSameValue(
        '<input type="image" src="/base/templates/parent-a/buttons/german/button_parent.gif" alt="Parent submit" title="Parent submit" />',
        xtc_image_submit('button_parent.gif', 'Parent submit', '', false),
        'xtc_image_submit muss einen ausschließlich im Parent vorhandenen Button auflösen.'
    );

    $smarty = new Smarty();
    assertSameValue(
        true,
        isset($smarty->registered_plugins['modifier']['defined']),
        'Smarty muss PHP-Plugins aus einem Parent-Template registrieren.'
    );
    assertSameValue(
        true,
        isset($smarty->registered_plugins['modifier']['sprintf']),
        'Smarty muss die PHP-Plugin-Liste des Child-Templates ergänzend registrieren.'
    );
    define('MODIFIED_TEMPLATE_PLUGIN_INHERITANCE', true);
    set_error_handler(static function (int $severity, string $message): bool {
        if ($severity === E_USER_DEPRECATED && strpos($message, 'Using unregistered function') !== false) {
            throw new ErrorException($message, 0, $severity);
        }

        return false;
    });
    try {
        $inheritedPhpPluginsOutput = $smarty->fetch(Template::resolve('inherited-php-plugins.html'));
    } finally {
        restore_error_handler();
    }
    assertSameValue(
        'registered',
        $inheritedPhpPluginsOutput,
        'Ein geerbtes Template muss PHP-Plugins aus Parent und Child ohne Warnung verwenden können.'
    );
    $smarty->assign('rating', 5);
    assertSameValue(
        '/base/templates/current/img/current.png|/base/templates/parent-a/img/logo.png|/base/templates/parent-a/img/logo.png?v=1700000000|https://shop.example/catalog/templates/parent-a/img/logo.png?v=1700000000|/base/templates/parent-a/img/stars_5.png|/base/templates/parent-a/stylesheet.min.css',
        $smarty->fetch(Template::resolve('template-asset.html')),
        'template_asset muss statische, dynamische, absolute, versionierte und bedingte Assetpfade über die Template-Kette auflösen.'
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
    if ($readOnlyTemplateDirectory !== null && is_dir($readOnlyTemplateDirectory)) {
        chmod($readOnlyTemplateDirectory, 0755);
    }
    removeTestDirectory($temporaryDirectory);
}
