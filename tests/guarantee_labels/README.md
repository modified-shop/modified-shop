# Tests der EU-Haltbarkeitsgarantie

Die Tests des GARAN-Moduls aus Issue #3050. Sie laufen ohne Datenbank, ohne Webserver und ohne
installierten Shop.

## Ausführen

Die Tests liegen auf einem eigenen Branch, siehe `tests/README.md`. Sie erwarten eine Arbeitskopie
des Shops. Liegen sie darin unter `tests/`, findet der Bootstrap ihn von allein; sonst nennt
`GARAN_SHOP_ROOT` den Pfad.

```sh
tests/guarantee_labels/run.sh              # alle Tests
tests/guarantee_labels/run.sh mail render  # nur die Tests, deren Name das enthält
PHP=/pfad/zu/php tests/guarantee_labels/run.sh
```

Der Runner endet mit `0`, wenn alles durchläuft, sonst mit `1`. Bei einem Fehlschlag nennt er die
betroffene Datei und die fehlgeschlagenen Zeilen.

## Voraussetzungen

PHP auf dem Pfad, mit `gd` einschließlich FreeType (`imagettfbbox()`) und `mbstring`. Ohne
FreeType kann der Renderer keine Textbreite messen und die Tests dazu schlagen fehl.

## Wie sie arbeiten

`bootstrap.php` legt unter dem Temp-Verzeichnis einen kleinen Shopbaum an: Symlinks auf `inc/`,
`includes/` und `lang/` des Repositorys, dazu beschreibbare `cache/`, `log/` und `media/`. Der
Arbeitsbaum wird vom Runner vor jedem Lauf verworfen. In der Arbeitskopie des Shops entsteht
nichts.

Die Datenbank ersetzt jeder Test durch eigene Stubs für `xtc_db_query()` und Verwandte. Es wird
also nicht geprüft, ob eine Abfrage zur Tabelle passt, sondern was der Code aus den gelieferten
Zeilen macht.

Die Labelvorlagen unter `fixtures/assets/` sind Attrappen mit denselben Platzhaltern wie die
offiziellen Dateien, aber wenige hundert Bytes groß. Dadurch laufen die Rendertests schnell und
unabhängig von den lizenzierten Originalen. `fixtures/other_extension.php` ist eine fremde
Klassenerweiterung; mit ihr zeigen die Tests, dass Installation und Entfernung des Moduls andere
Erweiterungen eines Shops unberührt lassen.

## Aufbau eines Tests

Jede Datei ist ein eigenständiges PHP-Skript mit derselben Ausgabe:

```
  ok    was geprüft wurde
  FAIL  was fehlschlug  (Zusatzinfo)

bestanden: 12   fehlgeschlagen: 0
```

Tests mit mehreren Ausgangslagen rufen sich selbst als Unterprozess auf, weil PHP-Konstanten und
statische Puffer einen Prozess lang leben. Der erste Aufruf ohne Argument ist dann der Treiber.

Einen Test dazuschreiben heißt: Datei `<name>_test.php` anlegen, `bootstrap.php` einbinden,
dieselbe Ausgabe erzeugen und mit dem passenden Code beenden. Der Runner findet sie von allein.

## Was abgedeckt ist

| Datei | Prüft |
| --- | --- |
| `archive_test` | Archiv und Cache, Prüfsummen, beschädigte Verzeichnisse |
| `charset_test` | Shops auf utf8 und auf latin1 erzeugen dasselbe Label |
| `context_test` | Zusammenspiel im Storefront-Kontext |
| `diagnosis_test` | Moduldiagnose in drei Ausgangslagen |
| `downloads_update_test` | Aufräumen der Downloadzeilen einer Bestellposition |
| `extension_test` | Installation, Entfernung und Registrierung der Klassenerweiterungen |
| `import_test` | CSV-Import der Garantiedauer |
| `label_texts_test` | Label ohne Modultexte, beschädigter Cache |
| `listing_test` | Ausgabe in Artikellisten |
| `mail_test`, `mail_admin_test` | Auftragsbestätigung, Anhänge, Maskierung |
| `order_test`, `order_label_admin_test` | Bestellansichten |
| `order_language_test` | Bestellsprache gegen Sitzungssprache |
| `output_test` | Storefront-Ausgabe und Gewährleistungshinweis |
| `position_type_test` | körperlich oder digital je gewählter Variante |
| `render_test` | Rendern, Maskierung, Breitenprüfung |
| `save_test` | Speicherlauf der Modulverwaltung |
| `snapshot_test` und Verwandte | Bestellsnapshots schreiben, ändern, löschen |
| `terms_check_test` | Zuordnung der Garantiebedingungen |
| `validate_test` | Validierung der GARAN-Artikeldaten |
| `virtual_test` | virtuelle Artikel erhalten kein Label |

## Sicherheit des Arbeitsverzeichnisses

Jeder Lauf bekommt ein eigenes Arbeitsverzeichnis. Der Runner legt es mit `mktemp -d` an, setzt
darin die Markerdatei `.garan-tests` und entfernt am Ende genau dieses eine wieder — auch nach
einem Abbruch mit Strg-C, der den Lauf dann wirklich beendet. Zwei Läufe gleichzeitig stören sich
deshalb nicht. Ein einzeln aufgerufener Test legt sich selbst eines an und räumt es beim Beenden
weg; die Prozesse, die er startet, erben es über `GARAN_TEST_WORK_DIR` und lassen es stehen.

Ein über `GARAN_TEST_WORK_DIR` übergebener Pfad wird nur verwendet, wenn er unmittelbar im
geprüften Basisverzeichnis liegt, mit `garan-tests-` beginnt, ein echtes Verzeichnis ist und die
Markerdatei enthält. In den Tests entstehen und verschwinden Verzeichnisse, deshalb darf dort nie
ein beliebiger Pfad ankommen.

Aus der Umgebung kommt nur das Basisverzeichnis:

```sh
GARAN_TEST_BASE=/pfad/zum/scratch tests/guarantee_labels/run.sh
```

Ein leerer Wert zählt wie eine nicht gesetzte Variable. Ein Basisverzeichnis, das nicht
existiert, nicht beschreibbar ist oder auf `/`, das Home-Verzeichnis oder das Repository zeigt,
wird abgelehnt — im Runner wie im Bootstrap, mit
derselben Meldung. Entfernt wird nur ein Pfad, dessen letzter Bestandteil mit `garan-tests-`
beginnt und der kein Symlink ist.

Ein Arbeitsverzeichnis, das selbst ein Symlink ist, wird abgelehnt, damit kein Schreibvorgang in
ein fremdes Ziel läuft.
