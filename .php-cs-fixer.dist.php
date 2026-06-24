<?php

/**
 * PHP-CS-Fixer Konfiguration für modified eCommerce Shopsoftware
 *
 * Diese Konfiguration wird verwendet für:
 * - Lokale Entwicklung (VS Code, CLI)
 * - GitHub Actions
 *
 * Ziel:
 * - Konsistente Code-Formatierung
 * - Schrittweise Modernisierung des Legacy-Codes
 * - Vermeidung unnötig großer Diffs
 *
 * Die Regeln sind in fünf Stufen unterteilt:
 *
 * STUFE 1 (AKTIV)
 * - Sichere Formatierungsregeln
 * - PSR-12
 * - Einrückungen, Leerzeichen und Leerzeilen
 * - Praktisch risikofrei
 *
 * STUFE 2 (OPTIONAL)
 * - Import-Regeln
 * - Import-Reihenfolge vereinheitlichen
 * - Nicht verwendete Imports entfernen
 * - Sehr geringes Risiko
 *
 * STUFE 3 (OPTIONAL)
 * - PHP-Migrationsregeln
 * - Modernere PHP-Schreibweisen
 * - Kann größere Diffs erzeugen
 *
 * STUFE 4 (OPTIONAL)
 * - Code-Struktur modernisieren
 * - Sichtbarkeiten erzwingen
 * - Klassenelemente vereinheitlichen
 * - Kann viele Legacy-Dateien ändern
 *
 * STUFE 5 (OPTIONAL)
 * - Typisierung modernisieren
 * - Rückgabetypen
 * - Strict Types
 * - Höchste Eingriffstiefe
 *
 * Empfehlung:
 * Zunächst nur Stufe 1 aktivieren und erst nach erfolgreicher
 * Einführung schrittweise weitere Stufen freischalten.
 */

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'includes/external/GuzzleHttp',
        'includes/external/magnalister',
        'includes/external/nusoap',
        'includes/external/Phpfastcache',
        'includes/external/phpmailer',
        'includes/external/Psr',
        'includes/external/smarty',
    ]);

return (new PhpCsFixer\Config())

    // Riskante Regeln deaktivieren.
    // Diese Regeln können potentiell das Verhalten des Codes verändern.
    ->setRiskyAllowed(false)

    // Cache aktivieren.
    // Beschleunigt Folgeausführungen erheblich.
    ->setUsingCache(true)

    // Cache-Datei festlegen.
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')

    // Erlaubt die Ausführung auch mit zukünftigen PHP-Versionen,
    // die von der aktuellen PHP-CS-Fixer-Version noch nicht offiziell
    // freigegeben wurden.
    ->setUnsupportedPhpVersionAllowed(true)

    ->setRules([
        // -----------------------------------------------------------------
        // STUFE 1 (AKTIV)
        // Locker:
        // - PSR-12 Formatierung
        // - Einrückungen
        // - Leerzeichen
        // - Leerzeilen
        //
        // Diese Regeln sind praktisch risikofrei und sollten
        // dauerhaft aktiviert bleiben.
        // -----------------------------------------------------------------

        // Offizieller PSR-12 Coding Standard.
        '@PSR12' => true,

        // Arrays sauber einrücken.
        'array_indentation' => true,

        // Leerzeile nach <?php einfügen.
        'blank_line_after_opening_tag' => true,

        // Einrückungen vereinheitlichen.
        'indentation_type' => true,

        // Keywords wie if, else, return klein schreiben.
        'lowercase_keywords' => true,

        // Mehrzeilige Parameterlisten vollständig umbrechen.
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        // Leerzeichen um String-Verkettungen vereinheitlichen.
        'concat_space' => [
            'spacing' => 'one',
        ],

        // Überflüssige Leerzeilen entfernen.
        // Die verwendeten Tokens sind bewusst explizit definiert,
        // damit zukünftige PHP-CS-Fixer-Versionen das Verhalten
        // nicht unbemerkt verändern.
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
            ],
        ],

        // -----------------------------------------------------------------
        // STUFE 2 (OPTIONAL)
        // Import-Regeln:
        // - Imports aufräumen
        // - Import-Reihenfolge vereinheitlichen
        // - Nicht verwendete Imports entfernen
        //
        // Sehr geringes Risiko.
        // Empfehlenswert, sobald Stufe 1 sauber läuft.
        // -----------------------------------------------------------------

        // use-Statements alphabetisch sortieren.
        //'ordered_imports' => true,

        // Leerzeilen zwischen unterschiedlichen Importgruppen.
        //'blank_line_between_import_groups' => true,

        // Pro use-Statement nur ein Import.
        //'single_import_per_statement' => true,

        // Nicht verwendete use-Statements entfernen.
        //'no_unused_imports' => true,

        // -----------------------------------------------------------------
        // STUFE 3 (OPTIONAL)
        // PHP-Migrationsregeln:
        // - PHP-8.2-Migrationsregeln
        // - Modernere PHP-Schreibweisen
        // - Konsistenzverbesserungen
        //
        // Diese Regeln modernisieren bestimmte Sprachkonstrukte,
        // ohne bewusst die eigentliche Programm-Logik zu verändern.
        //
        // Kann jedoch größere Diffs erzeugen.
        // Erst aktivieren, wenn Stufe 1 und 2 stabil laufen.
        // -----------------------------------------------------------------

        // PHP-8.2-Migrationsregeln aktivieren.
        //'@PHP8x2Migration' => true,

        // -----------------------------------------------------------------
        // STUFE 4 (OPTIONAL)
        // Code-Struktur:
        // - Sichtbarkeiten erzwingen
        // - Klassenelemente vereinheitlichen
        //
        // Diese Regeln können bei Legacy-Code viele Dateien ändern.
        // Nur nach bewusster Entscheidung aktivieren.
        // -----------------------------------------------------------------

        // public/protected/private erzwingen.
        //'visibility_required' => [
        //    'elements' => [
        //        'property',
        //        'method',
        //    ],
        //],

        // Mehrere Klassenelemente in einer Zeile verbieten.
        //'single_class_element_per_statement' => true,

        // -----------------------------------------------------------------
        // STUFE 5 (OPTIONAL)
        // Typisierung:
        // - Rückgabetypen
        // - Strict Types
        // - Nullable-Typen
        //
        // Höchste Eingriffstiefe.
        // Für Legacy-Projekte oft erst im Rahmen größerer Modernisierung
        // sinnvoll.
        // -----------------------------------------------------------------

        // Rückgabetypen vereinheitlichen.
        //'return_type_declaration' => true,

        // declare(strict_types=1) erzwingen.
        //'declare_strict_types' => true,

        // Leerzeichen in declare()-Anweisungen vereinheitlichen.
        //'declare_equal_normalize' => true,

        // Nullable-Typen modernisieren.
        //'compact_nullable_type_declaration' => true,
    ])
    ->setFinder($finder);
