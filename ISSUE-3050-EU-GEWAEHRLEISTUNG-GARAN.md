# Planung zu Issue #3050: EU-Gewaehrleistungs- und GARAN-Label

## Ziel

modified eCommerce Shopsoftware soll die ab 27. September 2026 geltenden Informationspflichten fuer die gesetzliche Gewaehrleistung und die gewerbliche Haltbarkeitsgarantie unterstuetzen.

Die Erweiterung behandelt zwei getrennte Kennzeichnungen:

1. Die harmonisierte Mitteilung zum gesetzlichen Gewaehrleistungsrecht.
2. Das produktspezifische GARAN-Label fuer bestimmte Haltbarkeitsgarantien des Herstellers.

## Quellen

- Issue #3050: <https://github.com/modified-shop/modified-shop/issues/3050>
- EU-Durchfuehrungsverordnung 2025/1960: <https://eur-lex.europa.eu/legal-content/DE/TXT/?uri=CELEX:32025R1960>
- Offizielle EU-Grafiken und Praxisleitfaden: <https://commission.europa.eu/publications/practical-guidelines-and-high-resolution-vector-files-eu-notice-and-label-product-guarantees_en>
- Deutsches Umsetzungsgesetz, BGBl. 2026 I Nr. 28: <https://www.recht.bund.de/bgbl/1/2026/28/VO.html>
- § 479 BGB zu Inhalt und Bereitstellung der Garantieerklaerung: <https://www.gesetze-im-internet.de/bgb/__479.html>
- IT-Recht Kanzlei, Ueberblick: <https://www.it-recht-kanzlei.de/neue-label-gewaehrleistung-garantie-2026.html>
- IT-Recht Kanzlei, FAQ: <https://www.it-recht-kanzlei.de/faq-gewaehrleistunglabel-garantielabel.html>
- Haendlerbund zur Darstellung des Gewaehrleistungshinweises: <https://ohn.haendlerbund.de/recht/rechtsfragen/gewaehrleistungslabel-shop-dargestellt>
- Haendlerbund zum GARAN-Label: <https://ohn.haendlerbund.de/recht/rechtsfragen/garantielabel-ab-september-unterschaetzte-aufwand-shops>

## Rechtlicher Rahmen

### Beginn

Die Vorgaben gelten ab 27. September 2026.

### Gewaehrleistungshinweis

Der Gewaehrleistungshinweis gilt im B2C-Verkauf fuer koerperliche Waren.

Eigenschaften:

- Statische EU-Grafik.
- Nicht produkt- oder haendlerabhaengig.
- Sprachabhaengig.
- Im Onlinehandel muss die farbige RGB-Version verwendet werden.
- Die Grafik darf inhaltlich und gestalterisch nicht veraendert werden.
- Der Hinweis muss vor Abgabe der Bestellung hervorgehoben bereitgestellt werden.
- Ein direkter Link zum Ziel des QR-Codes soll zusaetzlich vorhanden sein.

Digitale Inhalte und Dienstleistungen fallen nicht unter diese Kennzeichnungspflicht.

Dasselbe gilt fuer das GARAN-Label: Die gewerbliche Haltbarkeitsgarantie ist an Waren geknuepft. An einem Artikel, der ausschliesslich digitale Inhalte liefert, darf das Label deshalb nicht stehen; es wuerde eine Zusage benennen, die dort rechtlich nicht bestehen kann. Massgeblich ist der Inhaltstyp des Artikels: `virtual` erhaelt kein Label, `mixed` behaelt es, weil koerperliche Ware enthalten ist. Bei abgeschalteten Downloads entfaellt die Pruefung ohne Abfrage. Die Regel gilt fuer jede Ausgabe und fuer den Bestellsnapshot; die Artikelverwaltung weist ausdruecklich darauf hin, statt das Label still wegzulassen.

### GARAN-Label

Das GARAN-Label ist erforderlich, wenn alle folgenden Voraussetzungen erfuellt sind:

- Der Hersteller bietet eine gewerbliche Haltbarkeitsgarantie an.
- Die Garantie verursacht fuer den Verbraucher keine Zusatzkosten.
- Die Garantie umfasst die gesamte Ware und nicht nur einzelne Bestandteile.
- Die Laufzeit betraegt mehr als zwei Jahre.
- Der Hersteller hat dem Haendler die Information zur Garantie bereitgestellt.

Das Label gilt selbst als Hinweis auf beziehungsweise Werbung mit einer Garantie. Die vollstaendigen Garantiebedingungen bleiben deshalb zusaetzlich erforderlich. Sie muessen insbesondere Garantiegeber, Verfahren, betroffene Ware, Dauer und raeumlichen Geltungsbereich enthalten. Die Garantieerklaerung muss dem Verbraucher spaetestens bei Lieferung auf einem dauerhaften Datentraeger vorliegen.

Das Modul erzwingt diese rechtliche Pflicht nicht durch ein Pflichtfeld. Der Shopbetreiber kann die Garantiebedingungen auch ausserhalb des Moduls auf einem dauerhaften Datentraeger bereitstellen. Fehlt ein als `garan_terms` markierter Anhang, warnt das Modul, zeigt das GARAN-Label bei vollstaendigen Kerndaten aber weiterhin an. Der Shopbetreiber bleibt fuer die Vollstaendigkeit der Garantieerklaerung und ihre rechtzeitige Bereitstellung verantwortlich.

Die Garantie kann rechtlich auf bestimmte Laender begrenzt sein oder je Land unterschiedlich lange gelten. Solche laenderabhaengigen Garantien gehoeren jedoch nicht zum ersten Umfang. GARAN-Daten duerfen fuer einen Artikel nur gepflegt werden, wenn Garantiedauer und Garantiebedingungen in allen vom Shop belieferten Laendern identisch gelten. Die globale Modulaktivierung bleibt davon unberuehrt.

Der Haendler muss nicht selbst aktiv nach unbekannten Herstellergarantien suchen. Sobald die Information vorliegt und die Voraussetzungen erfuellt sind, muss das Label angezeigt werden.

Das Label enthaelt produktspezifische Daten:

- Name des Herstellers beziehungsweise Garantiegebers.
- Modellkennung des Herstellers.
- Dauer der Haltbarkeitsgarantie in Jahren.

Zulaessig sind ganze Jahre und halbe Jahre. Andere Dezimalwerte sind laut EU-Praxisleitfaden nicht vorgesehen. Der Leitfaden nennt ganze Zahlen einschliesslich zweistelliger Werte und `0,5`-Schritte; das Dezimaltrennzeichen im Label ist ausdruecklich ein Komma, unabhaengig von der Shopsprache.

### Rechtliche Unsicherheit bei der geschachtelten Darstellung

Der EU-Praxisleitfaden zeigt fuer den Gewaehrleistungshinweis eine Anzeige nach Klick oder Mouseover. Die EU-Durchfuehrungsverordnung erlaubt eine geschachtelte Darstellung jedoch ausdruecklich nur fuer das GARAN-Label.

Deshalb gilt fuer die Planung die konservative Variante:

- Gewaehrleistungshinweis im Checkout mit Text, Link und der Zuordnung bei gemischten Warenkoerben unmittelbar anzeigen. Der rechtlich massgebliche Wortlaut steht damit ohne jede Interaktion auf der Seite. Die offizielle Grafik des Hinweises oeffnet sich auf Wunsch; der Praxisleitfaden zeigt fuer den Hinweis ausdruecklich eine Anzeige nach Klick oder Mouseover, die Grafik nachzuladen bleibt also innerhalb des Leitfadens und haelt die Seite schlank.
- GARAN-Label auf der Produktseite als offizielle kompakte Anzeige mit vollstaendiger Ansicht nach der ersten Interaktion anzeigen.

## Geplanter Funktionsumfang

### Gewaehrleistungshinweis

- Im Checkout vor dem Bestellbutton anzeigen.
- Nur anzeigen, wenn der Warenkorb mindestens eine koerperliche Ware enthaelt.
- Nur in B2C-Verkaufssituationen anzeigen. Gastzugriffe werden bis zu einer eindeutigen B2B-Zuordnung als B2C behandelt.
- Die Sprache der Shopoberflaeche bestimmt die Grafik.
- Die Grafik liegt als `lang/<Sprachverzeichnis>/notice.svg` direkt im jeweiligen Sprachpaket.
- Farbige Originalgrafik der EU verwenden.
- Grafik in der Standarddarstellung lesbar halten.
- Grafik vergroesserbar machen, ohne sie zu beschneiden oder zu veraendern.
- Direkten, sprachabhaengigen Link zum Ziel des QR-Codes anbieten.
- Sprachabhaengigen Text und direkten Link in die HTML-Bestellbestaetigung aufnehmen.
- In der Text-Bestellbestaetigung einen klaren Hinweis mit direktem Link aufnehmen.

### GARAN-Label

- Nur bei entsprechend gepflegten und validen Produktdaten anzeigen.
- Auf der Produktdetailseite produktspezifisch anzeigen.
- An weiteren direkten Bestellmoeglichkeiten wie Produktlisten, Suche, Sonderangeboten oder Cross-Selling produktspezifisch anzeigen oder dort den direkten Kauf fuer den Artikel unterbinden.
- Offizielle kompakte GARAN-Anzeige verwenden.
- Vollstaendiges Label beim ersten Klick oder Touch anzeigen. Mouseover ist nicht vorgesehen: Ein Geraet ohne Zeiger kennt ihn nicht, und eine Anzeige, die schon beim Darueberfahren aufgeht, laesst sich mit der Tastatur nicht bedienen.
- Direkten Link zum Ziel des QR-Codes anbieten.
- Im Checkout bei jedem betroffenen Artikel anzeigen.
- Hinterlegte Garantiebedingungen am Produkt zugaenglich machen und ueber den bestehenden Mailablauf auf einem dauerhaften Datentraeger bereitstellen.
- Das GARAN-Label weder in die Mail aufnehmen noch als Datei anhaengen.

## Modulform

Die Funktion soll als Systemmodul umgesetzt werden.

Vorgeschlagener Modulname:

```text
guarantee_labels
```

Die Modulklasse setzt `$this->code = 'guarantee_labels'` und beginnt mit `$this->version = '1.00'`. Die Modulversion wird bei funktionalen oder schemarelevanten Aenderungen erhoeht. Sie dokumentiert damit, fuer welche Modulversion die Aktion `Modul aktualisieren` und die zugehoerige idempotente Schemaroutine gelten.

Das Systemmodul uebernimmt:

- Installation und Konfiguration.
- Anlage der Modultabellen.
- Aktivierung und Deaktivierung der Ausgabe.
- Bereitstellung der offiziellen GARAN-Vorlagen. Die sprachabhaengigen Gewaehrleistungsgrafiken gehoeren zu den Sprachpaketen.
- Erzeugung und Cache-Verwaltung der produktspezifischen GARAN-Label.

Bei der Deinstallation werden nur Konfigurationseintraege entfernt. Modultabellen, Produkt- und Bestelldaten bleiben erhalten. Dadurch gehen historische Bestelldaten nicht verloren.

Die Klassenerweiterungen werden dabei abgemeldet, mit einer Ausnahme: Solange der Katalog noch eine Garantiedauer oder einen `garan_terms`-Anhang fuehrt, bleibt `guarantee_labels_product.php` eingerichtet. Sie ist die einzige Stelle, an der `duplicate_product()` sich einklinken laesst, und ohne sie wuerde ein Duplikat Modellkennung, Garantiedauer und Garantiebedingungen des Ursprungsartikels erben; nach einer spaeteren Neuinstallation stuende dort eine Zusage, die nie gegeben wurde. Alles andere in dieser Erweiterung folgt dem Modulstatus und ist ohne ihn wirkungslos.

Die Artikeldaten werden dabei ausdruecklich nicht geloescht, damit eine Neuinstallation daran ankuepfen kann. Ohne das Systemmodul fehlen aber auch die Pflegefelder, mit denen sich eine Garantiedauer oder ein `garan_terms`-Anhang entfernen liesse. Die verbliebene Erweiterung kann sich deshalb nicht selbst ueberfluessig machen. Die Deinstallationsmaske nennt den Ausweg: Wer sie trotzdem loswerden will, deinstalliert sie unter Module > Artikelverwaltung und nimmt in Kauf, dass ein Duplikat die GARAN-Daten des Ursprungsartikels wieder erbt.

## Datenmodell

### Tabelle `products`

Neues Feld:

```sql
products_garan_duration DECIMAL(4,1) NULL
```

Bedeutung:

- `NULL`: Zur Herstellergarantie ist nichts hinterlegt.
- `0.5` bis `2.0`: Der Hersteller sagt eine Dauer zu, die die gesetzliche Gewaehrleistung nicht uebersteigt. Der Wert wird gespeichert und dokumentiert diesen Stand, erzeugt aber kein Label.
- Wert groesser als `2.0`: Dauer der qualifizierten Haltbarkeitsgarantie, erzeugt ein Label.
- Werte unter `0.5` sind nicht zulaessig; eine Dauer von null ist keine Aussage.
- Zulaessige Nachkommastellen: `.0` und `.5`.

Die Trennung von Speichern und Ausgeben ist bewusst: Beim direkten Lesen der Datenbank oder beim Abgleich mit einem Fremdsystem ist `2` eine eindeutige Aussage, `NULL` dagegen nur die Abwesenheit einer Aussage. Eine Dauer von zwei Jahren oder weniger wird deshalb gespeichert, aber nicht gekennzeichnet; ob ein Label entsteht, entscheidet allein die Pruefung auf mehr als zwei Jahre an einer Stelle im Renderer.

Der zulaessige Bereich gilt fuer jede Eingabe, auch fuer den Import: `0.5` bis `99.5` in Halbjahresschritten. Ein Wert ausserhalb davon wird nicht gespeichert. Ein Artikelimport scheitert daran trotzdem nicht: Zurueckgehalten werden genau die drei GARAN-Kerndaten, die uebrige Produktzeile wird regulaer importiert und der Shopbetreiber bekommt die konkrete Meldung ueber den `messageStack`.
- Werte von `0.5` bis `99.5` sind zulaessig, ganze wie halbe Jahre zweistellig. Die Vorlage bietet dafuer genug Platz; die Grenze folgt dem Praxisleitfaden, der ganze Zahlen bis zwei Stellen nennt.
- Der Datenbankwert traegt einen Punkt, das Label ein Komma. Die Umsetzung trennt beides in `duration_text()`.
- Admin und Import akzeptieren Komma oder Punkt als Eingabe und normalisieren den Datenbankwert.

Bereits vorhandene Felder:

```text
products.manufacturers_id
products.products_manufacturers_model
manufacturers.manufacturers_name
```

Zuordnung zum GARAN-Label:

| GARAN-Feld | modified-Quelle |
| --- | --- |
| Hersteller/Garantiegeber | `manufacturers.manufacturers_name` |
| Modellkennung | `products.products_manufacturers_model` |
| Garantiedauer | `products.products_garan_duration` |

`products.products_model` wird nicht als regulaere Modellkennung verwendet. Dieses Feld kann eine interne Artikelnummer des Haendlers enthalten und muss nicht der Modellkennung des Herstellers entsprechen.

### Garantiebedingungen als Artikel-Anhang

Die Garantieerklaerung nutzt die bereits vorhandenen Artikel-Anhaenge in `products_content` und `media/products/`. Das Modul fuehrt keinen zweiten Datei-Upload und keine eigene Ablage fuer Katalogdateien ein.

Da ein Artikel mehrere Anhaenge wie Datenblaetter, Anleitungen und Garantieerklaerungen enthalten kann, erhaelt `products_content` ein zusaetzliches Typfeld:

```sql
products_content.content_type VARCHAR(32) NOT NULL DEFAULT ''
```

Regeln:

- Ein leerer Wert kennzeichnet einen normalen bestehenden Artikel-Anhang.
- Der feste Wert `garan_terms` kennzeichnet die Garantieerklaerung.
- Pro Artikel und Sprache kann genau ein Anhang als Garantieerklaerung dienen.
- Zulaessig sind nur lokal gespeicherte Dateien. Ein Eintrag mit ausschliesslich `content_link` ist kein dauerhafter Datentraeger fuer den Mailversand.
- Ein `garan_terms`-Eintrag traegt eine Datei und keinen externen Link. Beides zusammen ergaebe zwei verschiedene Dokumente: `includes/modules/products_media.php` zeigt bei gesetztem `content_link` nur den Link, die Mail versendet dagegen die Datei. Ein nicht leerer Link wird deshalb abgelehnt.
- Es gibt keine zusaetzliche Formateinschraenkung fuer `garan_terms`. Alle Dateiformate, die die vorhandene Artikel-Anhangsverwaltung akzeptiert, koennen verwendet und unveraendert versendet werden.
- Der normale Artikel-Anhang bleibt auf der Produktseite sichtbar und kann weiterhin auch dort heruntergeladen werden.
- Deutsch und Englisch werden durch die bereits vorhandene Spalte `languages_id` getrennt. Ein Anhang vom Typ `garan_terms` ist je Sprache optional.
- Ein fehlender Anhang blockiert weder das Speichern der GARAN-Daten noch die Anzeige des sprachneutralen GARAN-Labels. In dieser Sprache wird lediglich keine Garantieerklaerung per Mail versendet.
- Die Kombination aus `products_id`, `languages_id` und `content_type` wird bei `garan_terms` in der Anwendung auf Eindeutigkeit geprueft. Ein allgemeiner Unique-Index ist nicht moeglich, weil ein Artikel beliebig viele normale Anhaenge mit leerem Typ haben darf.

### Bestell-Modultabellen

Die Bestellsnapshots liegen nicht in `orders` und `orders_products`, sondern in zwei eigenen Modultabellen. Vorbild ist das bestehende Systemmodul `withdraw` mit `orders_withdraw` und `orders_withdraw_products`.

```sql
CREATE TABLE IF NOT EXISTS `orders_guarantee` (
  `orders_guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `notice_hash` varchar(64) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`orders_guarantee_id`),
  UNIQUE KEY `idx_orders_id` (`orders_id`)
);

CREATE TABLE IF NOT EXISTS `orders_products_guarantee` (
  `orders_products_guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `orders_products_id` int(11) NOT NULL,
  `manufacturers_name` varchar(255) NOT NULL,
  `manufacturers_model` varchar(64) NOT NULL,
  `garan_duration` decimal(4,1) NOT NULL,
  `garan_hash` varchar(64) NOT NULL,
  `terms_hash` varchar(64) DEFAULT NULL,
  `terms_filename` varchar(255) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`orders_products_guarantee_id`),
  UNIQUE KEY `idx_orders_products_id` (`orders_products_id`),
  KEY `idx_orders_id` (`orders_id`)
);
```

Gruende fuer die Modultabellen:

- Herstellername, Hersteller-Modellkennung, Garantiedauer und `garan_hash` sind `NOT NULL`. Eine Bestellposition hat diese GARAN-Kerndaten vollstaendig oder sie hat keine Zeile.
- `terms_hash` und `terms_filename` sind optional. Sie sind entweder beide gefuellt oder beide `NULL`.
- Fuer den Gewaehrleistungshinweis gilt dasselbe: Eine Bestellung hat einen vollstaendigen historischen Hinweis-Snapshot in `orders_guarantee` oder keinen. Der Snapshot stellt die historischen Daten fuer den Mailversand bereit. `orders_guarantee` erhaelt kein zusaetzliches Feld fuer den Entstehungskontext oder die Anzeige.
- `orders` erhaelt kein Feld wie `has_legal_notices`. Ob rechtliche Hinweise vorhanden sind, ergibt sich aus einer Zeile in `orders_guarantee` oder mindestens einer Zeile in `orders_products_guarantee`. Beide Abfragen sind ueber `orders_id` indiziert.
- Nur betroffene Bestellungen und Positionen belegen Platz.
- Spaetere Erweiterungen aendern nur Modultabellen, nicht `orders` oder `orders_products`.

`orders_id` steht bewusst auch in `orders_products_guarantee`. Der Mailversand spart damit den Umweg ueber `orders_products`. Das Widerruf-Modul haelt es genauso.

`products_id` wird nicht gespeichert. Der Bestellsnapshot soll nicht auf den Katalog zeigen.

Die Tabellenkonstanten `TABLE_ORDERS_GUARANTEE` und `TABLE_ORDERS_PRODUCTS_GUARANTEE` werden in `includes/database_tables.php` eingetragen, wie die Konstanten des Widerruf-Moduls.

### Zuordnung beim Bestellabschluss

| Quelle | Bestellsnapshot |
| --- | --- |
| `manufacturers.manufacturers_name` | `orders_products_guarantee.manufacturers_name` |
| `products.products_manufacturers_model` | `orders_products_guarantee.manufacturers_model` |
| `products.products_garan_duration` | `orders_products_guarantee.garan_duration` |
| Optional vorhandene Datei des sprachabhaengigen Artikel-Anhangs | Archivdatei unter `media/guarantee_labels/archive/terms/<terms_hash>/<terms_filename>` |
| Optional vorhandenes `products_content.content_file` | `orders_products_guarantee.terms_filename` |

Die Bestellbestaetigung und ein spaeterer erneuter Mailversand verwenden nur diese gespeicherten Werte. Aenderungen am Produkt oder am Hersteller duerfen historische Bestellungen nicht veraendern.

### Hinweis-Snapshot und Versionierung

Zusaetzlich zu den drei sichtbaren GARAN-Werten haelt die Bestellung den zugeordneten historischen Gewaehrleistungshinweis fest.

Die Existenz einer Zeile in `orders_guarantee` bedeutet ausschliesslich, dass der Bestellung ein historischer Gewaehrleistungshinweis zugeordnet ist. Bei normalen Bestellungen entsteht der Snapshot im Bestellabschluss, bei manuell angelegten Bestellungen bereits beim Anlegen der Bestellung. Damit stehen Text und Link fuer den jeweiligen Mailversand bereit. Die Tabelle dokumentiert nicht den Entstehungsweg der Bestellung.

Die Zeile ist insbesondere kein allgemeiner Nachweis dafuer, dass der Hinweis im Checkout angezeigt oder spaeter per Mail versendet wurde. Bei einer Storefront-Bestellung entsteht sie nur im dafuer vorgesehenen Checkout-Ablauf mit koerperlicher Ware und angezeigtem Hinweis. Bei einer manuellen Bestellung ist sie dagegen bereits ein vorbereiteter Mail-Snapshot, auch wenn die Bestellung spaeter ausschliesslich virtuelle Ware enthaelt und der Hinweis nicht ausgegeben wird. Auswertungen duerfen `orders_guarantee` deshalb nur als `Hinweis-Snapshot vorhanden` interpretieren.

Bei einer normalen Bestellung entsteht die Zeile beim Bestellabschluss aus Modulstatus, Kundengruppe, Warenkorbinhalt und einer vollstaendig gepflegten Bestellsprache. Dazu muessen `lang/<Sprachverzeichnis>/notice.svg` sowie die Sprachkonstanten fuer Mailtext, Linktext und sprachabhaengige Your-Europe-URL vorhanden sein. Fehlt einer dieser Bestandteile, laeuft der Checkout ohne Gewaehrleistungshinweis weiter und es entsteht keine Zeile. Ein erneuter Mailversand darf den Snapshot nicht aus aktuellen Katalogdaten oder einer spaeter vervollstaendigten Sprache neu ableiten.

Vorlagen-, Font- und Rendererversion brauchen keine eigenen Felder. Sie stecken im jeweiligen Hash:

```text
garan_hash  = sha256( Herstellername + Modellkennung + Garantiedauer + Version beider SVG-Vorlagen + Fontversion + Rendererversion )
notice_hash = sha256( Inhalt notice.svg + Sprachverzeichnis + Mailtext + Linktext + Ueberschrift + Your-Europe-URL + Version )
```

Die Schreibweise mit `+` beschreibt nur die fachlichen Bestandteile. Technisch werden die Werte als UTF-8 in einer fest definierten Reihenfolge und mit eindeutigen Feldgrenzen serialisiert. Dezimalwerte werden vorher kanonisch normalisiert. Vorlagen und Schriften gehen mit dem SHA-256-Hash ihres tatsaechlichen Dateiinhalts ein. Dadurch koennen unterschiedliche Feldaufteilungen nicht denselben Eingabestrom erzeugen.

Aendert sich ein Bestandteil, aendert sich der zugehoerige Hash. `garan_hash` ist der gemeinsame Cache- und Archivschluessel fuer die zusammengehoerige farbige und kompakte Variante. `notice_hash` referenziert den vollstaendigen historischen Sprachstand aus Grafik, Sprachverzeichnis, Mailtext, Linktext, Ueberschrift und URL. `notice.json` fuehrt genau diese Felder, dazu die Version.

Die Zusage an der Bestellposition entsteht in zwei getrennten Fassungen. Die HTML-Fassung maskiert Garantiedauer, Herstellername, Modellkennung und Dateinamen einzeln, bevor sie in das Textmuster eingesetzt werden; die Textfassung verwendet die Rohwerte und loest nur die Entities des Musters auf. Eine Fassung aus der anderen abzuleiten wuerde entweder Maskierungen aufloesen oder Entities in die Textmail tragen. Der Dateinamenfilter laesst Zeichen wie `<` durch, weil sie einen Dateinamen nicht unbrauchbar machen; maskiert wird deshalb bei der Ausgabe.

Mailtext und Anhangsliste fragen dieselbe Funktion `guarantee_labels_terms_file()`. Getrennt gefragt koennte der Text ein Dokument nennen, das die Anhangspruefung anschliessend als beschaedigt verwirft, und die Mail wuerde etwas behaupten, das sie nicht mitfuehrt.

Ist in der Bestellsprache ein Artikel-Anhang vom Typ `garan_terms` vorhanden, enthaelt `orders_products_guarantee.terms_hash` den SHA-256-Hash seines Inhalts. Der Hash referenziert die Fassung der Garantiebedingungen, die zur Bestellposition gehoert. Ein erneuter Mailversand haengt genau diese archivierte Fassung an, unabhaengig davon, ob der Artikel-Anhang inzwischen ersetzt oder geloescht wurde. Fehlt ein solcher Anhang, bleiben `terms_hash` und `terms_filename` `NULL` und es wird keine Garantieerklaerung angehaengt.

`terms_filename` speichert den bereinigten Dateinamen einschliesslich Erweiterung. Der bestehende Mailweg bietet keinen separaten Namen fuer den Anhang an, sondern uebergibt nur den Dateipfad an PHPMailer. Die Archivdatei muss deshalb selbst unter diesem Dateinamen gespeichert werden. Pfadbestandteile, Steuerzeichen, Kommas und sonstige unzulaessige Dateinamen sind abzulehnen. Ein Komma darf nicht ersetzt oder maskiert werden, weil `check_attachments()` die Anhangsliste mit `explode(',', ...)` zerlegt. Aus demselben Grund wird ein Name mit fuehrendem oder abschliessendem Leerzeichen abgelehnt: `check_attachments()` schneidet den ganzen Pfad mit `trim()`, die Datei waere unter einem anderen Namen gesucht und der Anhang stillschweigend weggefallen. Die Regel steht nur in `guarantee_labels_archive::usable_filename()`; `guarantee_labels_terms_filename()` reicht dorthin durch.

Ein Anhang wird nur archiviert, wenn die Kundengruppe der Bestellung ihn im Shop auch sehen darf. Massgeblich ist dieselbe Bedingung wie in `includes/define_conditions.php`: Bei `GROUP_CHECK = true` muss die Gruppe in `group_ids` stehen, ein leeres `group_ids` erreicht also niemanden; bei abgeschaltetem `GROUP_CHECK` wird `group_ids` gar nicht gelesen und jeder Anhang gilt als sichtbar. Die Moduldiagnose meldet unerreichbare Anhaenge zusaetzlich, weil eine einmal geprueft gespeicherte Markierung spaeter ungueltig werden kann.

Ein `garan_terms`-Anhang gehoert zur GARAN-Kennzeichnung und damit an eine gewerbliche Haltbarkeitsgarantie von mehr als zwei Jahren. Bei einer gespeicherten Dauer von `0.5` bis `2.0` entsteht kein Label, deshalb entsteht auch kein Bestellsnapshot und es wird kein Dokument archiviert oder mitversendet. Ein solcher Wert ist eine Herstellerangabe im Katalog, keine gekennzeichnete Zusage; ihn zu versenden wuerde eine Zusage behaupten, die das Label ausdruecklich nicht macht. Ein Snapshot ohne GARAN-Grafikdaten ist dafuer nicht vorgesehen.

`terms_hash` und `terms_filename` bilden ein optionales Wertepaar. Sobald einer der beiden Werte gesetzt ist, muss auch der andere gesetzt sein. Ihre Existenz beeinflusst die Anzeige des sprachneutralen GARAN-Labels nicht.

#### Nachtraeglich geaenderte Kundengruppe

`orders_address_edit()` in `admin/includes/functions/orders_functions.php` erlaubt es, `orders.customers_status` ueber das Pull-down im Adressblock zu aendern; der Shop meldet das selbst mit `ERROR_STATUS_CHANGE`. Fuer bereits vorhandene Snapshots hat diese Aenderung keine Folgen:

- Wechselt eine Bestellung von B2C nach B2B, bleiben vorhandene Zeilen in `orders_guarantee` und `orders_products_guarantee` bestehen. Sie halten fest, was der Kunde tatsaechlich erhalten hat; ein erneuter Mailversand soll dasselbe Dokument reproduzieren und keine bereinigte Fassung.
- Wechselt sie von B2B nach B2C, entstehen keine Snapshots nachtraeglich. Der Kunde hat den Hinweis im Checkout nie gesehen, eine spaeter erzeugte Zeile waere eine erfundene Historie.

Das ist dieselbe Regel wie bei der Bestellsprache: Eine spaetere Aenderung erzeugt oder ersetzt vorhandene Snapshots nicht.

Positionen, die **nach** der Aenderung eingefuegt werden, folgen dagegen der aktuellen Kundengruppe der Bestellung. `orders_product_insert()` liest sie aus `$order->info['status']`, also aus `orders.customers_status`, nicht aus dem Kundenstammsatz und nicht aus der Sitzung des Admins.

Daraus kann eine Bestellung entstehen, die einen Hinweis-Snapshot hat, aber keine Positions-Snapshots, oder umgekehrt. Das ist kein Widerspruch: `orders_guarantee` bedeutet ausschliesslich `Hinweis-Snapshot vorhanden` und ist von den Positionen unabhaengig.

### Cache und Archiv

Ein Hash in der Bestellung ist nur so gut wie die Dateien dahinter. Cache und Archiv sind deshalb getrennt.

Der jederzeit loeschbare Cache enthaelt beide gemeinsam erzeugten GARAN-Varianten:

```text
cache/guarantee_labels/
└── <garan_hash>/
    ├── colour.svg
    └── nested.svg
```

Das unveraenderliche Grafik- und Hinweisarchiv liegt unter:

```text
media/guarantee_labels/archive/
├── garan/
│   └── <garan_hash>/
│       ├── colour.svg
│       └── nested.svg
├── notice/
│   └── <notice_hash>/
│       ├── notice.svg
│       └── notice.json
└── terms/
    └── <terms_hash>/
        └── <terms_filename>
```

`notice.json` speichert Sprache, Mailtext, Linktext, Ueberschrift, Your-Europe-URL und Version des bei der Snapshoterzeugung verwendeten Hinweises. Die erste und jede erneute Bestellbestaetigung lesen Text, Link und Ueberschrift anhand von `orders_guarantee.notice_hash` aus diesem Snapshot. Spaetere Aenderungen an Sprachkonstanten oder URLs veraendern bestehende Bestellungen nicht.

Jedes Hashverzeichnis erhaelt beim Schreiben eine `checksums.json` mit dem SHA-256 jeder abgelegten Datei. Beim Lesen wird jede Datei gegen ihren Eintrag geprueft; ein Verzeichnis mit abweichendem Inhalt gilt als nicht lesbar. Vor dem Umbenennen wird das temporaere Verzeichnis vollstaendig gelesen, genau so wie eine spaetere Anfrage es liest, einschliesslich Sidecar. Ein verkuerzter Schreibvorgang kann eine positive Byteanzahl melden; ohne diese Pruefung koennte eine Datenbankzeile auf ein Archiv verweisen, dessen Schaden erst beim Zugriff auffaellt. Ein unlesbarer Sidecar zaehlt als Schaden und nicht als Archiv ohne Sidecar. Ist ein Sidecar vorhanden, muss er jede gelesene Datei nennen; ein entfernter Eintrag wuerde sonst eine ersetzte Datei decken. Ein beschaedigtes Hashverzeichnis wird beim naechsten Schreibvorgang verworfen und neu angelegt, weil sein Inhalt aus seinem Namen folgt.

Das erzeugte Label traegt selbst, ob die Cachekopie steht: Der Renderer hat sie in derselben Zerlegung gerade gelesen oder geschrieben. Die Ausgabe fragt deshalb nicht noch einmal nach, sonst laese und hashte sie dieselben 294 kB ein zweites Mal je Label. Dasselbe gilt auf der Bestellseite, wo zuerst die billige Frage nach der Cachekopie steht und die Archivdatei nur gelesen wird, wenn sie fehlt.

Auch die Cache-URL des vollstaendigen Labels wird gegen den Sidecar geprueft. Faellt die Pruefung durch, verweist die Seite nicht auf die Datei, sondern bettet die Grafik der aktuellen Anfrage ein. Der Verzeichnisname deckt die Eingangsdaten des Labels ab und nicht die Bytes der erzeugten Dateien, deshalb ist die Pruefsumme der einzige Weg, ein beschaedigtes Archiv von einem intakten zu unterscheiden. Aeltere Archive ohne Datei bleiben unveraendert lesbar.

Der Anhang mit den Garantiebedingungen wird beim Mailversand zusaetzlich gegen `terms_hash` geprueft. Passt der Inhalt nicht mehr, protokolliert das Modul den Fall und laesst den Anhang weg, statt eine ersetzte Datei zu versenden.

Ein Hashverzeichnis heisst immer wie ein SHA-256. Das Archiv weist jeden anderen Namen ab und bildet daraus keinen Pfad, beim Lesen wie beim Schreiben: Jeder Pfad der Klasse entsteht ueber dieselbe Pruefung, sonst bliebe ausgerechnet das Anlegen eines Verzeichnisses ungeschuetzt. Die Hashes entstehen zwar ausschliesslich im Modul, sie kommen aber aus Datenbankspalten und landen in einem Dateipfad; eine Spalte, die je etwas anderes fuehrt, darf das Archiv nicht auf ein fremdes Verzeichnis zeigen lassen. Fuer den Dateinamen eines Anhangs gilt beim Lesen dieselbe Regel wie beim Schreiben.

Cachedateien duerfen jederzeit geloescht und aus den versionierten Vorlagen neu erzeugt werden. Archivdateien werden nur einmal geschrieben und nie ueberschrieben. Mehrere Bestellungen duerfen dieselben Hash-Verzeichnisse referenzieren. Beim Loeschen einer Bestellung werden gemeinsam verwendete Archivdateien nicht entfernt. Eine automatische Archivbereinigung gehoert nicht zum ersten Umfang.

Der GARAN-Grafikcache ist vom Smarty-Blockcache zu unterscheiden. `includes/modules/cross_selling.php`, `includes/modules/new_products.php` und `includes/modules/products_media.php` koennen ihre gerenderte Ausgabe ueber `CacheCheck()` bis zu `CACHE_LIFETIME` zwischenspeichern. Ihre Cache-IDs enthalten keinen Stand der GARAN-Daten. Ohne ausdrueckliche Leerung koennten deshalb ein entferntes Label, ein geaenderter Hersteller oder eine geaenderte Garantiebedingung bis zum Ablauf des Blockcache sichtbar bleiben.

Das Modul leert von sich aus keinen Cache.

Die erzeugten Grafiken liegen unter ihrem Inhaltshash. Eine geaenderte Dauer, ein anderer Herstellername oder eine neue Vorlagenversion ergeben einen neuen Hash und damit neue Dateien; die alten werden nie wieder abgefragt. Der eigene Cache kann also verwaisen, aber nicht falsch werden. Eine automatische Leerung waere reines Aufraeumen gewesen und haette bei jeder Artikelaenderung die Grafiken aller uebrigen Artikel mit verworfen.

Der Smarty-Blockcache haelt dagegen fertig gerendertes HTML mitsamt eingebettetem Label. Betroffen sind die Module, die ueber `CacheCheck()` zwischenspeichern und Labels enthalten: Cross-Selling, neue Artikel, Artikel einer Kategorie, ebenfalls gekaufte Artikel und die kommenden Artikel. Produktdetailseite und Kategorielisting setzen `caching = 0` und sind nicht betroffen. Ihre Cache-IDs bestehen aus Sprache, Kundengruppe, Artikel, Waehrung und Land; ein GARAN-Stand geht nicht ein. Ein umbenannter Hersteller aendert die ID deshalb nicht, und der Block wird bis zum Ablauf von `CACHE_LIFETIME` weiter aus dem Cache bedient. Im Auslieferungszustand ist `USE_CACHE` abgeschaltet, dann entsteht die Lage gar nicht.

Fuer beides gilt derselbe Weg: Der Shopbetreiber leert den Cache ueber die vorhandene Aktion `delcache` in `admin/configuration.php`. Die Moduldiagnose weist darauf hin. Der Shopcache gehoert dem Shopbetreiber; ihn bei jedem Artikelspeichern zu verwerfen wuerde die Arbeit aller anderen Module mit wegwerfen.

Eine saubere automatische Loesung waere ein GARAN-Stand in den Cache-IDs der betroffenen Module. Das erfordert Eingriffe in sechs Kernmodule und gehoert nicht in diese Erweiterung.

`delcache` entfernt mit `clear_dir(DIR_FS_CATALOG.'cache/')` auch das Unterverzeichnis `cache/guarantee_labels/` vollstaendig. Der Renderer muss das Basisverzeichnis deshalb vor jedem Schreibvorgang bei Bedarf rekursiv neu anlegen, bevor er darin temporaere Nachbarverzeichnisse erzeugt. Historische Dateien in den Archivverzeichnissen bleiben unveraendert.

In Unterverzeichnissen von `cache/` schuetzt `clear_dir()` weder `.htaccess` noch `index.html`: Der rekursive Aufruf verwendet `$basefiles = true` und entfernt anschliessend das Unterverzeichnis selbst. Fuer `cache/guarantee_labels/` sind deshalb keine dauerhaften Schutzdateien vorgesehen. Sollte das Verzeichnis spaeter eigene Schutzdateien benoetigen, muss die Cache-Leerung sie ausdruecklich wiederherstellen oder ausserhalb des geloeschten Unterverzeichnisses ablegen.

Das Grafik- und Hinweisarchiv erhaelt eine eigene `.htaccess`, die direkte HTTP-Aufrufe vollstaendig sperrt. PHP kann die Dateien weiterhin lokal lesen. Ein Cache fuer `notice.svg` ist nicht erforderlich; die aktuelle Storefront-Ausgabe verwendet die Datei direkt aus dem Sprachverzeichnis.

Garantie-Anhaenge werden unter `media/guarantee_labels/archive/terms/<terms_hash>/<terms_filename>` archiviert, im selben Archiv wie Label und Hinweis. Das Archiv liegt unterhalb von `DIR_FS_DOCUMENT_ROOT`, damit `check_attachments()` die Datei ueber den bestehenden Mailweg findet. Der Inhalts-Hash bildet das Verzeichnis; die Datei selbst traegt den in `terms_filename` gespeicherten Namen. Der Dateipicker in `admin/includes/modules/content_manager_products.php` liest nur Dateien direkt unter `media/products/`; das Archiv liegt ausserhalb davon und erscheint dort nicht als unbenutzte Artikeldatei.

### Fehlerverhalten bei Cache und Archiv

Fehler beim erstmaligen Erzeugen oder Archivieren eines Snapshots blockieren weder den normalen Checkout noch das manuelle Anlegen einer Bestellung.

- Vor einem Cache-Schreibvorgang legt der Renderer `cache/guarantee_labels/` bei Bedarf rekursiv neu an. Danach werden die Dateien zuerst in einem eindeutigen temporaeren Verzeichnis darunter vollstaendig geschrieben und geprueft. Erst anschliessend wird das Verzeichnis atomar auf `<garan_hash>` umbenannt. Existiert das Ziel bereits, werden die vorhandenen Dateien geprueft und wiederverwendet. Ist ein vorhandener Cache unvollstaendig oder beschaedigt, wird er nicht verwendet; die aktuelle Anfrage verwendet direkt erzeugte SVGs und ein spaeterer Schreibversuch darf den Cache kontrolliert neu aufbauen.
- Kann lediglich der Cache nicht geschrieben werden, darf der Renderer die erzeugten SVGs fuer die aktuelle Anfrage direkt verwenden. Der Fehler wird protokolliert und in der Moduldiagnose angezeigt.
- Die zusammengehoerigen GARAN-Grafiken sowie `notice.svg` und `notice.json` werden zuerst in einem eindeutigen temporaeren Verzeichnis neben dem endgueltigen Ziel vollstaendig geschrieben und geprueft. Erst danach wird das komplette Verzeichnis atomar auf den Hashpfad umbenannt.
- Garantie-Anhaenge werden atomar auf Dateiebene archiviert. Das Hashverzeichnis wird bei Bedarf angelegt. Die Datei wird unter einem eindeutigen temporaeren Namen im selben Verzeichnis geschrieben, anhand von `terms_hash` geprueft und danach auf `<terms_filename>` umbenannt. Existiert die Zieldatei bereits, wird sie geprueft und wiederverwendet. Bei gleichem Dateiinhalt und unterschiedlichem Dateinamen duerfen mehrere Dateien im selben Hashverzeichnis liegen.
- Eine Zeile in `orders_guarantee` wird erst geschrieben, wenn `notice.svg` und `notice.json` vollstaendig archiviert und geprueft sind. Schlaegt dies fehl, laeuft die Bestellung weiter und es entsteht keine Zeile.
- Eine Zeile in `orders_products_guarantee` wird erst geschrieben, wenn `colour.svg` und `nested.svg` vollstaendig archiviert und geprueft sind. Schlaegt dies fehl, laeuft die Bestellung weiter und es entsteht keine Zeile fuer die betroffene Position.
- Ein in der Bestellbearbeitung hochgeladener Garantie-Anhang muss zusaetzlich vom Typ her passen. `guarantee_labels_terms_accepted()` liest die Listen aus `admin/includes/upload_types.php` und fuehrt genau die sechs Listen zusammen, die auch `admin/content_manager.php` fuer eine Artikeldatei zusammenfuehrt. Sonst liesse sich eine am Artikel zulaessige Datei in der Bestellung nicht als Ersatz hochladen. Geprueft werden Endung und der ueber `finfo` ermittelte MIME-Typ des Inhalts, wie es die Klasse `upload` tut; beide muessen passen. Ist die Liste nicht lesbar, wird nichts angenommen.
- Ist beim erstmaligen Erzeugen eines Snapshots ein optionaler Garantie-Anhang vorhanden, kann aber nicht archiviert werden, wird der GARAN-Kerndatensatz ohne Dateiverweis gespeichert. `terms_hash` und `terms_filename` bleiben beide `NULL`. Der Fehler darf nicht wie ein regulaer fehlender Anhang behandelt werden, sondern wird ausdruecklich protokolliert. Besteht die Ursache weiterhin, zeigt sie auch die Moduldiagnose.
- Fehlerprotokolle enthalten mindestens Bestell-ID, gegebenenfalls Bestellpositions-ID, Hash, Zielpfad, Fehlerart und Zeitpunkt. Sie enthalten keine vollstaendigen Kunden- oder Zahlungsdaten.
- Die Grafik illustriert den Gewaehrleistungshinweis, sie ist nicht der Hinweis. Laesst sich die Archivgrafik nicht in den Cache kopieren oder fehlt die Sprachgrafik, gehen Text und Link trotzdem hinaus; nur Grafik und Oeffnen-Schaltflaeche entfallen, sonst stuende ein leerer Dialog auf der Seite. Ein technischer Grafikfehler darf eine gesetzlich geforderte Angabe nicht von der Seite nehmen. Fehlt dagegen der archivierte Wortlaut selbst, entsteht kein Hinweis.
- Ein fehlendes oder beschaedigtes `notice.json` oder ein fehlender Garantie-Anhang wird beim Mailversand nicht durch aktuelle Katalog- oder Sprachdaten ersetzt. Der betroffene Mailinhalt wird uebersprungen, der Versand laeuft weiter und der Fehler wird protokolliert. Fehlende GARAN-Archivgrafiken betreffen nur die historische Anzeige im Admin und haben keinen Einfluss auf den Mailversand.
- Der Admin erhaelt fuer Fehler bei jeder manuell ausgeloesten Aktion eine Fehlermeldung ueber den bestehenden `messageStack`. Das gilt auch fuer die Snapshoterzeugung beim manuellen Anlegen einer Bestellung und beim Einfuegen einer Position: `guarantee_labels_snapshot_failures()` sammelt die Fehler des Requests, die Aufrufstelle gibt sie aus. Ein nicht anwendbarer Snapshot ist dabei kein Fehler; ein Artikel ohne GARAN-Daten, eine B2B-Gruppe oder eine nicht gepflegte Sprache erzeugen einfach keine Zeile. Nur eine defekte Sprache, ein defekter Renderer oder ein fehlgeschlagenes Archiv erreichen den Shopbetreiber. Bei Aktionen mit anschliessendem Redirect verwendet GARAN `$messageStack->add_session()`, damit die Meldung den naechsten Request erreicht. Das gilt insbesondere fuer `install()`, fehlgeschlagene `update()`-Aufrufe und Herstelleraktionen. Auch die Bestellbearbeitung gehoert dazu: `admin/orders_edit.php` leitet nach jeder Positionsaktion weiter. Der Import laeuft ohne Redirect und verwendet deshalb `$messageStack->add()`. Das Artikelspeichern gehoert dagegen zu den Aktionen mit Redirect: `admin/categories.php` leitet nach `insert_product` wie nach `update_product` weiter, deshalb verwendet die Klassenerweiterung dort ebenfalls `add_session()`. Eine zusaetzliche Admin-Fehlerverwaltung ist nicht erforderlich. GARAN verwendet den vorhandenen `LoggingManager` mit dem Dateimuster `DIR_FS_LOG . 'mod_guarantee_labels_%s_%s.log'`. Daraus entstehen Dateien wie `mod_guarantee_labels_error_2026-08-27.log`, die von der bestehenden Logverwaltung angezeigt und von der vorhandenen Logpflege erfasst werden. Die Moduldiagnose zeigt aktuell feststellbare Fehler wie fehlende Schreibrechte, unvollstaendige Sprachen oder fehlende Archivdateien. Sie prueft dabei die Anwesenheit und nicht den Inhalt: Jede archivierte Datei zurueckzulesen und zu hashen hiesse rund 300 kB je Labelhash und 640 kB je Hinweishash bei jedem Aufruf der Modulseite. Ob eine vorhandene Datei noch zu ihrem Hash passt, entscheidet der Leseweg beim Zugriff und protokolliert es dort. Ein zusaetzlicher persistenter Fehlerspeicher und ein Status `geprueft` gehoeren nicht zum Umfang.

Durch diese Reihenfolge verweist keine Datenbankzeile auf ein nur teilweise geschriebenes Archiv. Es gibt keine automatische nachtraegliche Neuerzeugung eines fehlgeschlagenen Bestellsnapshots.

### Installation der Tabellen

Die Tabellen `orders_guarantee` und `orders_products_guarantee` entstehen in der `install()`-Routine des Systemmoduls per `CREATE TABLE IF NOT EXISTS`, nicht im Installationsschema. Fuer diese beiden Modultabellen ist deshalb kein Datenbankupdate erforderlich. Die Modulkonfiguration wird erst eingetragen, nachdem auch diese Tabellen und ihre erforderlichen Indizes geprueft wurden.

Die beiden Core-Felder `products.products_garan_duration` und `products_content.content_type` stehen weiterhin im Installationsschema und im regulaeren Datenbankupdate. Zusaetzlich legt die `install()`-Routine des Systemmoduls beide Spalten selbst idempotent an. Sie folgt dem vorhandenen Muster aus `admin/includes/modules/system/products_tariff.php`:

1. Modultabellen mit `CREATE TABLE IF NOT EXISTS` anlegen und ihre Indizes pruefen.
2. Core-Spalte mit `SHOW COLUMNS FROM <Tabelle> LIKE <Spalte>` pruefen.
3. Nur eine fehlende Core-Spalte mit `ALTER TABLE` anlegen.
4. Zuerst fehlende Core-Spalten anlegen, danach fehlende Indizes, und einen Index nur dann, wenn alle seine Spalten wirklich vorhanden sind: Ein Index ueber eine fehlende Spalte laesst sich nicht erzeugen und beendete die Installation mit einem SQL-Fehler statt mit einer Meldung. Ein Index, dessen Name schon vergeben ist, wird nicht erneut angelegt, auch wenn er ueber andere Spalten geht oder seine Eindeutigkeit fehlt; ein zweites `ADD` scheitert am doppelten Namen und ein `DROP` wuerde einen Index entfernen, den der Shop aus eigenen Gruenden angelegt haben kann. Danach erneut pruefen, dass Modultabellen, ihre Spalten, die Indizes samt Spaltenfolge und Eindeutigkeit sowie beide Core-Spalten mit dem vorgesehenen Schema vorhanden sind. Bei den Spalten zaehlt nicht nur der Typ: Auch `NOT NULL`, `AUTO_INCREMENT` und der Defaultwert werden gegen die Definition gehalten, mit der die Spalte angelegt wird. Der Default wird immer verglichen, nicht nur wo die Definition einen nennt: Eine Spalte ohne `DEFAULT`-Klausel hat keinen, und eine, die trotzdem einen fuehrt, wuerde neue Zeilen mit einem Wert vorbelegen, den das Modul nie vorgesehen hat. Dazu kommt der Primaerschluessel jeder Modultabelle, der genau eine Spalte umfassen muss; ein zusammengesetzter liesse eine zweite Zeile zur selben Bestellung zu, die die gesamte Snapshotlogik ausschliesst. Eine Tabelle mit richtigen Spaltentypen und falschem Schluessel scheitert sonst erst beim ersten Snapshot. `CREATE TABLE IF NOT EXISTS` laesst eine vorhandene Tabelle unberuehrt, eine teilweise oder aeltere Tabelle gaebe sonst einen SQL-Fehler beim ersten Snapshot statt einer Meldung bei der Installation. Spalten der Modultabellen werden nur geprueft und nicht per `ALTER TABLE` ergaenzt: Fehlt dort eine, ist die Tabelle nicht die des Moduls.
5. Erst dann die Konfiguration einschliesslich des aktiven Modulstatus eintragen.

Die Spaltendefinitionen lauten in allen drei Anlagewegen identisch:

```sql
products.products_garan_duration DECIMAL(4,1) NULL
products_content.content_type VARCHAR(32) NOT NULL DEFAULT ''
```

Damit bleiben Neuinstallation, regulaeres Datenbankupdate und Modulinstallation idempotent. Werden zuerst nur die neuen Dateien eingespielt und das Datenbankupdate noch nicht ausgefuehrt, erzeugt die Modulinstallation die fehlenden Spalten, bevor eine SELECT-Erweiterung aktiv werden kann. Ist nur eine Spalte vorhanden, legt `install()` ausschliesslich die andere an. Eine bereits vorhandene Spalte mit unvereinbarem Typ wird nicht stillschweigend geaendert; die Installation bleibt inaktiv und meldet den konkreten Schemafehler ueber den `messageStack`. Eine teilweise fehlgeschlagene Installation traegt den aktiven Status ebenfalls nicht ein; ein erneuter Installationsversuch kann den vorhandenen Teil gefahrlos wiederverwenden.

`admin/module_export.php` leitet unmittelbar nach `install()` weiter und erzeugt fuer diesen Pfad keine eigene Erfolgs- oder Fehlermeldung. `install()` schreibt deshalb sowohl seine Erfolgsmeldung als auch einen Schemafehler ausdruecklich mit `$messageStack->add_session()`. Ein einfaches `$messageStack->add()` ist in `install()` unzulaessig, weil die Meldung beim Redirect verloren ginge. Bei einem Fehler bleibt die Modulkonfiguration einschliesslich `MODULE_GUARANTEE_LABELS_STATUS` unangetastet.

`admin/includes/extra/modules/add_db_fields/` registriert `products_garan_duration` fuer den Speicherweg der Artikelverwaltung; diese Erweiterungsstelle legt selbst keine Datenbankspalte an. Die Registrierung fliesst ueber `ADD_PRODUCTS_FIELDS` in `add_data_fields()` und damit in das `$sql_data_array` von `insert_product()`. Fuer das Lesen ist sie nicht noetig, weil die Artikelmaske den Artikel mit `SELECT *` laedt. `products_content.content_type` wird direkt von der angepassten Artikel-Anhangsverwaltung gelesen und geschrieben.

`CREATE TABLE IF NOT EXISTS` legt bei einer bereits vorhandenen Tabelle keine neuen Indizes an. Die idempotente Schemapruefung verwendet deshalb auch das `SHOW KEYS`-Muster aus `admin/includes/modules/system/withdraw.php`.

Das Systemmodul implementiert zusaetzlich `update()`. `install()` und `update()` rufen dieselbe zentrale Schemaroutine fuer Modultabellen, Indizes und Core-Spalten auf. Dadurch gilt jede spaetere Schemaerweiterung an genau einer Stelle und erreicht auch bereits installierte Module:

- Bei einer Erstinstallation fuehrt `install()` die Schemaroutine aus und legt die Konfiguration erst nach erfolgreicher Pruefung an.
- Bei einer vorhandenen Installation stellt der Konstruktor ueber `$this->properties['button_update']` den vorhandenen Button `Modul aktualisieren` bereit. Der Button ruft in `admin/module_export.php?set=system` die bestehende Frameworkaktion `action=update` fuer dieses Systemmodul auf.
- Die Admin-Aktion `Modul aktualisieren` fuehrt die Methode `update()` aus. Eine Deinstallation oder Neuinstallation ist dafuer weder erforderlich noch vorgesehen.
- `update()` aendert bestehende Status- und Modulwerte nicht. Es legt nur fehlende Schemaelemente und spaeter neu eingefuehrte Konfigurationsschluessel idempotent an.
- Bei Erfolg gibt `update()` eine optionale Erfolgsmeldung oder eine leere Zeichenkette zurueck; der jeweilige Frameworkaufrufer erzeugt daraus die vorhandene Erfolgsmeldung.
- Bei einem Schemafehler schreibt `update()` die konkrete Fehlermeldung mit `$messageStack->add_session(..., 'error')`, gibt `false` zurueck und verhindert damit die automatische Erfolgsmeldung des Frameworks.

Jede Modulversion mit einer Schemaaenderung nennt die Admin-Aktion `Modul aktualisieren` als erforderlichen Updateschritt. Die Tests pruefen sowohl den direkten Versionssprung als auch das wiederholte Ausfuehren ohne weitere Aenderung.

Die `remove()`-Routine loescht nur Konfigurationseintraege. Modultabellen und beide Core-Spalten bleiben erhalten, damit historische Bestell- und Produktdaten nicht verloren gehen.

### Aufraeumen beim Loeschen

Wird eine Bestellung oder eine Bestellposition geloescht, muessen die Modulzeilen mit verschwinden. Die beiden vorhandenen Loeschpfade werden dafuer erweitert:

- `xtc_remove_order()` in `inc/xtc_remove_order.inc.php` erhaelt einen eigenen GARAN-Block. Er prueft wie die vorhandene Bereinigung des Widerruf-Moduls per `SHOW TABLES`, ob `orders_guarantee` und `orders_products_guarantee` existieren, und loescht vorhandene Zeilen ueber `orders_id`. Die Pruefung darf nicht vom Modulstatus abhaengen, weil die Tabellen nach einer Deinstallation erhalten bleiben.
- `orders_product_delete()` in `admin/includes/functions/orders_functions.php` loescht die Zeile in `orders_products_guarantee` ueber `orders_products_id`, sofern die Tabelle existiert. Dadurch funktioniert die Bestellbearbeitung auch, wenn das Modul nie installiert wurde.

Beim Loeschen eines Artikels oder Artikel-Anhangs ist keine separate GARAN-Zuordnung aufzuraeumen. Die Typinformation liegt direkt in der geloeschten Zeile von `products_content`. Die physische Anhangsdatei folgt weiterhin den vorhandenen Regeln der Artikel-Anhangsverwaltung.

Die Loeschlogik anderer Module wird im Rahmen dieser Erweiterung nicht veraendert.

## Artikelverwaltung

Die Artikelmaske erhaelt einen eigenen Bereich `EU-Haltbarkeitsgarantie`. Die Dateien und ihr Typ werden weiterhin ueber die vorhandene Verwaltung der Artikel-Anhaenge gepflegt. Die Bearbeitungsmaske eines Anhangs erhaelt dafuer die Auswahl `Standard` oder `GARAN-Garantiebedingungen`.

Anzuzeigen sind:

- Ausgewaehlter Hersteller.
- Hersteller-Modellkennung.
- Eingabefeld fuer die Garantiedauer.
- Status und Verknuepfung des als `garan_terms` markierten Anhangs fuer Deutsch und Englisch, soweit die jeweilige Sprache im Shop aktiv ist.
- Kurze Erklaerung der Voraussetzungen.

Der Hilfetext muss zuerst den Anwendungsbereich des Feldes klarstellen, weil sonst der Eindruck entsteht, ein leeres Feld sei ein Mangel:

- Das Feld nimmt die Dauer der Haltbarkeitsgarantie des Herstellers auf. Es ist unabhaengig von der gesetzlichen Gewaehrleistung; auf die weist der Gewaehrleistungshinweis im Checkout fuer die gesamte Bestellung hin, ohne Pflege am Artikel.
- Ein leeres Feld ist der Normalfall. Ein Artikel mit ausschliesslich der gesetzlichen Gewaehrleistung erhaelt korrekterweise kein GARAN-Label.
- Ein Label entsteht erst ab `2.5`. Werte von `0.5` bis `2.0` werden gespeichert und dokumentieren, was der Hersteller zusagt; ein Label erzeugen sie nicht, weil eine Garantie bis zwei Jahre dem Verbraucher nichts ueber die gesetzliche Gewaehrleistung hinaus gibt. Der Hilfetext nennt diesen Grund.
- Speichern und Ausgeben sind bewusst getrennt, siehe die Begruendung beim Datenmodell: Eine Dauer von zwei Jahren oder weniger wird gespeichert, aber nicht gekennzeichnet. Der zulaessige Bereich `0.5` bis `99.5` in Halbjahresschritten gilt dabei fuer jede Eingabe, auch fuer den Import; ein abgewiesener Wert haelt den Artikelimport nicht auf, sondern nur die drei GARAN-Kerndaten zurueck.

Danach muss der Hilfetext klarstellen, dass eine Laufzeit nur eingetragen werden darf, wenn:

- eine Herstellergarantie vorliegt,
- sie kostenlos ist,
- sie die gesamte Ware umfasst und
- sie laenger als zwei Jahre gilt,
- sie mit derselben Dauer und denselben Bedingungen in allen vom Shop belieferten Laendern gilt und
- die vollstaendigen Garantiebedingungen verfuegbar sind.

Das Modul weist auf fehlende Garantiebedingungen hin, blockiert die Pflege der GARAN-Daten aber nicht. Der Shopbetreiber entscheidet selbst, welche Dateien er hinterlegt.

Validierung beim Speichern:

- Leeres Feld ist erlaubt und wird als `NULL` gespeichert.
- Der Wert muss mindestens `0.5` sein.
- Nur ganze oder halbe Jahre sind erlaubt.
- Bis einschliesslich `2.0` wird der Wert gespeichert, ohne Hersteller, Modellkennung oder Textbreite zu verlangen. Aus dieser Dauer entsteht kein Label, also braucht es die Labeldaten nicht. Erst ab `2.5` gelten die folgenden Anforderungen.
- Ein Hersteller muss ausgewaehlt sein.
- `products_manufacturers_model` muss gefuellt sein.
- Ein optional markierter Anhang muss lokal gespeichert und fuer die betroffenen B2C-Kundengruppen erreichbar sein. Die Anhangsverwaltung prueft die ausgewaehlten `group_ids` gegen die Kundengruppen, denen das Label gezeigt wird, und lehnt die Markierung ab, wenn eine davon das Dokument nicht sehen kann.
- Herstellername und Modellkennung muessen in die offiziellen editierbaren Bereiche der Vorlage passen.
- Werte duerfen weder abgeschnitten noch durch eine kleinere als die vorgegebene Schrift passend gemacht werden.
- Bei Artikeln mit Varianten muss dieselbe Garantie fuer alle bestellbaren Auspraegungen gelten. Andernfalls darf im ersten Umfang kein GARAN-Label aktiviert werden.
- Das Modul speichert keine Laenderzuordnung und fuehrt im Storefront keine Pruefung des Lieferlands durch. Mit dem Eintragen der Garantiedauer bestaetigt der Admin, dass die Garantie in allen belieferten Laendern identisch gilt.

Bei unvollstaendigen GARAN-Kerndaten darf kein GARAN-Label im Storefront erscheinen. Ein fehlender Garantie-Anhang zaehlt nicht zu diesen Kerndaten und verhindert die Anzeige nicht. Die Artikelmaske soll Fehler und fehlende optionale Anhaenge konkret benennen. Jede bei einer Admin-Aktion entstehende Fehlermeldung wird ueber den bestehenden `messageStack` ausgegeben.

Eine abgelehnte Eingabe erreicht die Spalten nicht. `insert_product_before()` entfernt bei einem Fehler alle drei GARAN-Kerndaten aus dem Datensatz: `products_garan_duration`, `products_manufacturers_model` und `manufacturers_id`. Sie gehoeren zusammen; einen Teil durchzulassen wuerde entweder eine gute gespeicherte Garantiedauer loeschen oder den Artikel mit einer Dauer ohne Hersteller dahinter zuruecklassen. Die Artikelverwaltung schreibt die Zeile, bevor sie ueber `insert_product_error()` vom Fehler erfaehrt; ein Eintrag an dieser Stelle wuerde also eine gueltige gespeicherte Garantie bei einem Speichervorgang loeschen, den der Shopbetreiber gar nicht durchbekommen hat. Ein bewusst geleertes Feld bleibt davon unberuehrt und setzt weiterhin `NULL`.

### Artikelpruefung als Klassenerweiterung

Die Pruefung greift nicht ueber eine Aenderung an `admin/includes/classes/categories.php`, sondern ueber die vorhandene Klassenerweiterung des Modultyps `categories`:

```text
admin/includes/modules/categories/guarantee_labels_product.php
```

- `insert_product_before()` normalisiert die Garantiedauer und prueft den GARAN-Kerndatensatz, bevor der Artikel geschrieben wird.
- `insert_product_error()` meldet das Ergebnis an die Artikelverwaltung zurueck, damit sie bei einer abgelehnten Eingabe auf der Artikelmaske bleibt. Dieser Hook ist eine allgemeine Erweiterung von `categoriesModules` und nicht Bestandteil dieses Moduls.
- Die Klasse heisst bewusst nicht `guarantee_labels`, weil sie sonst mit der Klasse des Systemmoduls kollidieren wuerde.
- Solange `MODULE_GUARANTEE_LABELS_STATUS` nicht aktiv ist, reicht die Erweiterung die Artikeldaten unveraendert durch.

Das Systemmodul installiert und entfernt diese Klassenerweiterung selbst. Es ruft dafuer deren `install()` beziehungsweise `remove()` auf und laesst `update_module_configuration('categories')` die Konfiguration `MODULE_CATEGORIES_INSTALLED` aus den tatsaechlich installierten Modulen neu aufbauen. Vorbild ist `includes/modules/checkout/paypal_plan_checkout.php`, das seine begleitenden Module genauso einrichtet. Dadurch bleibt es bei einem Modul und einem Schalter; andere Klassenerweiterungen und ihre Sortierung bleiben unberuehrt. `update()` richtet eine von Hand entfernte Klassenerweiterung wieder ein.

### Artikel duplizieren

Beim Duplizieren eines Artikels werden GARAN-Daten nicht ungeprueft aktiviert uebernommen. Auch das laeuft ueber die Klassenerweiterung und braucht keine Aenderung an `admin/includes/classes/categories.php`:

- Ist beim Ursprungsartikel `products_garan_duration` gesetzt, wird das Feld beim Duplikat auf `NULL` gesetzt.
- In diesem Fall wird auch `products_manufacturers_model` beim Duplikat geleert, damit die Modellkennung fuer den neuen Artikel bewusst neu gepflegt werden muss. Bei Artikeln ohne GARAN-Daten bleibt das bisherige allgemeine Kopierverhalten unveraendert.
- Zeilen aus `products_content` mit `content_type = 'garan_terms'` werden auch bei aktivierter Option `cnt_copy` nicht auf das Duplikat kopiert. Andere Artikel-Anhaenge folgen weiterhin dem bestehenden Kopierverhalten.
- Der Admin muss Modellkennung, Garantiedauer und sprachabhaengige Garantiebedingungen fuer das Duplikat ausdruecklich pruefen und neu pflegen.

Dafuer werden zwei vorhandene Hooks verwendet:

- `duplicate_product_before()` erhaelt das `$sql_data_array` der zu kopierenden Produktzeile und leert dort Garantiedauer und Hersteller-Modellkennung.
- `duplicate_product_end()` laeuft nach dem Kopierblock der Artikel-Anhaenge und entfernt die auf das Duplikat kopierten Zeilen mit `content_type = 'garan_terms'`.

Damit kann ein duplizierter Artikel nicht allein durch kopierte Daten mit der Modellkennung oder Garantieerklaerung des Ursprungsartikels als GARAN-Artikel erscheinen.

Beide Hooks pruefen den Modulstatus ausdruecklich nicht. Modellkennung und `content_type` sind Core-Felder und gehoeren dem Ursprungsartikel, unabhaengig davon, ob das Modul gerade laeuft. Ein spaeter eingeschaltetes Modul wuerde die mitkopierten Werte sonst als gueltige GARAN-Daten des Duplikats ausgeben.

### Import und Export

Der bestehende Produkt-CSV-Import und -Export wird um genau ein Feld erweitert:

```text
p_garan_duration
```

- Der Import schreibt `products.products_garan_duration`.
- Ein leerer Wert wird als `NULL` gespeichert. Komma und Punkt werden wie in der Artikelverwaltung akzeptiert und kanonisch normalisiert.
- Die Validierung verwendet den nach dem Import wirksamen Hersteller aus `p_manufacturer`, die Hersteller-Modellkennung aus `p_man` und die Garantiedauer aus `p_garan_duration`. Fehlt eines dieser Felder in der CSV, wird fuer die Pruefung der vorhandene Artikelwert verwendet.
- Die Pruefung laeuft auch dann, wenn `p_garan_duration` gar nicht Teil der Datei ist. Eine Zeile, die nur `p_man` oder `p_manufacturer` aendert, kann eine gespeicherte Dauer unbrauchbar machen; ohne diesen Lauf entstuende dabei stillschweigend ein unvollstaendiger GARAN-Artikel.
- Ist der resultierende GARAN-Datensatz unvollstaendig oder ungueltig, laesst der Import alle drei GARAN-Kerndaten unangetastet. Ein bestehender Artikel behaelt seinen gespeicherten Stand, ein neuer Artikel bekommt die Standardwerte der Spalten. Der Admin erhaelt die konkrete Fehlermeldung ueber den `messageStack`.
- Die uebrigen Felder der Produktzeile werden regulaer importiert. Die Erweiterungsstelle `insert_before` kann den Import einer Zeile nicht abbrechen, und ein ganzer Artikelimport soll nicht an einem einzelnen GARAN-Feld scheitern. Zurueckgehalten werden deshalb genau die drei Felder, aus denen ein unvollstaendiger GARAN-Datensatz entstehen koennte, und nicht die ganze Zeile.
- Der Export gibt `products_garan_duration` normalisiert als `p_garan_duration` aus; `NULL` wird als leeres Feld exportiert.
- `products_content.content_type` und Garantie-Anhaenge sind nicht Bestandteil des Produkt-CSV-Formats. Sie werden weiterhin ausschliesslich ueber die Artikel-Anhangsverwaltung gepflegt.

Die Umsetzung nutzt die vorhandenen Erweiterungsstellen unter `admin/includes/extra/modules/import/file_layout/`, `admin/includes/extra/modules/import/insert_before/`, `admin/includes/extra/modules/export/file_layout/` und `admin/includes/extra/modules/export/export_end/`.

## Erzeugung der GARAN-Grafiken

Grundlage sind ausschliesslich die offiziellen EU-Dateien:

- `images/guarantee_labels/assets/garan_label_colour.svg`
- `images/guarantee_labels/assets/garan_label_nested.svg`

Die beiden sprachneutralen Vorlagen liegen zentral unter `images/guarantee_labels/assets/`. Sie gehoeren weder in ein Sprachverzeichnis noch in ein Shoptemplate.

Nur folgende Elemente duerfen veraendert werden:

- `XX`: Garantiedauer.
- `Brand/Trademark`: Herstellername.
- `Model identifier`: Modellkennung.

Alle weiteren Elemente, Farben, Abstaende, Schriftgroessen und QR-Codes bleiben unveraendert.

Die Schrift Inter muss in den vorgesehenen Schnitten bereitstehen:

- Regular.
- ExtraBold.

Die Schriftdateien liegen unter `images/guarantee_labels/fonts/`:

- `Inter-Regular.ttf`
- `Inter-ExtraBold.ttf`
- `Inter-Regular.woff2`
- `Inter-ExtraBold.woff2`

Die TTF-Dateien dienen der serverseitigen Messung. Der Renderer verwendet `imagettfbbox()` mit genau dem Schriftschnitt und der Schriftgroesse des jeweiligen editierbaren Vorlagenbereichs. Er rechnet die gemessene Breite in SVG-Einheiten um und zieht eine kleine feste Sicherheitstoleranz vom verfuegbaren Bereich ab. Passt Herstellername oder Modellkennung nicht, lehnt die Artikelverwaltung das Speichern der GARAN-Daten mit einer konkreten Fehlermeldung ab. Die Schriftgroesse wird nicht verkleinert und der Text wird nicht abgeschnitten.

Das Modul prueft bei der Aktivierung den vollstaendigen Zustand: GD mit FreeType und `imagettfbbox()`, beide SVG-Vorlagen, die benoetigten Schriften und die Dateien der drei Klassenerweiterungen. Fehlt davon etwas, kann das Modul nicht aktiviert werden und nennt die fehlenden Bestandteile ueber den `messageStack`. Dieselbe Pruefung laeuft in `process()`, damit eine spaetere Aktivierung ueber die Modulverwaltung nicht daran vorbeikommt. Ein Modul, das ohne Vorlagen als aktiv gilt, meldet sonst Erfolg und zeichnet nie ein Label. Die Moduldiagnose zeigt den Zustand zusaetzlich an. ImageMagick und eine zusaetzliche PHP-Bibliothek werden nicht benoetigt.

Die WOFF2-Dateien dienen der Browserausgabe. Eine zentrale Modul-CSS-Datei bindet sie per `@font-face` ein. Die gecachten SVGs werden inline in das HTML eingefuegt, damit sie dieselben zentral geladenen Schriften verwenden und die Interaktion zwischen kompakter und vollstaendiger Darstellung ohne eingebettete Fontkopien funktioniert. `images/.htaccess` wird um die Dateiendung `.woff2` erweitert. Dadurch bleibt `.ttf` fuer direkte HTTP-Aufrufe gesperrt und steht PHP weiterhin lokal fuer die Messung zur Verfuegung.

Der Renderer muss Eingaben XML-sicher maskieren. Produktdaten duerfen keinen eigenen SVG- oder HTML-Code einschleusen.

Vorlagen und Messung arbeiten in UTF-8. Ein Shop darf laut `includes/configure.php` aber auch auf `latin1` laufen, dann liefert der Katalog ISO-8859-15. Der Renderer wandelt Herstellername und Modellkennung deshalb mit `encode_utf8()` um, bevor er misst, hasht und einsetzt. Ohne diese Umwandlung verwirft die XML-Maskierung den ganzen Wert, das Feld bliebe leer und Snapshot wie Hash wuerden trotzdem geschrieben.

Die Kodierung wird dabei aus `DB_SERVER_CHARSET` benannt und nicht erraten. `mb_detect_encoding()` prueft ISO-8859-1 vor ISO-8859-15 und macht aus einem Eurozeichen ein Waehrungszeichen, das dann dauerhaft im Label steht. Ebenso wenig taugt die Byte-Gueltigkeit als Erkennung: Manche ISO-8859-15-Folgen sind zugleich gueltiges UTF-8 und liessen sich davon nicht unterscheiden.

Umgewandelt wird deshalb genau einmal an einer festen Grenze. `label()` wandelt seine Eingaben um und misst danach; `fits()` erwartet bereits umgewandelte Werte. Ein Aufrufer von aussen, etwa die Artikelverwaltung oder die Bestellbearbeitung, geht ueber `measurable()` durch dieselbe Grenze. So misst die Pruefung genau die Bytes, die spaeter in der Grafik stehen, und kein Wert wird zweimal gewandelt.

Die Breitenpruefung findet zweimal statt. Die Artikelverwaltung prueft frueh, um eine brauchbare Fehlermeldung zu geben. Der Renderer prueft noch einmal und ist die verbindliche Schranke: Er ist nicht der einzige Weg in die Spalten. Ein in `admin/manufacturers.php` umbenannter Hersteller loest keine erneute Pruefung der betroffenen Artikel aus, und ein Import oder ein Fremdsystem prueft gar nicht. Passt ein Wert nicht, entsteht kein Label und der Grund wird protokolliert. Abschneiden oder Verkleinern ist an keiner Stelle vorgesehen.

Das Modul erzeugt SVG fuer Storefront und Checkout auf Basis der offiziellen SVG-Dateien. Ein PNG fuer die Auftragsbestaetigung wird nicht erzeugt.

Die Ausgabe soll anhand folgender Werte gecacht werden:

```text
Herstellername + Modellkennung + Garantiedauer + Version beider SVG-Vorlagen + Fontversion + Rendererversion
```

Storefront und Checkout verwenden die gemeinsam erzeugten Dateien `cache/guarantee_labels/<garan_hash>/colour.svg` und `cache/guarantee_labels/<garan_hash>/nested.svg`. Die Auftragsbestaetigung enthaelt keine GARAN-Grafik und haengt weder SVG noch PNG an. Sie fuegt vorhandene archivierte Garantiebedingungen ueber den vorhandenen Anhangsmechanismus bei.

## Storefront

### Produktdetailseite

Das GARAN-Label muss eindeutig dem Artikel zugeordnet sein.

Empfohlene Ausgabe:

- Kompaktes GARAN-Label in der Naehe der Bestellmoeglichkeit.
- Vollstaendiges Label in einem Dialog oder einer vergroesserten Ansicht.
- Oeffnung beim ersten Klick oder Touch.

Zur Groesse sagt der Praxisleitfaden fuer die digitale Darstellung: Das Label muss in der Standarddarstellung lesbar bleiben und darf weder verzerrt noch beschnitten werden. Eine feste Mindestgroesse in Pixeln nennt er nicht; das verschachtelte Label ist ausdruecklich fuer Situationen mit wenig Platz vorgesehen.

Daraus folgen zwei Grenzen fuer die Ausgabe:

- Das kompakte Label wird mit 120 Pixeln Breite ausgegeben und schrumpft nur, wenn die Spalte schmaler ist. Es traegt ausschliesslich die Dauer, bleibt damit auch klein lesbar und dient als Einstieg in die vollstaendige Ansicht. Ueber seine Vorlagengroesse von 368,5 Einheiten hinaus wird es nie vergroessert.
- Das vollstaendige Label wird nie schmaler als seine 269,29 Einheiten dargestellt. Herstellername und Modellkennung stehen dort in 9 Einheiten; darunter faellt ihr Text unter neun Pixel. Ein schmaler Bildschirm scrollt statt zu verkleinern.

Die bestehende Erweiterungsstelle befindet sich in:

```text
includes/extra/modules/product_info_end/
```

Die Standardtemplates benoetigen einen definierten Smarty-Platzhalter fuer das Label.

Hinterlegte Garantiebedingungen bleiben ueber den bestehenden Medienbereich des Artikels zugaenglich. `includes/modules/products_media.php` liest alle fuer Artikel, Sprache und Kundengruppe sichtbaren Zeilen aus `products_content`; damit erscheint auch ein lokaler Anhang mit `content_type = 'garan_terms'` ohne neuen Downloadweg. Der Typ beeinflusst die bestehende Storefront-Ausgabe nicht, sondern kennzeichnet den Anhang zusaetzlich fuer den Bestellsnapshot und den Mailversand.

Fuer Artikellisten gilt die vorhandene Shopkonfiguration `SHOW_BUTTON_BUY_NOW`:

- Ist der direkte Warenkorb-Button aktiviert, zeigt jeder betroffene Artikel in Kategorie, Suche, Sonderangeboten, Cross-Selling und weiteren Artikellisten das kompakte GARAN-Label beim Artikel.
- Ist der direkte Warenkorb-Button deaktiviert, wird in Artikellisten kein GARAN-Label benoetigt. Das Label erscheint erstmals in der Produktdetailansicht.
- Die Produktdetailansicht und der Checkout zeigen das Label unabhaengig von `SHOW_BUTTON_BUY_NOW`.
- Alle vier mitgelieferten Templates erhalten dieselben Smarty-Platzhalter und folgen dieser Regel.

### Bereitstellung der Labeldaten

Jede GARAN-Ausgabe braucht Herstellername, Hersteller-Modellkennung und Garantiedauer. Nur die Produktdetailabfrage in `includes/classes/product.php` liefert diese drei Werte bereits vollstaendig. Warenkorb, Checkout und Artikellisten duerfen deshalb nicht voraussetzen, dass der Herstellername in ihren vorhandenen Produktdaten enthalten ist.

Die Umsetzung erweitert die vorhandenen SELECT-Listen um die erreichbaren Spalten der Produkttabelle:

- `ADD_SELECT_DEFAULT`: `p.products_garan_duration`; `p.products_manufacturers_model` und `p.manufacturers_id` sind dort bereits vorhanden.
- `ADD_SELECT_SEARCH`: `p.products_garan_duration` und `p.manufacturers_id`; `p.products_manufacturers_model` ist dort bereits vorhanden.
- `ADD_SELECT_CART`: `p.products_garan_duration` und `p.products_manufacturers_model`; `p.manufacturers_id` ist dort bereits vorhanden.
- `ADD_SELECT_PRODUCT`: `p.products_garan_duration`, `p.products_manufacturers_model` und `p.manufacturers_id` fuer neue Artikel, Cross-Selling, Reverse-Cross-Selling, ebenfalls gekaufte Artikel und weitere Abfragen mit `$product->default_select`.

Diese Erweiterungen erfolgen ueber eine Datei unter `includes/extra/define_add_select/`. Die Erweiterungsstelle ergaenzt nur SELECT-Ausdruecke und keine JOINs. Sie kann den Herstellernamen deshalb nicht bereitstellen.

Der Herstellername wird ueber eine zentrale GARAN-Hilfsfunktion nachgeladen:

- `guarantee_labels_manufacturer_names()` liest die aktiven Hersteller aus `TABLE_MANUFACTURERS` und merkt sich Treffer und Fehlschlaege fuer die Dauer des Aufrufs. Ein Hersteller wird deshalb je Seitenaufruf hoechstens einmal abgefragt.
- `guarantee_labels_collect_manufacturers()` sammelt vorab die unterschiedlichen `manufacturers_id` eines Ergebnisblocks und laedt sie mit genau einer Abfrage. Diesen Weg nehmen alle Ausgabestellen, denen der ganze Block vorliegt.
- Artikellisten laufen ueber die Klassenerweiterung `includes/modules/product/guarantee_labels_listing.php`. `buildDataArray()` wird je Artikel aufgerufen; der Block liegt dort nicht vor. Der Puffer stellt sich deshalb selbst um: Muss er ein zweites Mal nachfragen, laeuft der Aufrufer offenbar eine Liste durch, und die aktiven Hersteller werden einmal geholt. Ab da beantwortet er alles ohne Abfrage. Vierzig Hersteller kosten so zwei Abfragen statt siebenunddreissig, und eine Seite mit einem einzigen Hersteller bleibt bei einer. Dasselbe gilt fuer die Bestellpositionen, die `getOrderData()` ebenfalls einzeln liefert.
- `GUARANTEE_LABELS_MANUFACTURER_LIMIT` begrenzt das auf 500 aktive Hersteller. Darueber bleibt es beim einzelnen Nachfragen, damit ein Marktplatzshop nicht Tausende Zeilen laedt, um eine Handvoll zu beantworten. Eine gescheiterte Abfrage zaehlt nicht als leere Liste, sonst blieben die Label den Rest der Anfrage aus.
- Die GARAN-Ausgabe verwendet ausschliesslich den Namen aus dieser eigenen Abfrage aktiver Hersteller. Bereits von einer anderen Produktabfrage mitgelieferte Werte in `manufacturers_name` werden fuer das Label ignoriert. Das gilt insbesondere fuer `includes/modules/new_products.php`, dessen vorhandener `LEFT JOIN` den Herstellerstatus nicht prueft.
- Fehlt die Hersteller-ID, ist der Hersteller inaktiv oder liefert die Abfrage keinen Namen, ist der GARAN-Datensatz unvollstaendig und der Artikel erhaelt kein Label.
- Bei deaktiviertem Modul sowie in Artikellisten mit `SHOW_BUTTON_BUY_NOW = false` erfolgt keine GARAN-Nachladeabfrage.

Die Nachladelogik wird in den vorhandenen Ausgabepipelines aufgerufen:

- alle Ausgaben ueber `product->buildDataArray()`: Produktlisting fuer Kategorie, Suche, Sonderangebote und neue Artikel, Cross-Selling, Reverse-Cross-Selling, ebenfalls gekaufte Artikel, Artikel einer Kategorie und die kommenden Artikel,
- Produktdetailseite ueber `includes/extra/modules/product_info_end/`; der dort bereits geladene Herstellername wird fuer das GARAN-Label ebenfalls nicht direkt verwendet,
- Warenkorb und daraus erzeugte Checkout-Produktdaten,
- weitere direkte Bestelllisten, die `$product->default_select` verwenden.

Der Merkzettel baut seine Produktdaten in `inc/get_wishlist_content.inc.php` selbst auf und laeuft nicht ueber `buildDataArray()`. Er bekommt das kompakte Label trotzdem, ueber die vorhandene Erweiterungsstelle `includes/extra/modules/wishlist_content/`: Er bietet den direkten Weg in den Warenkorb an, und ein Artikel, der von dort gekauft werden kann, muss die Kennzeichnung vorher tragen.

Er laeuft auf derselben Klasse `shoppingCart` wie der Warenkorb und traegt damit dieselben Felder, die `ADD_SELECT_CART` ergaenzt. Der Haken ist deshalb bis auf die Zielvariable mit dem Warenkorbhaken identisch, einschliesslich der Warenkorbkennung: Eine gemerkte reine Downloadvariante bekommt kein Label.

Die Sonderangebotsseite besitzt in diesem Stand keine eigene Abfrage unter `includes/modules/specials.php`, sondern verwendet `includes/modules/default.php` und `includes/modules/product_listing.php`. Cross-Selling verwendet `includes/classes/product.php` und `includes/modules/cross_selling.php`; eine Datei `includes/modules/xsell_products.php` existiert nicht. Die Roadmap verwendet deshalb die tatsaechlichen Erweiterungs- und Ausgabestellen dieses Quellstands.

### Checkout

Der Checkout zeigt:

1. Beim jeweiligen Artikel das GARAN-Label, sofern vorhanden.
2. Den vollstaendigen Gewaehrleistungshinweis vor Abgabe der Bestellung.

Der Hinweis steht in jedem mitgelieferten Template als eigener Block neben Versandart, Zahlungsweise und Bemerkungen, im jeweiligen Aufbau des Templates. Das Modul liefert ihn dafuer in Bestandteilen: `GUARANTEE_NOTICE_TITLE` und `GUARANTEE_NOTICE_BODY`. `GUARANTEE_NOTICE` enthaelt denselben Inhalt mit eigenem Rahmen und eigener Ueberschrift, fuer ein Template, das den Hinweis als fertiges Stueck setzen will.

Ob eine Warenkorbposition koerperliche Ware ist, entscheidet die gewaehlte Attributkombination und nicht der Katalogartikel. Ein Artikel mit einer Download- und einer koerperlichen Variante gilt als Ganzes als gemischt; waehlt der Kunde nur den Download, hat er digitale Inhalte gekauft und erhaelt weder Label noch Zusage noch Anhang. `guarantee_labels_position_physical()` liest die gewaehlten Werte aus der Warenkorbkennung `<products_id>{option}value` und wendet die Regel aus `shopping_cart::get_content_type()` an. Warenkorb, Checkout und Positionssnapshot verwenden dieselbe Entscheidung; eine Artikelliste kennt noch keine Auswahl und fragt weiter den Artikel. Die Artikelfrage lautet dabei, ob der Artikel ueberhaupt als Ware zu haben ist: Nur ein Artikel, der ausschliesslich Downloadattribute traegt, ist digital. `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` gilt fuer eine gewaehlte Kombination und nicht fuer den Artikel, sonst verloere ein gemischter Artikel sein Listenlabel und mit ihm die ausdruecklich gewaehlte koerperliche Variante. `xtcPrice::get_content_type_product()` wird dafuer bewusst nicht gefragt: Die Methode beantwortet die Warenkorbfrage und faltet die Einstellung in den Artikel, wo schon eine Downloadvariante den ganzen Artikel virtuell macht. `guarantee_labels_product_physical()` zaehlt stattdessen selbst, mit einer Abfrage je Artikel und einem Puffer fuer den Aufruf.

Der Gewaehrleistungshinweis wird nicht angezeigt, wenn der Warenkorb ausschliesslich digitale Inhalte oder Dienstleistungen enthaelt.

Bei gemischten Warenkoerben muss der Hinweis klar den koerperlichen Waren zugeordnet werden.

## Bestellverarbeitung

Nach dem Anlegen eines Eintrags in `orders_products` wird bei qualifizierten Artikeln eine Zeile in `orders_products_guarantee` geschrieben.

Die Modultabelle braucht die `orders_products_id`. Die Erweiterungsstelle muss deshalb nach dem Insert liegen:

```text
includes/extra/checkout/checkout_process_products_end/
```

Diese Stelle existiert bereits, liegt noch innerhalb der Produktschleife von `checkout_process.php` und hat `$order_products_id` im Scope. Eine neue Erweiterungsstelle im Storefront ist nicht noetig.

`includes/extra/checkout/checkout_process_products/` laeuft vor dem Insert und ist fuer diesen Zweck nicht geeignet.

Die drei GARAN-Werte werden ueber `ADD_SELECT_CART` und die Herstellerabfrage in die Warenkorb- und Checkout-Produktdaten aufgenommen und dort zentral validiert. Der Snapshot verwendet diese ausdruecklich erweiterten Daten. Er darf nicht voraussetzen, dass die bestehende Warenkorbabfrage den Herstellernamen bereits liefert.

Der Hinweis-Snapshot wird bei einer normalen Bestellung einmal in `orders_guarantee` geschrieben, sofern die Sprache vollstaendig gepflegt war und die Grafik im Checkout angezeigt wurde. Dafuer eignet sich die Erweiterungsstelle nach der Produktschleife:

```text
includes/extra/checkout/checkout_process_order/
```

### Manuell im Admin angelegte Bestellungen

Der normale Checkout-Ablauf greift bei manuell angelegten Bestellungen nicht. Der Admin legt zuerst eine leere Bestellung an. Artikel werden danach ueber `orders_product_insert()` in `admin/includes/functions/orders_functions.php` direkt in `orders_products` eingefuegt.

Die Sprache der manuellen Bestellung stammt aus der Backend-Sitzung des Admins. `admin/customers.php` schreibt `orders.language` aus `$_SESSION['language']` und `orders.languages_id` aus `$_SESSION['languages_id']`. Fuer das Modul ist `orders.language` die massgebliche Bestellsprache. Sie bestimmt den Hinweis-Snapshot beim Anlegen der Bestellung und den `garan_terms`-Anhang beim Einfuegen einer Position. Die Sprache des Kunden wird nicht automatisch ermittelt. Soll eine englische Bestellung entstehen, muss der Admin die Bestellung in einer englischen Backend-Sitzung anlegen. Eine spaetere Aenderung der Bestellsprache erzeugt oder ersetzt vorhandene Snapshots nicht automatisch.

Beim Anlegen der leeren Bestellung wird `orders_guarantee` sofort mit dem historischen Gewaehrleistungshinweis der Bestellsprache befuellt, wenn das Modul aktiv ist, die Kundengruppe nicht als B2B ausgeschlossen ist und die Sprache vollstaendig gepflegt ist. Gleichzeitig werden `notice.svg` und `notice.json` unter dem berechneten `notice_hash` archiviert. Die Bestellung braucht zu diesem Zeitpunkt noch keine Positionen. Dadurch stehen Mailtext und Link bereits fuer die vorhandene Admin-Funktion `Auftragsbestaetigung senden` bereit.

Enthaelt die manuelle Bestellung spaeter ausschliesslich virtuelle Ware, bleibt der Snapshot gespeichert, wird aber nicht in der Auftragsbestaetigung ausgegeben. Bei physischer oder gemischter Ware wird er ausgegeben. Fehlen die Voraussetzungen beim Anlegen der Bestellung, entsteht keine Zeile; eine spaetere Aenderung von Modulstatus, Kundengruppe oder Sprachdateien erzeugt den historischen Snapshot nicht automatisch.

Beim Einfuegen eines Katalogartikels muss der Admin-Ablauf deshalb ebenfalls folgende Werte aus den aktuellen Artikeldaten laden und als Snapshot speichern:

```text
orders_products_guarantee.manufacturers_name
orders_products_guarantee.manufacturers_model
orders_products_guarantee.garan_duration
orders_products_guarantee.garan_hash
orders_products_guarantee.terms_hash
orders_products_guarantee.terms_filename
```

Dafuer gelten folgende Regeln:

- Checkout und Admin verwenden dieselbe zentrale Snapshot- und Validierungslogik.
- Der Admin-Ablauf darf nicht vom Hook `includes/extra/checkout/checkout_process_products_end/` abhaengen.
- Bei fehlenden oder ungueltigen GARAN-Daten entsteht keine Zeile in `orders_products_guarantee`.
- Spaetere Aenderungen am Katalogartikel aktualisieren eine bestehende Bestellposition nicht automatisch.
- Wird ein Artikel aus der Bestellung entfernt und erneut eingefuegt, werden die zu diesem Zeitpunkt aktuellen Artikeldaten uebernommen.
- Die Bestellbearbeitung zeigt die GARAN-Snapshotwerte an und erlaubt eine ausdrueckliche Korrektur mit derselben Validierung wie die Artikelverwaltung.
- Ein erneuter Mailversand verwendet ausschliesslich die Werte der Bestellposition.
- Eine frisch manuell angelegte Bestellung hat in `orders.content_type` den leeren Wert `''`, weil `admin/customers.php` das Feld nicht befuellt. Das Modul verlaesst sich deshalb nicht auf diese Spalte und schreibt sie auch nicht.
- Ob eine Bestellung koerperliche Ware enthaelt, beantwortet `guarantee_labels_order_physical()`. Ist `orders.content_type` gefuellt, entscheidet dieser Wert: Der Checkout hat die Bestellung bereits eingestuft und kennt dabei die gewaehlten Attribute, die eine einzelne Position `mixed` machen koennen.
- Nur bei leerem `orders.content_type`, also bei einer manuell angelegten Bestellung, entscheiden die Positionen. Je Position werden ihre Attribute und ihre Downloadzeilen gezaehlt, nicht verknuepft: Eine Position ohne Downloadzeile ist koerperliche Ware; hat sie Downloadzeilen, aber mehr Attribute als Downloads, ist sie gemischt und zaehlt ebenfalls als koerperlich. Bei `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED = true` macht schon eine Downloadzeile die ganze Position digital. Die Regel folgt damit `shopping_cart::get_content_type()`.
- Dieselbe Zaehlung beantwortet die Frage auch je Position. `guarantee_labels_order_position_types()` liefert eine Bestellung mit einer Abfrage als Zuordnung von `orders_products_id` auf Ware ja/nein; `guarantee_labels_order_physical()` fragt nur, ob darin ein `true` steht. Damit steht die Regel an genau einer Stelle.
- Fuer einen vorhandenen Snapshot entscheidet nur der Modulstatus, `guarantee_labels_order_active()`. Die Kundengruppe wird nicht erneut gefragt: Dass der Snapshot existiert, ist bereits die Antwort darauf. Sonst verloere ein Kunde, den der Shopbetreiber spaeter einer B2B-Gruppe zuordnet, die Garantieangaben seiner alten Bestellung im Kundenkonto, waehrend der Admin sie mit Gruppe `0` weiterhin versendet. `guarantee_labels_active()` mit Gruppenpruefung gilt weiter fuer alles, was aus dem Katalog kommt.
- Bei einer Checkout-Bestellung gilt die Einstufung von damals nur fuer einen Snapshot, den es schon gibt. Fuer eine Position ohne Snapshot sagt `orders.content_type` nichts aus: `mixed` gilt fuer die ganze Bestellung, nicht fuer die einzelne Position, und wuerde einer reinen Downloadposition sonst nachtraeglich eine Zusage per Hand erlauben. Dann entscheidet die Position selbst.
- Wer ueberhaupt entscheidet, beantwortet `guarantee_labels_order_by_position()` und sonst niemand. Bei gefuelltem `orders.content_type` gilt die Einstufung des Checkouts, bei leerem entscheiden die Positionen. Diese Trennung ist zwingend: `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` laesst sich jederzeit umschalten, und eine Neueinstufung bei jeder Ausgabe wuerde die Geschichte abgeschlossener Bestellungen umschreiben. Ein erneuter Mailversand darf nicht davon abhaengen, wie der Shop heute eingestellt ist.
- Eine Position einer manuell angelegten Bestellung kann dagegen nachtraeglich digital werden, im Admin durch das Hinzufuegen eines Downloadattributs. Die gespeicherte Zeile in `orders_products_guarantee` bleibt als Historie erhalten, wird aber nicht mehr ausgegeben: `guarantee_labels_order_products()` laesst solche Positionen weg. Label, Bestelltext und Mailanhang haengen alle an dieser einen Funktion und kommen dadurch zwangslaeufig zur selben Aussage. Bei einer Checkout-Bestellung entfernt stattdessen die Bestellbearbeitung den Snapshot von Hand; das ist der ausdrueckliche Weg.
- Ein Snapshot, dessen Position in der Typabfrage gar nicht vorkommt, faellt in jedem Fall heraus. Es gibt keine Fremdschluessel, ein fremder Loeschweg kann eine verwaiste Zeile hinterlassen, und deren Dokument darf nicht in eine Mail geraten.
- Ob eine einzelne Position Ware ist, beantwortet `guarantee_labels_order_position_goods()`, und Lesen wie Schreiben fragen dieselbe Funktion. Sonst bliebe eine ausgeblendete Position bearbeitbar oder eine sichtbare liesse sich nicht mehr speichern. Bei einer Checkout-Bestellung folgt die Position der Einstufung der Bestellung, eine gemischte bleibt also korrigierbar, auch wenn `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` inzwischen anders steht.
- Sind Downloads ueber `DOWNLOAD_ENABLED` abgeschaltet, ist jede Position Ware. Eine liegengebliebene Zeile in `orders_products_download` darf die Zusage dann nicht wegnehmen; `shopping_cart::get_content_type()` fragt in diesem Fall ebenfalls nichts.
- Die Bestellbearbeitung liest ueber `guarantee_labels_order_snapshots()` ungefiltert, sonst waere die Zeile einer digital gewordenen Position weder sichtbar noch loeschbar. Sie meldet in diesem Fall `ERROR_GUARANTEE_LABELS_SNAPSHOT_VIRTUAL`, und `guarantee_labels_write_snapshot()` verweigert das Speichern; das Entfernen bleibt erlaubt. Geprueft wird bereits vor dem Archivieren, mit derselben Bedingung wie beim Schreiben: Erst dort zu pruefen liesse eine hochgeladene Datei je Versuch ohne Verweis im Archiv zurueck.
- Der Mailweg loest mehrsprachige Anhangswerte selbst auf, bevor er die Garantiebedingungen anhaengt. `EMAIL_BILLING_ATTACHMENTS` kann `de::a.pdf||en::b.pdf` lauten; `xtc_php_mail()` loest das erst spaeter auf, ein Anhaengen an den rohen Wert landete nur im letzten Sprachabschnitt und jede andere Sprache verloere die Bedingungen. `guarantee_labels_order_language_code()` liefert dafuer die Sprache der Bestellung, dieselbe, die `xtc_php_mail()` verwendet.
- Auch der Gewaehrleistungshinweis haengt an dieser Regel: `guarantee_labels_order_notice()` gibt fuer eine Bestellung ohne Ware nichts zurueck. Mail und Kundenansicht lesen durch dieselbe Funktion und koennen deshalb nicht auseinanderlaufen.
- Ein `LEFT JOIN` auf `orders_products_download` reicht dafuer nicht: Eine Position mit einem Download- und einem koerperlichen Attribut traegt eine Downloadzeile und saehe darin rein digital aus.
- Gezaehlt wird ausschliesslich in den Bestelldaten: die Attribute der Position und ihre Zeilen in `orders_products_download`. Der Katalog wird nicht gefragt. Er wuerde fuer heute antworten und einen geloeschten Artikel oder ein geloeschtes Attribut nachtraeglich in die Einstufung einer alten Bestellung tragen; genau das soll ein Snapshot ausschliessen.
- Dass die Bestelldaten dafuer stimmen, setzt voraus, dass `orders_products_download` beim Bearbeiten einer Position gepflegt wird. Der Bestand tat das nicht: `orders_product_option_insert()` legt eine Zeile an, `orders_product_option_delete()` liess sie stehen. `orders_product_downloads_cleanup()` raeumt sie nach dem Loeschen eines Attributs auf; beim Loeschen einer ganzen Position verschwinden ihre Zeilen ebenfalls. Das ist ein allgemeiner Kernfehler und liegt zusaetzlich als eigener Branch mit eigenem Pull Request vor.
- Das Aufraeumen loescht ausschliesslich, schreibt nie und fragt den Katalog gar nicht:
  - Keine Downloadzeile auf der Position: nichts zu tun und nichts zu melden.
  - Kein Attribut mehr: Jede Zeile ist verwaist, alle werden entfernt.
  - Attribute und Zeilen verbleiben: Welche Zeile zu welchem Attribut gehoert, ist nicht feststellbar. Es wird nichts geloescht und der Shopbetreiber erhaelt eine Meldung ueber `messageStack->add_session()`, weil `admin/orders_edit.php` anschliessend weiterleitet.
- Keiner der beiden denkbaren Schluessel taugt zur Entscheidung. Der Dateiname steht nur im Katalog, der sich unabhaengig von der Bestellung aendert. Der Optionsname der Bestellung liegt zwar in `orders_products_attributes.products_options` und die Attributverwaltung bietet die Downloadfelder nur fuer eine Option namens `Downloads` an; ein Shopbetreiber kann das Attribut einer Bestellung aber umbenennen, und ein dann nicht erkannter Name liesse die Position so aussehen, als haette sie gar kein Downloadattribut mehr. Alle gueltigen Zeilen wuerden geloescht. Ein Fehlgriff darf nie die zerstoerende Richtung sein. Dasselbe gilt fuer Shops, deren Option je Adminsprache anders heisst.
- Zaehlen loest den haeufigsten Fall deshalb nicht: Wird das einzige Downloadattribut einer Position geloescht, die auch ein koerperliches traegt, bleiben ein Attribut und eine Zeile uebrig, und es wird nichts entfernt. Die Meldung erscheint dafuer immer, sobald Zeilen und Attribute nebeneinander bestehen bleiben. Erst der Verweis aus Issue #3280 kann eine einzelne Zeile einem Attribut zuordnen.
- Ueber den Dateinamen zu entscheiden waere unsicher. Er steht nur im Katalog, und der aendert sich unabhaengig von der Bestellung: Wird einem frueher koerperlichen Attribut spaeter die Datei eines anderen zugeordnet, wuerde beim Loeschen des einen das Downloadrecht des anderen verschwinden. Zwei Attribute, die dieselbe Datei fuehren, sind ueber den Namen ohnehin nicht zu unterscheiden.
- Eine verwaiste Zeile ist der harmlose Fehler, ein entzogenes Downloadrecht nicht. Bleibt eine Zeile stehen, gilt die Position weiterhin als digital und der Gewaehrleistungshinweis entfaellt dort, wo er haette erscheinen muessen. Das trifft den Ablauf `koerperliches und digitales Attribut, digitales geloescht` und damit keinen Randfall. Die Meldung macht ihn sichtbar, loest ihn aber nicht.
- `orders_product_option_edit()` ruft das Aufraeumen ausdruecklich nicht auf. Die Maske aendert Bezeichnung, Preis und Gewicht der Bestellposition, nie das zugrunde liegende Attribut; ein Eingriff waere dort reines Risiko ohne Anlass.
- Sobald mindestens eine koerperliche Position existiert, gilt die Bestellung als koerperlich. Eine Bestellung ohne Positionen gilt als nicht koerperlich.
- Das Modul schreibt die Spalte nie. Eine spaetere Positionsaenderung wirkt sofort, weil nichts zwischengespeichert wird, das nachgezogen werden muesste.
- Der Gewaehrleistungshinweis wird nur ausgegeben, wenn diese Pruefung koerperliche Ware findet.
- `asset_hash()` bildet Groesse und Aenderungszeit der Vorlagen, Schriften und Browserdateien ab, nicht deren Inhalt. Ein Inhaltshash haette auf jeder Seite mit einem Label 1,4 MB gelesen. Eine ersetzte Datei aendert beides, und eine bewusste Aenderung der Zeichnung deckt `RENDERER_VERSION` ab.

Nach dem Insert in `orders_products` entsteht der GARAN-Snapshot der manuell eingefuegten Position. `orders_product_insert()` in `admin/includes/functions/orders_functions.php` bekommt dafuer am Ende die Erweiterungsstelle `orders_functions/product_insert/`, passend zu den bereits vorhandenen `product_edit/` und `product_delete/`. Die neue `orders_products_id` steht dort als `$orders_products_id` bereit. Die GARAN-Logik selbst liegt in `admin/includes/extra/modules/orders/orders_functions/product_insert/guarantee_labels.php`.

Der Grundsatz, so wenig wie moeglich an Kerndateien zu aendern, spricht fuer diesen Weg und nicht dagegen: Die Kerndatei bekommt eine Zeile nach dem Muster ihrer eigenen Nachbarfunktionen, die Modullogik bleibt vollstaendig im Modul, und eine Deinstallation laesst nichts zurueck.

Die GARAN-Ergaenzungen in den allgemeinen Admin-Funktionen pruefen vor jedem Lesen, Schreiben oder Loeschen, ob die jeweilige Modultabelle existiert. Neue Snapshots entstehen zusaetzlich nur bei aktivem Modul. Dadurch bleiben das manuelle Anlegen und Bearbeiten von Bestellungen funktionsfaehig, wenn das Modul nie installiert oder spaeter deinstalliert wurde.

Der Hinweis-Snapshot in `orders_guarantee` wird beim Anlegen der manuellen Bestellung gesetzt und durch spaetere Positionsaenderungen nicht neu berechnet. Der Mailversand liest nur den gespeicherten Stand und berechnet nichts aus aktuellen Katalog- oder Moduleinstellungen neu.

Ein besonderer Sperrzeitpunkt ist nicht erforderlich. Die GARAN-Daten werden bereits beim Einfuegen des Artikels in `orders_products_guarantee` uebernommen. Der erste Versand der Auftragsbestaetigung veraendert den Snapshot nicht. Bewusste spaetere Korrekturen in der Bestellbearbeitung bleiben moeglich und werden protokolliert.

#### Bearbeitung der GARAN-Daten einer Bestellung

Die Bestellbearbeitung zeigt bei jeder Position einen eigenen Bereich `EU-Haltbarkeitsgarantie` mit folgenden Feldern:

- Hersteller/Garantiegeber aus `orders_products_guarantee.manufacturers_name`.
- Hersteller-Modellkennung aus `orders_products_guarantee.manufacturers_model`.
- Garantiedauer aus `orders_products_guarantee.garan_duration`.
- Optional archivierte Garantieerklaerung aus `orders_products_guarantee.terms_hash` und `terms_filename` mit Ersetzen- und Entfernen-Funktion. Die Maske nennt den Dateinamen; ein Download aus dem Admin ist nicht vorgesehen. Das Archiv ist fuer HTTP gesperrt, ein Auslieferungsweg mit eigener Rechtepruefung waere dafuer noetig und bringt gegenueber dem Dateinamen keinen Erkenntnisgewinn.

Die Bearbeitung gilt fuer Bestellungen aus dem Storefront und fuer manuell angelegte Bestellungen.

#### Weitere Ansichten einer Bestellung

Dieselben Snapshotwerte stehen ueberall bereit, wo `getOrderData()` die Positionen liefert. Ausgegeben wird je nach Zweck unterschiedlich:

- Bestellansicht im Admin (`admin/includes/modules/orders_info_blocks.php`): die Zusage als Textzeile, ohne den Anhang.
- Bestellung drucken (`admin/print_order.php`): ebenso als Textzeile, ohne den Anhang. Der Beleg fuehrt keine Datei mit. Die Seite baut ihre Positionen ueber `getOrderData()` auf und traegt `GUARANTEE_TEXT_HTML` deshalb schon; ausgegeben wird sie in `templates/*/admin/print_order.html`, so wie die Kundenfassung es in `templates/*/module/print_order.html` tut.
- Bestellansicht im Kundenkonto (`account_history_info.php`): das GARAN-Label als Grafik und dazu der Gewaehrleistungshinweis der Bestellung, im Aufbau des jeweiligen Templates neben Versandart und Zahlungsweise. Beides stammt aus dem Archiv der Bestellung, nicht aus dem Katalog und nicht aus den heutigen Sprachdateien: Der Kunde sieht die Kennzeichnung und den Wortlaut, die zu seiner Bestellung gehoeren. Fehlt eine Cachekopie, wird sie aus dem Archiv wiederhergestellt, weil das Archiv fuer HTTP gesperrt ist.
- Auftragsbestaetigung: Zusage und Dateiname der beigefuegten Garantiebedingungen, weil die Datei dort tatsaechlich mitgeht.

Die Statuswechselmail (`admin/includes/modules/orders_update.php`) gibt keine Positionen aus und bleibt unveraendert.

Das Systemmodul `order_mail_step` ersetzt die Auftragsbestaetigung durch `order_mail_step.html` und `order_mail_step.txt`. Diese Vorlagen erhalten dieselben Bloecke wie `order_mail`, sonst versendet ein Shop mit aktivem Modul eine Bestaetigung ohne den Gewaehrleistungshinweis.

Regeln:

- Aenderungen betreffen nur den Bestellsnapshot. Der Katalogartikel bleibt unveraendert.
- Herstellername, Hersteller-Modellkennung, Garantiedauer und `garan_hash` sind ein zusammengehoeriger Datensatz. Die Zeile in `orders_products_guarantee` enthaelt diese Kerndaten vollstaendig oder existiert nicht. Leert der Admin die Kerndaten, wird die Zeile geloescht.
- Die Garantiedauer muss groesser als `2.0` sein und auf `.0` oder `.5` enden.
- Bei gefuellter Garantiedauer sind Herstellername und Hersteller-Modellkennung Pflichtfelder.
- Eine Garantieerklaerung ist optional. Beim Ersetzen wird die neue Datei archiviert und `terms_hash` sowie `terms_filename` werden neu bestimmt. Beim Entfernen werden beide Felder auf `NULL` gesetzt; das GARAN-Label bleibt aktiv.
- `products_model` bleibt von der Hersteller-Modellkennung getrennt.
- Vor dem Speichern zeigt der Admin eine Vorschau des GARAN-Labels, das aus den Werten in der Maske entstehen wuerde, nicht des gespeicherten Snapshots. Sonst bestaetigte die Vorschau eine Aenderung, die noch gar nicht vorgenommen wurde.
- Beim Speichern werden `garan_hash` und die zugehoerige Archivgrafik neu bestimmt.
- Eine ausdrueckliche Aktion `Aus Artikeldaten uebernehmen` ersetzt die Snapshotwerte durch die aktuell gepflegten Katalogwerte. Ein in der Bestellsprache vorhandener Artikel-Anhang wird ebenfalls uebernommen; fehlt er, werden `terms_hash` und `terms_filename` auf `NULL` gesetzt. Sonst traege die Position aktuelle Kerndaten neben den Garantiebedingungen eines aelteren Stands. Der Artikel wird dabei ueber `orders_products.products_id` der bearbeiteten Position bestimmt und nicht ueber eine Artikelnummer aus dem Request.
- Es erfolgt keine automatische Synchronisierung mit dem Katalog.
- Bei bereits an den Kunden gesendeten Bestellungen muss der Admin die Aenderung gesondert bestaetigen.
- Jede Aenderung wird mit Zeitpunkt, Admin-Benutzer sowie alten und neuen Werten in der Bestellhistorie protokolliert.
- Ein erneuter Mailversand verwendet die korrigierten Snapshotwerte.

Eine Aenderung wird erst in `orders_products_guarantee` gespeichert, nachdem alle dafuer erforderlichen neuen Archivdateien vollstaendig geschrieben und geprueft wurden. Schlaegt die Erzeugung, Archivierung oder Pruefung fehl, bleibt der bisherige Snapshot einschliesslich eines vorhandenen Garantie-Anhangs unveraendert erhalten. Die Aenderung wird nicht teilweise gespeichert. Der Admin erhaelt die konkrete Fehlermeldung ueber den `messageStack`.

Bestehende Bestellungen erhalten bei der Modulinstallation keine Zeilen in den neuen Tabellen. Es findet keine automatische Rueckbefuellung aus aktuellen Katalogdaten statt. Der Admin kann die Werte bei Bedarf manuell erfassen oder ausdruecklich aus dem Artikel uebernehmen.

#### Versand bei manuell angelegten Bestellungen

Fuer manuell angelegte Bestellungen werden keine neuen Routinen fuer den Mailversand eingefuehrt.

- Der Versand erfolgt ausschliesslich ueber die vorhandene Funktion `Auftragsbestaetigung senden`.
- Das System versendet beim manuellen Anlegen oder Bearbeiten einer Bestellung keine automatische Mail.
- Der Admin entscheidet, ob und wann er die Auftragsbestaetigung sendet.
- Die bestehende Auftragsbestaetigung enthaelt den sprachabhaengigen Gewaehrleistungstext mit direktem Link sowie vorhandene archivierte Garantiebedingungen der betroffenen Bestellpositionen.
- Die Ausgabe verwendet die gespeicherten Bestellsnapshots.
- Ein erneuter Versand nutzt denselben bestehenden Ablauf und die zu diesem Zeitpunkt gespeicherten Snapshotwerte.

Die Verantwortung fuer den rechtzeitigen Versand der Auftragsbestaetigung liegt beim Admin.

## Bestellbestaetigung

### Leseweg

Die Bestelldaten fuer Mailausgabe und Bestellansicht kommen aus `includes/classes/order.php`. Die Positionen werden dort per `SELECT *` aus `orders_products` geladen. `order.php` wird dafuer nicht veraendert. Die Snapshotwerte holt das Modul selbst ueber zwei zentrale Funktionen in `inc/guarantee_labels_order.inc.php`:

- `guarantee_labels_order_products()` liest alle Zeilen aus `orders_products_guarantee` einer Bestellung mit genau einer Abfrage und merkt sie sich fuer die Dauer des Requests. Jede Ausgabestelle greift danach auf denselben Puffer zu, unabhaengig davon, wie oft sie aufgerufen wird.
- `guarantee_labels_order_notice()` liest `notice_hash` aus `orders_guarantee` und daraus den archivierten Sprachstand; die Existenz der Zeile bedeutet, dass der Bestellung ein historischer Hinweis-Snapshot zugeordnet ist.

Ein LEFT JOIN in der Kernabfrage waere der zweite Weg gewesen, haette aber `order.php` geaendert und die Modulspalten in jede Bestellansicht getragen, auch wo sie niemand liest. Die eigene gepufferte Abfrage kostet je Bestellung eine Abfrage und laesst die Kernklasse unberuehrt.

Wurde das Modul nie installiert und fehlen die Tabellen, unterbleiben beide Abfragen vollstaendig. Die Tabellenexistenz wird hoechstens einmal je Anfrage ermittelt und fuer weitere GARAN-Zugriffe derselben Anfrage wiederverwendet.

Ein allgemeiner Status `hat rechtliche Hinweise` wird bei Bedarf aus diesen beiden Modulabfragen gebildet. Er wird nicht redundant in `orders` gespeichert.

Beides gilt gleichermassen fuer die erste Bestellbestaetigung, den erneuten Versand und die Bestellansicht im Admin.

### HTML-Mail

- Historischen sprachabhaengigen Gewaehrleistungstext aus `media/guarantee_labels/archive/notice/<notice_hash>/notice.json` aufnehmen.
- Historischen Gewaehrleistungsstatus aus `orders_guarantee` verwenden.
- Historischen Linktext, die historische Your-Europe-URL und die historische Ueberschrift aus demselben Snapshot bereitstellen. Fuehrt die `notice.json` kein Feld `title`, greift nur die Ueberschrift auf die Sprachdatei der Bestellsprache zurueck; Text und Link bleiben historisch.
- Weder `notice.svg` noch eine daraus erzeugte Rastergrafik einbetten oder anhaengen. Der Grund ist praktisch: SVG ist in Mailprogrammen kein verlaesslich darstellbares Format. Ein Anhang, den der Empfaenger nicht oeffnen kann, erfuellt den Zweck des Hinweises nicht besser als der Text, sondern schlechter. Der rechtlich massgebliche Wortlaut und der Link zur Your-Europe-Seite gehen als Text mit und liegen damit auf einem dauerhaften Datentraeger.
- Kein GARAN-Label einbetten oder als Grafik anhaengen.
- Vorhandene, fuer die Bestellung archivierte Garantiebedingungen ueber denselben Anhangsmechanismus beifuegen. Fehlen sie, wird kein Ersatzdokument erzeugt und kein GARAN-Anhang versendet.

### Text-Mail

Die Text-Mail enthaelt mindestens:

- Hinweis auf das gesetzliche Gewaehrleistungsrecht.
- Direkten Link zur passenden Your-Europe-Seite.

Das GARAN-Label wird weder als Grafik noch als Nachbildung seines Aufbaus in die Mail uebernommen. Die Zusage dahinter steht dagegen als schlichte Zeile an der Bestellposition: Garantiedauer, Herstellername und Modellkennung aus dem Snapshot, dazu der Dateiname der beigefuegten Garantiebedingungen. Ohne diese Zeile bliebe der Anhang ohne Bezug zu einer Position. Vorhandene Garantiebedingungen werden wie bei der HTML-Mail als archivierte Dateien angehaengt. Fehlen sie, enthaelt auch die Text-Mail keinen GARAN-Anhang.

Die Datenvorbereitung kann ueber folgende Erweiterungsstelle erfolgen:

```text
includes/extra/send_order/data/
```

Die Mailtemplates benoetigen eigene Smarty-Platzhalter.

## Sprachen und Grafiken

Die EU stellt den Gewaehrleistungshinweis in allen 24 Amtssprachen bereit. Die Erweiterung liefert selbst davon ausschliesslich folgende Sprachen vollstaendig aus:

- Deutsch.
- Englisch.

Der Gewaehrleistungshinweis liegt unter `lang/<Sprachverzeichnis>/notice.svg`, konkret z. B. unter `lang/german/notice.svg` und `lang/english/notice.svg`. Ein spaeter installiertes Sprachpaket kann seine offizielle Grafik an derselben Stelle mitbringen. Die Artikel muessen dafuer weder geoeffnet noch neu gespeichert werden.

Eine Sprache ist fuer den Gewaehrleistungshinweis nur vollstaendig gepflegt, wenn sie `lang/<Sprachverzeichnis>/notice.svg` und alle Konstanten aus `guarantee_labels_notice_constants()` bereitstellt:

- `TEXT_GUARANTEE_NOTICE_TITLE` als Ueberschrift des Blocks,
- `TEXT_GUARANTEE_NOTICE_TEXT` fuer Storefront und Checkout,
- `TEXT_GUARANTEE_NOTICE_MAIL` fuer die Auftragsbestaetigung,
- `TEXT_GUARANTEE_NOTICE_LINK` als Linktext,
- `TEXT_GUARANTEE_NOTICE_URL` als sprachabhaengige Your-Europe-Adresse,
- `TEXT_GUARANTEE_NOTICE_OPEN` und `TEXT_GUARANTEE_NOTICE_ALT` fuer die vollstaendige Ansicht,
- `TEXT_GUARANTEE_NOTICE_MIXED` fuer die Zuordnung bei gemischten Warenkoerben. Ein gemischter Warenkorb ist keine Eigenschaft der Sprache, sondern des Shops; die Konstante getrennt zu pruefen hiess, dass die Diagnose eine Sprache als vollstaendig meldete, der gemischte Checkout aber stumm blieb und die Bestellung trotzdem einen Snapshot bekam.

Moduldiagnose, Checkout und Snapshoterzeugung fragen genau diese eine Liste. Getrennt gepflegte Listen sind auseinandergelaufen: Die Diagnose meldete eine Sprache als vollstaendig, die der Checkout dann ablehnte, und der Checkout konnte stumm bleiben, waehrend die Bestellung trotzdem einen Snapshot bekam. Die Labelkonstanten gehoeren ausdruecklich nicht dazu; das Label ist sprachneutral und faellt auf eigene Beschriftungen zurueck.

Die mitgelieferten Storefront- und Mailkonstanten liegen in diesen automatisch geladenen Dateien:

```text
lang/german/extra/guarantee_labels.php
lang/english/extra/guarantee_labels.php
```

Sie definieren mindestens `TEXT_GUARANTEE_NOTICE_MAIL`, `TEXT_GUARANTEE_NOTICE_LINK` und `TEXT_GUARANTEE_NOTICE_URL`. Alle Storefront-Konstanten des Moduls tragen das Praefix `TEXT_GUARANTEE_`; `MODULE_GUARANTEE_LABELS_` bleibt der Modulkonfiguration vorbehalten. Storefront-Requests laden diese Dateien ueber die vorhandene `extra/`-Sprachlogik. Adminroutinen, die einen Hinweis-Snapshot erzeugen, laden die Datei aus `lang/<orders.language>/extra/guarantee_labels.php` ausdruecklich, weil die Admin-Sprachinitialisierung die Storefront-Datei nicht automatisch einbindet. Admintexte ausserhalb der Modulverwaltung liegen entsprechend unter `lang/german/extra/admin/guarantee_labels.php` und `lang/english/extra/admin/guarantee_labels.php`.

`guarantee_labels_language()` gibt bereits geladene Konstanten nur frei, wenn sie zur angefragten Sprache gehoeren. Im Storefront stammen sie aus der Sprache der Sitzung, also der Sprache, in der der Kunde gerade blaettert, und nicht zwingend aus der Bestellsprache.

Ein solcher Fehlschlag kostet aber nie historische Daten. Die Bestellansichten laden die Bestellsprache nur versuchsweise und geben in jedem Fall aus:

- Das sprachneutrale GARAN-Label erscheint unveraendert; nur seine Beschriftungen fallen auf `GARAN` zurueck.
- Gewaehrleistungstext, Linktext, Adresse und Ueberschrift des Hinweises stammen aus `notice.json` und sind damit immer der Stand der Bestellung. Nur die Bedienelemente des Blocks kaemen aus der Sprache der Sitzung.
- Die Zusage an der Position nennt weiter die archivierten Werte; nur der Satz um sie herum folgt der geladenen Sprache.

Die Ueberschrift des Hinweises wird deshalb im Archiv mitgefuehrt und geht wie jedes andere archivierte Feld in `notice_hash` ein. Der Hash deckt genau das ab, was in `notice.json` steht: Grafik, Sprachverzeichnis, Mailtext, Linktext, Ueberschrift, Your-Europe-URL und Version. Ein Feld auszulassen waere falsch, auch wenn es aus derselben Sprachdatei stammt: Eine geaenderte Ueberschrift ergaebe denselben Hash, das vorhandene Archiv bliebe stehen und neue Bestellungen bekaemen stillschweigend den alten Stand. Zwei Sprachen mit gleicher Grafik und gleichen Texten wuerden sich dasselbe Verzeichnis teilen und darin die falsche Sprache tragen.

`GUARANTEE_LABELS_NOTICE_VERSION` steht auf `1.01`. Bestehende Archive bleiben fuer ihre Bestellungen unveraendert gueltig; neue Bestellungen erzeugen ein neues Verzeichnis. Ob ein Archiv auf die Sprachkonstante zurueckfaellt, entscheidet allein das fehlende Feld `title` in seiner `notice.json`, nicht die Versionsnummer. Praktisch trifft das die Archive der Version `1.00`, weil erst `1.01` die Ueberschrift mitschreibt.

Die Konfigurationssprache des Systemmoduls liegt getrennt unter:

```text
lang/german/modules/system/guarantee_labels.php
lang/english/modules/system/guarantee_labels.php
```

Diese beiden Dateien definieren mindestens `MODULE_GUARANTEE_LABELS_TEXT_TITLE`, `MODULE_GUARANTEE_LABELS_TEXT_DESCRIPTION`, `MODULE_GUARANTEE_LABELS_STATUS_TITLE`, `MODULE_GUARANTEE_LABELS_STATUS_DESC`, `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_TITLE` und `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_DESC`. Damit kann der Konstruktor Titel und Beschreibung zuweisen und die vorhandene Modulverwaltung beide Konfigurationsschluessel beschriften.

Der Text kommt nicht aus dem Content Manager. HTML- und Text-Mail verwenden dieselben Sprachkonstanten und formatieren Text und Link passend zum jeweiligen Mailformat. Der Link wird nicht als HTML in den Mailtext eingebettet.

Das GARAN-Label enthaelt bereits alle erforderlichen Sprachfassungen und ist sprachneutral. Seine offiziellen Vorlagen liegen zentral unter `images/guarantee_labels/assets/` und nicht in den Sprachordnern. Die benoetigten Inter-Schriften liegen unter `images/guarantee_labels/fonts/`. Weitere Shopsprachen gehoeren nicht zum mitgelieferten Umfang der Erweiterung. Ein stiller Fallback auf Deutsch oder Englisch ist nicht vorgesehen.

Beim Anlegen einer neuen Sprache kopiert `admin/languages.php` auf Wunsch vorhandene Artikel-Anhaenge. Zeilen mit einem gesetzten `content_type` werden dabei nicht in die neue Sprache kopiert. Der Shopbetreiber muss die Garantieerklaerung fuer die neue Sprache ausdruecklich zuordnen. Anhaenge ohne Typ folgen weiterhin dem bestehenden Kopierverhalten.

Diese Stelle besitzt keine Erweiterungsstelle und wird deshalb als gezielter Eingriff in `admin/languages.php` umgesetzt. Der Eingriff nennt das Modul nicht: `products_content.content_type` ist ein Core-Feld, und ein gesetzter Typ sagt, dass diese Datei fuer genau eine Sprache bestimmt ist. Sie in eine andere Sprache zu kopieren waere unabhaengig vom Modulstatus und unabhaengig vom Typ eine falsche Zuordnung. Bei inaktivem Modul liest ohnehin niemand das Feld, ein Schaden entsteht durch die Ausnahme also nicht.

Die Moduldiagnose meldet ausserdem eine B2B-Kundengruppe der Einstellung, die es nicht mehr gibt. Die Mehrfachauswahl bietet nur vorhandene Gruppen an, eine geloeschte Gruppe laesst sich also durch Speichern nicht mehr aus der Einstellung entfernen. Ihre Id bliebe stehen und wuerde wieder ausschliessen, sobald der Shop dieselbe Nummer erneut vergibt.

Die Moduldiagnose warnt ausserdem, wenn dieselbe `content_file` in mehreren Sprachen als `garan_terms` markiert ist. Das kann bei einer bewusst mehrsprachigen Datei korrekt sein, muss vom Shopbetreiber aber geprueft werden. Die Warnung blockiert weder die Artikelpflege noch den Checkout.

Fehlt einer der vier Bestandteile, gilt der Gewaehrleistungshinweis in dieser Sprache als nicht verfuegbar. Der Checkout laeuft ohne ihn weiter, die Moduldiagnose zeigt eine Warnung und fuer die Bestellung entsteht keine Zeile in `orders_guarantee`.

Das sprachneutrale GARAN-Label bleibt davon unberuehrt und wird ausgegeben, auch wenn eine Sprache gar keine Modultexte mitbringt. Die Beschriftungen um die Grafik fallen dann auf `GARAN` zurueck, den offiziellen Namen des Labels, der keine Uebersetzung braucht. Der Link zur Your-Europe-Seite erscheint nur, wenn Adresse und Linktext beide gepflegt sind.

## Konfiguration

Vorgesehene Moduloptionen:

- Modul aktivieren oder deaktivieren.
- B2B-Kundengruppen von der Ausgabe ausschliessen.

Die ausgeschlossenen B2B-Kundengruppen werden unter folgendem Konfigurationsschluessel als kommaseparierte Kundenstatus-IDs gespeichert:

```text
MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS
```

Die Konfiguration verwendet den vorhandenen Mehrfachauswahl-Helper und die vorhandene Datenquelle fuer Kundengruppen:

```php
'xtc_cfg_multi_checkbox(\'xtc_get_customers_statuses\', \'chr(44)\','
```

`xtc_get_customers_statuses()` liefert die erwarteten Eintraege mit `id` und `text`; eine neue Auswahlfunktion oder ein eigenes Formular ist nicht erforderlich. Der leere Standardwert schliesst keine Kundengruppe aus. Mehrere IDs werden mit Komma gespeichert, beim Lesen in eindeutige positive Integerwerte normalisiert und fuer die B2B-Pruefung verwendet. Gastzugriffe gelten als B2C, solange ihre Kundengruppe nicht ausdruecklich in dieser Konfiguration als B2B ausgewaehlt wurde.

`install()` legt den Schluessel mit `configuration_value = ''` und der genannten `set_function` an. `update()` ergaenzt ihn bei einer bestehenden Installation nur, wenn er fehlt. `keys()` fuehrt ihn gemeinsam mit dem Statusschluessel auf.

Status und B2B-Kundengruppen sind die einzigen Konfigurationsoptionen und die einzigen entsprechenden Eintraege in `keys()`. Die Moduldiagnose ist keine Konfigurationsoption und besitzt keinen Konfigurationsschluessel.

Die Moduldiagnose erscheint direkt unter der vorhandenen Modul-Infobox. Sie umfasst unvollstaendig gepflegte aktive Sprachen und unvollstaendige Produktdaten. Die Sprachpruefung kontrolliert `notice.svg`, Mailtext, Linktext und Your-Europe-URL. Die Produktpruefung listet insbesondere Artikel mit gesetzter `products_garan_duration`, aber fehlender Hersteller-ID, nicht mehr vorhandenem oder inaktivem Hersteller, leerer Hersteller-Modellkennung oder ungueltiger Garantiedauer. Dadurch bleiben auch GARAN-Artikel sichtbar, deren Hersteller geloescht und deren `manufacturers_id` vom Bestand auf `''` gesetzt wurde.

Der Konstruktor setzt `$this->properties['add_content']` und fuehrt die Diagnoseabfragen ausschliesslich unter dieser Bedingung aus:

```php
isset($_GET['module'])
&& $_GET['module'] == $this->code
&& $this->check() > 0
```

`admin/module_export.php` gibt diesen Inhalt bereits nach der Infobox aus. Das GARAN-Systemmodul ist nur dort unter `set=system` verwaltbar. Eine eigene Adminseite, ein Eintrag in `filenames.php` oder ein neuer Navigationspunkt sind nicht erforderlich. Die Diagnose fasst mindestens folgende Pruefungen zusammen:

- GD-FreeType und `imagettfbbox()`,
- Schreibrechte und Zustand der Cache- und Archivpfade,
- Vollstaendigkeit der aktiven Sprachen mit Angabe der jeweils fehlenden Bestandteile aus `notice.svg`, Mailtext, Linktext und Your-Europe-URL; abgeschaltete Sprachen werden nicht geprueft,
- unvollstaendige GARAN-Produktdaten,
- dieselbe `content_file`, die in mehreren Sprachen als `garan_terms` markiert ist,
- als `garan_terms` markierte Anhaenge, die eine Kundengruppe nicht erreichen, obwohl ihr das Label gezeigt wird. Die Markierung wird beim Speichern geprueft, kann aber nachtraeglich ungueltig werden: bei abgeschaltetem Modul eingeschraenkt oder durch eine Gruppe, die aus der B2B-Auswahl entfernt wurde und damit B2C ist,
- fehlende oder beschaedigte historische Archivdateien.

`ausgewaehlt` bedeutet damit ausdruecklich, dass `$_GET['module']` vorhanden ist und exakt dem Modulcode entspricht. Ist kein Modulparameter gesetzt, kann das Framework zwar das erste Modul als `$mInfo` anzeigen, GARAN erzeugt in diesem Fall aber weder Diagnoseinhalt noch Diagnoseabfragen. Dasselbe gilt, wenn ein anderes Modul ausgewaehlt ist oder GARAN noch nicht installiert wurde.

Die Diagnose prueft zuerst Tabellen und Core-Spalten. Fehlen Schemaelemente, zeigt sie diesen Zustand an und fuehrt keine Abfrage gegen die fehlenden Tabellen oder Spalten aus. Dynamische Namen, Pfade und Meldungen werden vor der HTML-Ausgabe maskiert. Die Diagnose aendert keine Daten und blockiert weder Storefront noch Checkout.

Fuer alle Admin-Bereiche gilt einheitlich: Fehler aus einer vom Admin ausgeloesten Aktion werden ueber den bestehenden `messageStack` ausgegeben. Pfade mit anschliessendem Redirect verwenden `add_session()`, Pfade ohne Redirect verwenden `add()`. Das betrifft insbesondere Modulinstallation, Modulaktualisierung und -aktivierung, Artikel- und Anhangspflege, Import, manuelles Anlegen einer Bestellung und Bestellbearbeitung. Von diesen leitet nur der Import nicht weiter.

Fuer Ansichten gilt das nicht: Eine Bestellansicht oder ein Beleg ist keine ausgeloeste Aktion. Ein beschaedigtes oder fehlendes Archiv wird dort protokolliert, und die Moduldiagnose fuehrt es unter `Archivierte Dateien zu bestehenden Bestellungen vorhanden` shopweit auf. Beim Wiederversand der Auftragsbestaetigung aus dem Admin meldet `includes/extra/send_order/mail/guarantee_labels.php` die gesammelten Fehler zusaetzlich als Warnung; die Mail geht trotzdem hinaus. Es wird kein eigener Mechanismus fuer Admin-Fehlermeldungen eingefuehrt.

Der einheitliche Konfigurationsschluessel fuer den Modulstatus lautet:

```text
MODULE_GUARANTEE_LABELS_STATUS
```

`install()` legt diesen Schluessel nach erfolgreicher Schemaanlage mit dem Wert `true` an. Konstruktor, `check()` und `keys()` des Systemmoduls verwenden exakt denselben Namen. `remove()` entfernt die Modulkonfiguration ueber das gemeinsame Praefix `MODULE_GUARANTEE_LABELS_`, laesst aber Tabellen und Spalten bestehen.

`add_db_fields`, `define_add_select`, Storefront-Ausgaben, Checkout, das Erzeugen neuer Bestellsnapshots und die ausgabebezogenen Admin-Hooks pruefen ausschliesslich `defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true'`. Abweichende oder verkuerzte Statusnamen werden nicht verwendet. Installation, Entfernung, ein Wechsel des Statuswerts und die Bereinigung bereits vorhandener Bestelldaten muessen auch ohne aktuell aktiven Status sicher arbeiten; diese Lebenszyklus- und Aufraeumvorgaenge werden deshalb nicht durch die Aktivpruefung unterdrueckt.

Dieser Modulstatus ist der einzige Schalter fuer die Ausgabe. Ein Aktivierungsdatum und einzelne Schalter fuer Produktseite, Artikellisten, Checkout oder Bestellbestaetigung gibt es nicht.

Die drei Klassenerweiterungen fuehren deshalb kein eigenes Statusfeld in ihrer Modulmaske. Der Schluessel `MODULE_<typ>_<klasse>_STATUS` muss vorhanden sein, weil die Modullader ihn lesen, aber `keys()` gibt ihn nicht aus. Ein zweiter Schalter koennte sonst Listenlabel, Bestelldaten oder den Duplikationsschutz abschalten, waehrend das Systemmodul sich weiter als aktiv meldet. Die Moduldiagnose prueft beides, Eintrag und Status, und `Modul aktualisieren` setzt einen abgeschalteten Status zurueck auf `true`.

- Aktiv: Alle mit den vorhandenen Sprach- und Produktdaten moeglichen Ausgaben und Bestellsnapshots werden erzeugt. Eine unvollstaendig gepflegte Sprache blockiert den Checkout nicht.
- Inaktiv: Keine Ausgabe, keine Mailanhaenge und keine neuen Bestellsnapshots. Bereits vorhandene Bestellsnapshots bleiben unveraendert erhalten.
- Nach einer erneuten Aktivierung verwendet ein erneuter Mailversand die vorhandenen Bestellsnapshots.

Der Admin entscheidet bei manuell angelegten Bestellungen weiterhin selbst, ob und wann er die bestehende Auftragsbestaetigung sendet.

## Betroffene technische Bereiche

Voraussichtlich betroffen sind:

- `admin/includes/modules/system/` fuer die Modulklasse mit `code`, `version`, `install()`, `update()`, `process()`, `properties['button_update']` und `properties['add_content']`, fuer Modultabellen, die idempotente Anlage beider Core-Spalten und die Modulkonfiguration. `process()` speichert die B2B-Auswahl und leert keinen Cache.
- `admin/module_export.php` als vorhandener Verwaltungsweg fuer Systemmodule, als Aufrufer von `install()`, `update()` und `process()` sowie als Renderer von `properties['button_update']` und `properties['add_content']`; am Frameworkpfad selbst ist keine Aenderung erforderlich. `admin/modules.php` ist fuer dieses Systemmodul nicht betroffen.
- `admin/includes/functions/general.php` mit dem vorhandenen Helper `xtc_cfg_multi_checkbox()` und `inc/xtc_get_customers_statuses.inc.php` als vorhandene Datenquelle fuer die B2B-Mehrfachauswahl; an beiden Dateien ist keine Aenderung erforderlich.
- `admin/includes/extra/modules/add_db_fields/`
- `admin/includes/extra/modules/new_product/`
- `admin/customers.php` fuer den Hinweis-Snapshot beim manuellen Anlegen einer Bestellung.
- `admin/includes/modules/content_manager_products.php` fuer die Auswahl des Anhangstyps und `admin/includes/extra/modules/content_manager/action/` fuer das Schreiben der Markierung; an `admin/content_manager.php` ist keine Aenderung erforderlich.
- `admin/includes/modules/categories/guarantee_labels_product.php` als Klassenerweiterung fuer Artikelpruefung und sicheres Duplizieren, dazu ihre Sprachdateien unter `lang/<Sprache>/modules/categories/`.
- `admin/includes/classes/categoriesModules.class.php` und `admin/includes/classes/categories.php` fuer den allgemeinen Hook `insert_product_error()`; er wird getrennt von diesem Modul bereitgestellt.
- `inc/update_module_configuration.inc.php` als vorhandene Funktion fuer das Ein- und Austragen der Klassenerweiterung; an dieser Datei ist keine Aenderung erforderlich.
- `admin/languages.php` fuer das Kopieren von Artikel-Anhaengen beim Anlegen einer Sprache.
- `admin/includes/functions/orders_functions.php` mit den drei neuen Erweiterungsstellen `orders_functions/product_insert/`, `orders_functions/product_edit/` und `orders_functions/product_delete/`; sie werden getrennt von diesem Modul bereitgestellt.
- `admin/includes/modules/orders_info_blocks.php` fuer die Garantiezeile in der Bestellansicht des Admins.
- `admin/categories.php` als Aufrufer des Hooks `insert_product_error()`; er wird getrennt von diesem Modul bereitgestellt.
- `account_history_info.php` fuer Label und historischen Hinweis in der Bestellansicht des Kundenkontos.
- `_installer/includes/sql/modified.sql` und `_installer/includes/update_system.php` fuer beide Core-Spalten in Neuinstallation und Datenbankupdate.
- `admin/includes/extra/modules/import/file_layout/`, `admin/includes/extra/modules/import/insert_before/`, `admin/includes/extra/modules/export/file_layout/` und `admin/includes/extra/modules/export/export_end/` fuer `p_garan_duration`; an `admin/includes/classes/import.php` ist keine Aenderung erforderlich.
- `includes/database_tables.php`
- `inc/guarantee_labels_order.inc.php` als eigener gepufferter Leseweg fuer die Bestellsnapshots; an `includes/classes/order.php` ist keine Aenderung erforderlich.
- `includes/classes/product.php` als Quelle von `$product->default_select` fuer neue Artikel, Cross-Selling, Reverse-Cross-Selling und weitere Produktbloecke; die zusaetzlichen Spalten kommen ueber `includes/extra/define_add_select/`, an der Klasse selbst ist keine Aenderung erforderlich.
- `includes/classes/shopping_cart.php` als Quelle der Warenkorb- und Checkout-Produktdaten; die GARAN-Werte ergaenzt die Klassenerweiterung `includes/modules/order/guarantee_labels_order.php` ueber `cart_products()`, an der Klasse selbst ist keine Aenderung erforderlich.
- `inc/xtc_remove_order.inc.php`
- `includes/extra/define_add_select/` fuer `products_garan_duration`, `products_manufacturers_model` und die je Abfrage noch fehlende `manufacturers_id`.
- Eine zentrale GARAN-Hilfsfunktion unter `inc/` fuer das gesammelte Nachladen aktiver Herstellernamen.
- `includes/extra/modules/product_info_end/`
- `includes/modules/product/guarantee_labels_listing.php` als Klassenerweiterung des Modultyps `product`. Ihr `buildDataArray()` ergaenzt das kompakte Label fuer jede Artikelliste, die ueber die Produktklasse laeuft: Kategorie, Suche, Sonderangebote, neue Artikel, Cross-Selling, Reverse-Cross-Selling, ebenfalls gekaufte Artikel, Artikel einer Kategorie und kommende Artikel. An `includes/modules/default.php`, `includes/modules/product_listing.php`, `includes/modules/new_products.php`, `includes/modules/cross_selling.php` und `includes/extra/default/listing_sql/99_advanced_search_result.php` ist deshalb keine Aenderung erforderlich.
- `includes/modules/products_media.php` als vorhandener Storefront-Leseweg fuer `garan_terms`; die Datei bleibt unveraendert, weil der Typ die bestehende Ausgabe nicht beeinflusst.
- `includes/extra/checkout/checkout_process_products_end/`
- `includes/extra/checkout/checkout_process_order/`
- `includes/extra/send_order/data/`
- Produktdetailtemplates der mitgelieferten Templates.
- Templates fuer Kategorie, Suche, Sonderangebote, Cross-Selling und weitere Artikellisten, wenn `SHOW_BUTTON_BUY_NOW` aktiviert ist.
- Checkout-Templates der mitgelieferten Templates.
- HTML- und Text-Mailtemplates der mitgelieferten Templates.
- Bestehender Anhangsmechanismus der Auftragsbestaetigung.
- Bestehende Artikel-Anhaenge aus `products_content` und `media/products/`.
- Installationsschema, Datenbankupdate und idempotente Modulinstallation fuer `products.products_garan_duration` und `products_content.content_type`.
- `lang/german/extra/guarantee_labels.php` und `lang/english/extra/guarantee_labels.php` fuer Storefront, Mailtext, Linktext und Your-Europe-URL.
- `lang/german/extra/admin/guarantee_labels.php` und `lang/english/extra/admin/guarantee_labels.php` fuer GARAN-Texte in den betroffenen Adminbereichen.
- `lang/german/modules/system/guarantee_labels.php` und `lang/english/modules/system/guarantee_labels.php` fuer Titel, Beschreibung und die Beschriftungen der zwei Moduloptionen.
- CSS und JavaScript fuer die interaktive Vollansicht.
- Offizielle EU-Grafikdateien und Inter-Schriftdateien.
- `images/.htaccess` fuer den HTTP-Zugriff auf die WOFF2-Schriften.
- `cache/guarantee_labels/` fuer die gemeinsam gecachten GARAN-Varianten.
- `media/guarantee_labels/archive/` einschliesslich eigener `.htaccess` fuer historische GARAN-Dateien, Gewaehrleistungshinweise und Garantie-Anhaenge. Die uebergeordnete `media/.htaccess` sperrt nur Skriptendungen; das historische Archiv braucht eine eigene Sperre. Die Katalogfassung eines Anhangs unter `media/products/` bleibt erreichbar.
- `includes/classes/class.logger.php`, `admin/logs.php` und die vorhandene Logpflege fuer `mod_guarantee_labels_<level>_<datum>.log`.
- `admin/configuration.php` als vorhandener Weg, den Shopcache zu leeren. Das Modul leert selbst keinen Cache.

Die Vorlagen tragen generische Namen wie `cls-1` und `clippath-6`. Jede eingebettete Grafik bekommt deshalb ein eigenes Praefix, sonst wirkte der Clip-Pfad des einen Labels auf das andere. Das gilt fuer beide Wege: `guarantee_labels_inline_svg()` auf dem Server und `prefixSvg()` in `guarantee_labels.js`, das die per `fetch` geholte Grafik vor dem Einsetzen genauso behandelt. Beide Wege koennen im selben Dokument nebeneinander stehen, etwa ein historisches und ein aktuelles Label.

## Vertrag mit den Kernfunktionen

Jede Kernfunktion, an die das Modul einen Wert uebergibt, macht danach etwas damit. Wer das nicht
zu Ende liest, baut einen Fehler, den erst der Betrieb zeigt. Die folgenden Faelle sind geprueft
und begruenden jeweils eine Stelle im Code.

| Kernfunktion | Was sie mit dem Wert macht | Folge fuer das Modul |
|---|---|---|
| `check_attachments()` in `inc/xtc_php_mail.inc.php` | zerlegt die Liste mit `explode(',', ...)` und schneidet jeden Pfad mit `trim()` | Ein Anhangsname darf kein Komma und kein aeusseres Leerzeichen tragen. Die Regel steht in `guarantee_labels_archive::usable_filename()`. |
| `parse_multi_language_value()` | loest `de::a.pdf||en::b.pdf` erst in `xtc_php_mail()` auf | Der Mailhaken loest den konfigurierten Wert selbst auf, bevor er anhaengt. Sonst landeten die Bedingungen nur im letzten Sprachabschnitt. |
| `xtc_php_mail()` | waehlt die Sprache ueber `orders.language`, nicht ueber die Sitzung | `guarantee_labels_order_language_code()` leitet dieselbe Sprache ab. |
| `xtc_db_perform()` | behandelt die Zeichenkette `null` als SQL-`NULL`, ebenso `now()` | `terms_hash` und `terms_filename` werden mit `'null'` geleert. Ein Dateiname `null` ohne Endung waere ohnehin abgelehnt. |
| `xtcPrice::get_content_type_product()` | faltet `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` in den Artikel: eine Downloadvariante macht den ganzen Artikel virtuell | Fuer die Artikelfrage nicht verwendbar, `guarantee_labels_product_physical()` zaehlt selbst. |
| `shopping_cart::get_content_type()` | fragt bei `DOWNLOAD_ENABLED != 'true'` gar nichts, jede Position ist Ware | `guarantee_labels_order_position_types()` folgt dem. |
| `xtc_get_customers_statuses()` | liefert auch Gruppe `0`, die Adminguppe | Sie ist keine Kundengruppe: aus der Sichtbarkeitspruefung ausgeschlossen, im Speicherweg verworfen. |
| `orders_product_delete()` | loescht die Position selbst mit `orders_id` UND `orders_products_id`, der Haken laeuft davor | `guarantee_labels_product_snapshot_delete()` bekommt beide Werte und loescht ebenso eng; ein falsches Paar naehme sonst den Snapshot einer fremden Bestellung mit. |
| `admin/categories.php` | leitet nach `insert_product` und `update_product` weiter | Die Klassenerweiterung meldet mit `add_session()`. |
| `admin/orders_edit.php` | leitet nach jeder Positionsaktion weiter | Die Bestellbearbeitung meldet mit `add_session()`. |
| Import in `admin/includes/modules/import.php` | leitet nicht weiter | Der Importhaken meldet mit `add()`. |
| `categoriesModules`, `productModules`, `orderModules` | laden eine Erweiterung nur bei eigenem `_STATUS = true` | Der Status steht nicht in der Modulmaske, `Modul aktualisieren` setzt ihn zurueck, die Diagnose prueft ihn mit. |
| `admin/includes/classes/categories.php::duplicate_product()` | kopiert alle Produktspalten mit `SELECT *` und bei `cnt_copy` alle Anhaenge | Nur `duplicate_product_before()` und `duplicate_product_end()` koennen das verhindern; deshalb bleibt die Kategorieerweiterung bei vorhandenen Katalogdaten auch nach einer Deinstallation eingerichtet. |
| `SHOW KEYS` / `SHOW COLUMNS` | MariaDB weist `ORDER BY` bei `SHOW` als Syntaxfehler zurueck, MySQL nimmt es an | `index_exists()` fragt ohne `ORDER BY` und sortiert in PHP nach `Seq_in_index`. Der Shop laeuft ueberwiegend auf MariaDB; die Abfrage scheiterte dort still und meldete alle Indizes als fehlend. |
| `xtc_cfg_multi_checkbox()` | nimmt einen Funktionsnamen oder ein fertiges Array; `admin/module_export.php` wertet `set_function` per `eval` aus | Der gespeicherte Ausdruck filtert Gruppe `0` mit `array_diff_key()` heraus, also ohne neue Funktion und ohne Kerneingriff. `update()` zieht den Ausdruck bei aelteren Installationen nach. |

Diese Tabelle ist Teil der Abnahme: Eine neue Uebergabe an eine Kernfunktion gehoert hier hinein,
bevor sie im Code steht.

## Mechanisch gepruefte Regeln

Diese Regeln wurden im Verlauf mehrfach an einer zweiten Stelle vergessen. Sie stehen deshalb
nicht mehr nur in diesem Dokument, sondern als Test in `tests/guarantee_labels/rules_test.php`:

- Keine rohen HTML-Funktionen im Modul, nur die Helfer aus `inc/html_encoding.php`.
- Kein Archivschreibvorgang ohne Auswertung des Rueckgabewerts. Die einzige Ausnahme ist der
  Cacheschreibvorgang im Renderer; sie ist dort benannt und der Fehler wird ueber `get_errors()`
  weitergereicht.
- Historische Ausgaben fragen `guarantee_labels_order_active()` und nie `guarantee_labels_active()`.
- Die Aktivierungspruefung deckt beide Seiten ab. `missing_requirements()` prueft neben SVG-Vorlagen, TTF-Schriften und Klassenerweiterungen auch die Browserseite: die beiden WOFF2-Schriften, `guarantee_labels.css` und `guarantee_labels.js`. Eine fehlende WOFF2 liesse den Browser eine Ersatzschrift waehlen, und die Darstellung liefe von der mit TTF gemessenen Breite weg. `asset_hash()` bezieht dieselben Dateien ein, sonst ergaebe eine geaenderte Browserschrift keinen neuen Hash und der Cache liefe alt weiter.
- Ein Erweiterungshaken prueft `MODULE_GUARANTEE_LABELS_STATUS`, bevor er Modulcode laedt. Die
  benannten Ausnahmen raeumen nur auf und geben nichts aus.
- Wer `guarantee_labels_snapshot_failures()` leert, gibt die Fehler auch aus.
- Keine `SHOW`-Abfrage mit `ORDER BY`. Die Datenbankattrappen der Tests bilden MariaDB nach und nicht MySQL, sonst faellt so etwas erst im installierten Shop auf.
- Ob ein Hersteller aktiv ist, beantwortet nur `guarantee_labels_manufacturer_names()`. Ausgenommen ist die Moduldiagnose: Sie zaehlt alle Artikel des Shops in einer Abfrage und ruft keinen einzelnen Hersteller ab.
- Die Form `c_<id>_group` zerlegt nur `guarantee_labels_terms_groups()`. Sichtbarkeitspruefung des Archivs und Moduldiagnose lesen die Auswahl durch dieselbe Funktion.
- Die Breite der Labeltexte misst nur `guarantee_labels_validate_texts()`. Artikelpflege, Import und die Maske der Bestellposition fragen dort, damit keine Stelle einen Text annimmt, den eine andere ablehnt.

## Testfaelle

### Daten und Admin

- Neuinstallation und Datenbankupdate legen `products.products_garan_duration` sowie `products_content.content_type` mit den vorgesehenen Typen und Standardwerten an.
- Neue Moduldateien ohne vorheriges Datenbankupdate einspielen und das Modul installieren; `install()` legt beide fehlenden Spalten vor `MODULE_GUARANTEE_LABELS_STATUS` an und der anschliessende Aufruf von Kategorie, Suche, Warenkorb und Produktklasse verursacht keinen SQL-Fehler.
- Modul bei bereits vorhandenen beiden Spalten installieren; `install()` fuehrt kein erneutes `ALTER TABLE` aus.
- Modul mit nur einer vorhandenen Core-Spalte installieren; `install()` legt nur die fehlende Spalte an.
- Modul bei einer bereits vorhandenen Core-Spalte mit unvereinbarem Typ installieren; die Installation bleibt inaktiv, veraendert die Spalte nicht stillschweigend und meldet den Schemafehler ueber den `messageStack`.
- Fehlschlagende oder unvollstaendige Spaltenanlage simulieren; der aktive Status wird nicht eingetragen und die `ADD_SELECT_*`-Erweiterungen bleiben inaktiv. Ein erneuter Installationsversuch vervollstaendigt das Schema idempotent.
- Erfolgreiche sowie fehlgeschlagene Installation ausloesen; `install()` verwendet jeweils `add_session()`, und die passende Meldung ist nach dem Redirect des Frameworkaufrufers sichtbar.
- Modulklasse pruefen; `$this->code` ist `guarantee_labels`, die erste ausgelieferte Fassung verwendet `$this->version = '1.00'` und eine spaetere funktionale oder schemarelevante Fassung erhoeht die Version.
- Modul entfernen und erneut installieren; Core-Spalten, Modultabellen und vorhandene Daten bleiben erhalten.
- Bei installiertem Modul pruefen, dass `$this->properties['button_update']` den vorhandenen Button `Modul aktualisieren` mit `action=update` bereitstellt.
- Bei einem bereits installierten Modul einen fuer eine spaetere Version vorgesehenen Index oder ein anderes Schemaelement entfernen und `Modul aktualisieren` ausfuehren; `update()` stellt das vollstaendige Schema ohne Deinstallation wieder her.
- `Modul aktualisieren` auf einem bereits vollstaendigen Schema wiederholt ausfuehren; es entstehen weder doppelte Indizes noch Fehler oder Datenverluste.
- Schemafehler in `update()` simulieren; die Methode verwendet `add_session(..., 'error')`, gibt `false` zurueck und der Frameworkaufrufer erzeugt keine zusaetzliche Erfolgsmeldung.
- `add_db_fields` traegt `products_garan_duration` in den Speicherweg der Artikelverwaltung ein, ohne als Ersatz fuer die Schemaaenderung behandelt zu werden.
- In Systemmodul, `add_db_fields`, `define_add_select`, Storefront, Checkout und Admin pruefen, dass ausschliesslich `MODULE_GUARANTEE_LABELS_STATUS` als Statusschluessel verwendet wird.
- Aktivieren, deaktivieren, entfernen und bestehende Bestelldaten bereinigen; diese Lebenszyklus- und Aufraeumvorgaenge funktionieren auch beim Wechsel zu oder aus dem inaktiven Status.
- Modulstatus und B2B-Auswahl ueber `admin/module_export.php?set=system` speichern; `process()` wird nach dem Speichern aufgerufen, liest den neuen Status aus `TABLE_CONFIGURATION` statt aus der in diesem Request veralteten Konstante und leert keinen Cache.
- Modul bei fehlendem GD mit FreeType nachtraeglich aktivieren; `process()` setzt den Status wieder auf `false` und nennt die fehlende Voraussetzung ueber den `messageStack`. Bei vorhandenem Renderer bleibt der Status stehen, ein abgeschaltetes Modul wird gar nicht erst geprueft.
- GARAN-Systemmodul ueber den regulaeren Adminweg aufrufen; Installation, Bearbeitung, Aktualisierung und Diagnose laufen ausschliesslich ueber `admin/module_export.php?set=system`. `admin/modules.php` benoetigt fuer GARAN weder einen Speicherpfad noch eine Cache-Erweiterung.
- B2B-Mehrfachauswahl in deutscher und englischer Adminsprache anzeigen; `xtc_cfg_multi_checkbox('xtc_get_customers_statuses', 'chr(44)', ...)` verwendet die lokalisierten Kundengruppen aus `xtc_get_customers_statuses()`.
- Keine, eine und mehrere B2B-Kundengruppen speichern; `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS` enthaelt entsprechend `''`, eine ID oder kommaseparierte IDs und wird beim Lesen in eindeutige positive Integerwerte normalisiert.
- Gastzugriff ohne ausdruecklich ausgewaehlte Gast-Kundengruppe bleibt B2C; nach ausdruecklicher Auswahl folgt er der B2B-Ausschlussregel.
- Installiertes GARAN-Modul in `admin/module_export.php?set=system&module=guarantee_labels` ausdruecklich auswaehlen; die Bedingung aus `$_GET['module']`, `$this->code` und `check()` ist erfuellt und `properties['add_content']` zeigt die Diagnose direkt unter der Modul-Infobox.
- Diagnose bei fehlender Modultabelle oder Core-Spalte aufrufen; sie meldet das fehlende Schema, ohne eine Abfrage gegen das fehlende Element auszufuehren.
- Einer vorhandenen Modultabelle eine Spalte entfernen und einen Index durch einen gleichnamigen ueber andere Spalten oder ohne Eindeutigkeit ersetzen; die Schemapruefung meldet beides.
- Einen als `garan_terms` markierten Anhang bei abgeschaltetem Modul auf eine Kundengruppe einschraenken, die das Label sieht, und das Modul wieder aktivieren; die Diagnose meldet den nicht erreichbaren Anhang. Dasselbe pruefen, nachdem eine Gruppe aus der B2B-Auswahl entfernt wurde.
- Diagnose mit fehlendem GD-FreeType, nicht beschreibbarem Cache oder Archiv, unvollstaendiger Sprache, unvollstaendigem Produkt, mehrfach markierter `content_file` und fehlender Archivdatei pruefen.
- Archivverzeichnis mit fehlender `nested.svg` oder `notice.json` sowie mit abweichender Pruefsumme anlegen; beide Faelle zaehlen als beschaedigt.
- Artikel mit einer Garantiedauer zwischen `0.5` und `2.0` ohne Hersteller und Modellkennung anlegen; die Diagnose meldet ihn nicht, weil daraus kein Label entsteht.
- Produktnamen, Pfade und Fehlermeldungen mit HTML-Sonderzeichen in der Diagnose pruefen; dynamische Inhalte werden maskiert.
- Modulverwaltung ohne `module`-Parameter, mit einem anderen ausgewaehlten Modul und mit einem noch nicht installierten GARAN-Modul aufrufen; `properties['add_content']` wird nicht gesetzt und keine Diagnoseabfrage ausgefuehrt. Das gilt auch, wenn das Framework ohne Parameter GARAN als erstes `$mInfo` anzeigt.
- Deutsche und englische Modulverwaltung pruefen; Titel, Beschreibung sowie Titel und Beschreibung beider Konfigurationsschluessel stammen aus den jeweiligen Dateien unter `lang/<Sprache>/modules/system/guarantee_labels.php`.
- Storefront und manuelle Bestellsnapshots in Deutsch und Englisch pruefen; die drei Hinweis-Konstanten stammen aus `lang/<Sprache>/extra/guarantee_labels.php`, und der Adminpfad laedt die zur Bestellsprache gehoerende Storefront-Datei ausdruecklich.
- Produkt ohne GARAN-Daten speichern.
- Laufzeit `0.5`, `1`, `1.5` und `2.0` speichern, aber kein Label erzeugen.
- Diese Laufzeiten ohne Hersteller und ohne Modellkennung speichern.
- Laufzeit `0` und `0.4` ablehnen.
- Laufzeit `2.5` akzeptieren.
- Ganze Laufzeit wie `3`, `5` oder `10` akzeptieren.
- Unzulaessige Laufzeit wie `4.1` ablehnen.
- Laufzeit ohne Hersteller ablehnen.
- Laufzeit ohne Hersteller-Modellkennung ablehnen.
- Sonderzeichen in Herstellername und Modellkennung sicher verarbeiten.
- Sehr lange Werte erkennen und mit verstaendlicher Meldung ablehnen.
- Modulaktivierung ohne GD-FreeType beziehungsweise ohne `imagettfbbox()` ablehnen und die konkrete Fehlermeldung ueber den `messageStack` ausgeben.
- Textbreite mit Regular und ExtraBold jeweils gegen den vorgesehenen Vorlagenbereich pruefen.
- Herstellername und Modellkennung mit Umlauten und Sonderzeichen in einem Shop mit `DB_SERVER_CHARSET = 'latin1'` und in einem auf `utf8` speichern und ausgeben; beide Shops erzeugen dasselbe Label mit demselben Hash und keiner der Werte faellt aus der Grafik.
- Einen Herstellernamen ueber `admin/manufacturers.php` so verlaengern, dass er nicht mehr in den Vorlagenbereich passt; die Artikel werden dabei nicht neu geprueft, der Renderer erzeugt aber trotzdem kein Label und protokolliert den Grund. Dasselbe mit einer direkt in die Datenbank geschriebenen Modellkennung pruefen.
- Eingaben mit `2,5` und `2.5` identisch normalisieren.
- Ganzjahreswert groesser als `99` ablehnen.
- Halbjahreswert groesser als `99.5` ablehnen.
- Halbe Jahre im Label mit Komma darstellen, zum Beispiel `2,5` statt `2.5`.
- GARAN-Daten ohne Garantie-Anhang speichern und das sprachneutrale Label trotzdem ausgeben.
- Artikel mit einer Download- und einer koerperlichen Variante anlegen; die Artikelliste zeigt das Label, die reine Downloadvariante im Warenkorb nicht. Bestellung abschliessen und pruefen, dass fuer diese Position weder Snapshot noch Zusage noch Anhang entsteht. Denselben Fall mit `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED = true` pruefen; die Artikelliste zeigt das Label auch dort, und die ausdruecklich gewaehlte koerperliche Variante behaelt es.
- Garantiebedingungen in der Bestellbearbeitung hochladen: PDF und Textdatei werden angenommen, eine PHP-Datei nicht, ebensowenig eine PHP-Datei mit der Endung `.pdf` oder ein PDF mit der Endung `.php`.
- Dateinamen mit fuehrendem oder abschliessendem Leerzeichen hochladen; der Upload wird abgelehnt, weil der Mailweg den Pfad schneidet.
- Vorhandenen Artikel-Anhang je Sprache als `garan_terms` markieren.
- Reinen externen Link als Garantieerklaerung ablehnen.
- Anhang eines anderen Artikels oder einer anderen Sprache ablehnen.
- Herstellername, Modellkennung und Anhangsnamen mit `<`, `>`, `"` und `&` in Bestellansicht, Beleg und Mail pruefen; die HTML-Fassung maskiert sie, die Textfassung nicht. Ein Dateiname wie `<img src=x onerror=alert(1)>.pdf` besteht den Dateinamenfilter und darf trotzdem kein HTML einschleusen.
- Anhang als `garan_terms` markieren, dessen `group_ids` eine Kundengruppe ausschliessen, die das Label sieht; die Markierung wird abgelehnt und die betroffene Gruppe benannt. Eine ausgeschlossene B2B-Gruppe fuehrt zu keinem Befund, eine leere Auswahl ebenfalls nicht.
- Unterschiedliche von der vorhandenen Artikel-Anhangsverwaltung erlaubte Dateiformate als `garan_terms` verwenden und unveraendert archivieren.
- Dateiname und Erweiterung sicher in `terms_filename` uebernehmen; Pfadbestandteile, Steuerzeichen und Kommas ablehnen.
- Archivdatei unter `DIR_FS_DOCUMENT_ROOT . 'media/guarantee_labels/archive/terms/<terms_hash>/<terms_filename>'` anlegen und mit ihrem gespeicherten Dateinamen versenden.
- Zwei inhaltlich identische Garantie-Anhaenge mit unterschiedlichen Dateinamen atomar als zwei Dateien im selben `terms_hash`-Verzeichnis archivieren.
- Dateinamen mit Komma ablehnen und pruefen, dass sie die kommaseparierte Anhangsliste nicht erreichen.
- Loeschen eines zugeordneten Anhangs setzt beim naechsten Bestellsnapshot `terms_hash` und `terms_filename` auf `NULL`, ohne die GARAN-Ausgabe zu deaktivieren.
- Artikel mit je Variante abweichender Garantie ablehnen.
- Artikel mit GARAN-Daten duplizieren; das Duplikat erhaelt `products_garan_duration = NULL`, eine leere `products_manufacturers_model` und keine als `garan_terms` markierten Anhaenge. Andere zum Kopieren ausgewaehlte Artikel-Anhaenge werden weiterhin uebernommen.
- Denselben Artikel bei abgeschaltetem Modul duplizieren; das Duplikat wird genauso bereinigt. Modellkennung und `content_type` gehoeren dem Ursprungsartikel, unabhaengig vom Modulstatus.
- Artikel mit gueltiger gespeicherter Garantiedauer mit einer ungueltigen Dauer speichern; die Artikelverwaltung kehrt zur Maske zurueck und die gespeicherte Dauer ist unveraendert. Dasselbe mit geleertem Hersteller oder geleerter Modellkennung pruefen.
- Garantiedauer bewusst leeren und speichern; die Spalte wird auf `NULL` gesetzt und es entsteht keine Fehlermeldung.
- Modellkennung eines Artikels mit gueltiger Garantiedauer leeren und speichern; die Aenderung wird abgelehnt und weder Dauer noch Modellkennung noch Hersteller sind veraendert. Der Artikel bleibt vollstaendig.
- `p_garan_duration` leer, mit Komma, mit Punkt sowie mit gueltigen und ungueltigen Werten importieren. Bei ungueltigem resultierendem GARAN-Datensatz bleibt `products_garan_duration` unveraendert und der Fehler erscheint im `messageStack`.
- CSV ohne die Spalte `p_garan_duration` importieren, die `p_man` eines Artikels mit gespeicherter Garantiedauer leert; die Pruefung laeuft trotzdem und meldet den unvollstaendigen GARAN-Datensatz.
- Einen Artikel mit gueltiger gespeicherter Garantiedauer mit einer CSV-Zeile importieren, deren GARAN-Daten unvollstaendig sind; Dauer, Modellkennung und Hersteller bleiben unveraendert. Die uebrigen Felder der Zeile werden regulaer uebernommen.
- Dieselbe Zeile mit geleertem `p_man` importieren; der Artikel behaelt seine bisherige Modellkennung und wird nicht unvollstaendig.
- `products_garan_duration` als `p_garan_duration` exportieren; `NULL` wird leer und ein Wert wird kanonisch ausgegeben.
- Pruefen, dass der Produkt-CSV weder `products_content.content_type` noch Garantie-Anhaenge importiert oder exportiert.
- Fehler aus Modulverwaltung, Artikelpflege, Anhangspflege, Import und Bestellbearbeitung jeweils ueber den bestehenden `messageStack` ausgeben.
- Fehler in einem Pfad mit Redirect und in einem Pfad ohne Redirect ausloesen; die erste Meldung wird mit `add_session()` nach dem Redirect, die zweite mit `add()` im aktuellen Request angezeigt.
- GARAN-Fehler ueber den `LoggingManager` in `mod_guarantee_labels_<level>_<YYYY-MM-DD>.log` schreiben und Anzeige sowie Aufraeumen ueber die bestehenden Logfunktionen pruefen.
- Mit `USE_CACHE = true` einen GARAN-Artikel in Cross-Selling und im Block neuer Artikel cachen, danach seine Garantiedauer entfernen und speichern; beide Bloecke koennen das Label bis zum Ablauf von `CACHE_LIFETIME` weiter zeigen. Nach `admin/configuration.php?action=delcache` ist es verschwunden.
- Herstellername oder Herstellerstatus ueber das Herstellerformular aendern; die naechste ungecachte Ausgabe verwendet den neuen Stand.
- Herstellerstatus mit `setflag` aus der Herstellerliste aendern; das Label folgt dem neuen Status, sobald der Block nicht mehr aus dem Smarty-Cache stammt.
- Hersteller ohne die Option `Artikel mitloeschen` loeschen; `manufacturers_id` der betroffenen GARAN-Artikel wird `''`, das Label verschwindet aus der ungecachten Ausgabe und die Moduldiagnose listet die unvollstaendigen Artikel.
- Hersteller mit der Option `Artikel mitloeschen` loeschen; die zugehoerigen GARAN-Daten verschwinden mit den Artikeln.
- Herstellerzuordnung und Hersteller-Modellkennung am Artikel jeweils aendern; die naechste ungecachte Ausgabe verwendet ausschliesslich den neuen gueltigen Stand.
- GARAN-Modul bei gefuelltem Blockcache deaktivieren; gecachte Bloecke koennen das Label bis zum Ablauf von `CACHE_LIFETIME` weiter zeigen, jede ungecachte Ausgabe nicht mehr.
- Einen in `products_media.php` gecachten `garan_terms`-Anhang ersetzen und entfernen; der ungecachte Medienblock zeigt weder die alte Datei noch einen veralteten Link.
- Mehrere GARAN-Datensaetze in einem CSV-Import aendern; der Import leert keinen Cache.
- Shopcache ueber `admin/configuration.php?action=delcache` mit vorhandenem `cache/guarantee_labels/` leeren; das gesamte Unterverzeichnis verschwindet und der naechste Renderaufruf legt es samt Hashverzeichnis selbststaendig neu an. Das Modul selbst loest diese Leerung nie aus.
- Fehlgeschlagene GARAN-Aenderung pruefen; die Daten bleiben unveraendert, die Fehlermeldung erscheint ueber den `messageStack`.

### Produktseite

- Kein Label ohne qualifizierte Garantie.
- Kompaktes Label bei qualifizierter Garantie.
- Vollstaendiges Label nach der ersten Interaktion.
- Bedienung mit Maus und Touch.
- Korrekte Darstellung auf Desktop und Mobilgeraet.
- QR-Code bleibt scanbar.
- Direkter Link funktioniert.
- Ein fuer die aktuelle Sprache und Kundengruppe sichtbarer Anhang vom Typ `garan_terms` erscheint ueber `includes/modules/products_media.php` im bestehenden Medienbereich des Artikels.
- `SHOW_BUTTON_BUY_NOW = true`: Kompaktes GARAN-Label in Kategorie, Suche, Sonderangeboten, Cross-Selling und weiteren Artikellisten anzeigen.
- `SHOW_BUTTON_BUY_NOW = false`: Kein GARAN-Label in Artikellisten; Label erstmals in der Produktdetailansicht anzeigen.
- Produktdetailansicht und Checkout zeigen das GARAN-Label bei beiden Einstellungen.
- Alle vier mitgelieferten Templates mit beiden Einstellungen pruefen.
- Normale Kategorieliste ohne Herstellerfilter pruefen; Herstellername, Hersteller-Modellkennung und Garantiedauer stehen fuer jeden GARAN-Artikel vollstaendig zur Verfuegung.
- Suche, Sonderangebotsseite, neue Artikel, Cross-Selling und Reverse-Cross-Selling jeweils mit vollstaendigen drei Labelwerten pruefen.
- Mehrere sichtbare GARAN-Artikel verschiedener Hersteller pruefen. Wo der ganze Ergebnisblock vorliegt, erfolgt eine gesammelte Herstellerabfrage; in Artikellisten ueber `buildDataArray()` und bei Bestellpositionen eine Abfrage je unterschiedlichem Hersteller der Seite, weil dort nur eine Zeile je Aufruf vorliegt.
- Artikel mit fehlendem oder inaktivem Hersteller erhaelt trotz gesetzter Garantiedauer kein Label.
- Neuen Artikel mit inaktivem Hersteller pruefen; ein von `includes/modules/new_products.php` bereits gelieferter `manufacturers_name` wird ignoriert und es erscheint kein Label.
- Bei inaktivem Modul und bei `SHOW_BUTTON_BUY_NOW = false` in einer Artikelliste erfolgt keine GARAN-Nachladeabfrage.

### Checkout

- Koerperliche Ware zeigt Gewaehrleistungshinweis.
- Reiner Download-Warenkorb zeigt keinen Gewaehrleistungshinweis.
- Gemischter Warenkorb zeigt einen passend erklaerten Hinweis.
- Betroffener Artikel zeigt sein GARAN-Label.
- Nicht betroffener Artikel zeigt kein GARAN-Label.
- Mehrere GARAN-Artikel bleiben eindeutig unterscheidbar.
- Ausgabe liegt vor dem Bestellbutton.
- Warenkorb und Checkout mit mehreren GARAN-Artikeln verschiedener Hersteller pruefen; alle drei Labelwerte werden uebernommen und die Herstellernamen gemeinsam nachgeladen.
- Beim Bestellabschluss pruefen, dass der Positionssnapshot dieselben validierten Hersteller-, Modell- und Garantiedaten wie die Checkout-Ausgabe verwendet.

### Bestellung und E-Mail

- GARAN-Daten werden in `orders_products_guarantee` gespeichert.
- Historischer Hinweis-Snapshot und Vorlagenversion sowie optional vorhandene Garantiebedingungen werden mit der Bestellung versioniert.
- Eine Zeile in `orders_guarantee` ausschliesslich als `Hinweis-Snapshot vorhanden` auswerten und weder bei Storefront- noch bei manuellen Bestellungen als allgemeinen Nachweis einer Checkout- oder Mailausgabe behandeln.
- Kann beim erstmaligen Erzeugen das Archiv fuer den Gewaehrleistungshinweis nicht vollstaendig geschrieben und geprueft werden, laeuft die Bestellung weiter und es entsteht keine Zeile in `orders_guarantee`.
- Kann beim erstmaligen Erzeugen das GARAN-Grafikarchiv fuer eine Position nicht vollstaendig geschrieben und geprueft werden, laeuft die Bestellung weiter und es entsteht keine Zeile in `orders_products_guarantee` fuer diese Position.
- Eine archivierte Garantiebedingung nach dem Bestellabschluss durch eine andere Datei gleichen Namens ersetzen und die Auftragsbestaetigung erneut versenden; die Mail nennt das Dokument nicht und haengt es nicht an. Zusage und Gewaehrleistungshinweis bleiben erhalten.
- Dieselbe Bestellung mit unveraendertem Archiv versenden; Mailtext und Anhangsliste nennen dieselbe Datei.
- Schlaegt beim erstmaligen Erzeugen nur das Archivieren optionaler Garantiebedingungen fehl, bleibt der GARAN-Kerndatensatz erhalten; `terms_hash` und `terms_filename` bleiben `NULL` und der Fehler wird sichtbar protokolliert.
- Produktdaten koennen nach der Bestellung geaendert werden, ohne die Bestellung zu veraendern.
- Ersetzen oder Loeschen des Artikel-Anhangs veraendert die archivierte Garantieerklaerung einer Bestellung nicht.
- Erneuter Mailversand verwendet den Bestellsnapshot.
- Mailtext, Linktext oder Your-Europe-URL in den Sprachkonstanten aendern; ein erneuter Mailversand verwendet weiterhin die Werte aus dem archivierten `notice.json`.
- HTML-Mail zeigt den sprachabhaengigen Gewaehrleistungstext und den direkten Link, aber weder die Grafik des Gewaehrleistungshinweises noch eine GARAN-Grafik.
- Die Auftragsbestaetigung enthaelt weder SVG- noch PNG-Anhaenge fuer das GARAN-Label.
- Text-Mail enthaelt den Gewaehrleistungshinweis und den passenden Link, aber keine GARAN-Produktdaten.
- Gastbestellung und Kundenbestellung verhalten sich gleich.
- B2B-Kundengruppe erhaelt keine B2C-Kennzeichnungen.
- Vorhandene Garantiebedingungen sind Bestandteil der Mail und werden nicht nur von externen URLs geladen. Ohne archivierte Garantiebedingungen wird kein GARAN-Anhang versendet.
- Fehlt beim Versand das referenzierte `notice.json` oder ein Garantie-Anhang oder ist die Datei beschaedigt, wird sie nicht aus aktuellen Daten ersetzt. Der betroffene Inhalt wird ausgelassen, der Mailversand laeuft weiter und der Fehler wird protokolliert.
- Fehlende GARAN-Archivgrafik in der historischen Admin-Ansicht protokollieren, ohne den Mailversand zu beeinflussen.
- Bei inaktivem Modul entstehen keine neuen GARAN- oder Gewaehrleistungssnapshots.
- Deaktivieren des Moduls loescht vorhandene Bestellsnapshots nicht.
- Nach erneuter Aktivierung verwendet ein erneuter Mailversand weiterhin die vorhandenen Bestellsnapshots.

### Loeschen und Datenkonsistenz

- Bestellung loeschen; die Zeilen in `orders_guarantee` und `orders_products_guarantee` verschwinden mit.
- Einzelne Bestellposition loeschen; nur die zugehoerige Zeile in `orders_products_guarantee` verschwindet.
- Bestellung mit installiertem und deinstalliertem GARAN-Modul loeschen; der eigene Block in `xtc_remove_order()` darf jeweils keine Fehler verursachen.
- Bestellposition bei nie installiertem GARAN-Modul loeschen; `orders_product_delete()` darf keine nicht vorhandene Modultabelle abfragen.
- Modul deinstallieren; Konfiguration verschwindet, beide Tabellen und ihre Daten bleiben erhalten.
- Modul erneut installieren; vorhandene Tabellen bleiben unveraendert und behalten ihre Indizes.
- Cache leeren; `colour.svg` und `nested.svg` werden unter demselben `garan_hash` neu erzeugt und archivierte SVG-Grafiken zu bestehenden Bestellungen bleiben verfuegbar.
- Direkten HTTP-Aufruf einer Datei unter `media/guarantee_labels/archive/` durch die eigene `.htaccess` blockieren, einschliesslich einer archivierten Garantiebedingung unter `terms/`. Die Katalogfassung derselben Datei unter `media/products/` bleibt erreichbar.
- Zwei Bestellungen mit demselben `garan_hash` und `notice_hash` verwenden dieselben Archivverzeichnisse, ohne vorhandene Dateien zu ueberschreiben.
- Zwei parallele Schreibvorgaenge fuer denselben Hash erzeugen durch temporaere Nachbarverzeichnisse und atomare Umbenennung keine unvollstaendigen Archivverzeichnisse.
- Nicht beschreibbaren GARAN-Cache testen; die aktuelle Ausgabe verwendet die direkt erzeugten SVGs, der Fehler erscheint im Protokoll und in der Moduldiagnose.
- Unvollstaendigen oder beschaedigten GARAN-Cache testen; er wird nicht ausgegeben und die aktuelle Anfrage verwendet direkt erzeugte SVGs. Die Seite verweist dann nicht auf die Cachedatei, sondern bettet die Grafik ein.
- Eine Datei im Hashverzeichnis ersetzen und den Sidecar unveraendert lassen; das Verzeichnis gilt als nicht lesbar.
- Den Sidecar unlesbar machen; das gilt als Schaden und nicht als Archiv ohne Sidecar.
- Einen Eintrag aus dem Sidecar entfernen und die zugehoerige Datei ersetzen; die fehlende Zeile deckt die Ersetzung nicht.
- Ein Archivverzeichnis ohne Sidecar aus einer aelteren Fassung lesen; es bleibt lesbar.
- Einen Hash mit Pfadanteilen, falscher Laenge oder Grossbuchstaben und einen Anhangsnamen mit Pfadanteil oder Komma an das Archiv geben; weder Lesen noch Schreiben erzeugt einen Pfad, es entsteht kein Verzeichnis ausserhalb des Archivs und jeder einzelne Fehlgriff steht im Protokoll, das Lesen wie das Schreiben. Der Dateiname wird beim Lesen nach derselben Regel geprueft wie beim Schreiben, einschliesslich der Steuerzeichen, die den Mailkopf zerlegen wuerden.
- Die Werte des Labels tragen den Zeichensatz des Shops, nur die Grafik ist immer UTF-8; ein Latin-1- und ein UTF-8-Shop erzeugen denselben Hash.
- Einen Schreibvorgang so scheitern lassen, dass das temporaere Verzeichnis unvollstaendig bleibt; es wird nicht umbenannt, kein Zielverzeichnis bleibt zurueck und der Fehler steht im Protokoll.
- In ein beschaedigtes Hashverzeichnis erneut schreiben; es wird verworfen und aus den Vorlagen neu angelegt.
- Ein beschaedigtes Hashverzeichnis unentfernbar machen; `remove_dir()` meldet den Fehlschlag, der Schreibvorgang bricht mit einer konkreten Meldung ab und protokolliert sie.
- Nicht beschreibbares oder unvollstaendig geschriebenes Archiv testen; die Bestellung laeuft weiter und keine Datenbankzeile verweist auf fehlende oder teilweise geschriebene Dateien.
- Erneuter Mailversand nach dem Leeren des Cache liefert dieselben vorhandenen Garantieerklaerungen und keine GARAN-Grafik. Waren keine Bedingungen archiviert, bleibt der Versand ohne GARAN-Anhang.
- Nur die Ueberschrift des Hinweises in der Sprachdatei aendern und eine neue Bestellung abschliessen; sie erhaelt ein neues Archivverzeichnis mit der neuen Ueberschrift, bestehende Bestellungen behalten ihres.
- Danach die Auftragsbestaetigung einer bestehenden Bestellung erneut versenden; Text, Link und Ueberschrift stammen alle aus ihrem Archiv, nicht aus der geaenderten Sprachdatei.
- Dasselbe mit einer Bestellung pruefen, deren `notice.json` kein Feld `title` traegt; nur ihre Ueberschrift kommt aus der Sprachdatei, Text und Link bleiben historisch.

### Manuell angelegte Bestellungen

- Leere B2C-Bestellung bei aktivem Modul und vollstaendig gepflegter Bestellsprache anlegen; `orders_guarantee`, `notice.svg` und `notice.json` entstehen bereits vor dem Einfuegen einer Position.
- Manuelle Bestellung in einer deutschen Backend-Sitzung anlegen; unabhaengig von der bevorzugten Kontaktsprache des Kunden verwendet der Hinweis-Snapshot Deutsch und ein GARAN-Artikel den deutschen `garan_terms`-Anhang.
- Manuelle Bestellung in einer englischen Backend-Sitzung anlegen; Hinweis-Snapshot und `garan_terms`-Anhang verwenden Englisch.
- Sprache der Backend-Sitzung nach dem Anlegen wechseln; die in `orders.language` gespeicherte Bestellsprache bleibt massgeblich, auch fuer danach eingefuegte Positionen, und bestehende Snapshots bleiben unveraendert.
- Leere Bestellung bei inaktivem Modul, ausgeschlossener B2B-Kundengruppe oder unvollstaendiger Sprache anlegen; `orders_guarantee` bleibt leer.
- Nach dem Anlegen Modulstatus, Kundengruppe oder Sprachdaten aendern; der bestehende Hinweis-Snapshot wird nicht automatisch erzeugt, ersetzt oder entfernt.
- Frisch angelegte Bestellung ohne Positionen pruefen; `guarantee_labels_order_physical()` liefert `false` und der Gewaehrleistungshinweis wird nicht ausgegeben.
- Nur virtuelle Positionen einfuegen und entfernen; die Pruefung liefert durchgehend `false` und die Auftragsbestaetigung enthaelt keinen Hinweis.
- Physische sowie gemischte Positionen einfuegen und entfernen; die Pruefung liefert `true`, solange mindestens eine koerperliche Position verbleibt. Koerperlich ist eine Position ohne Downloadzeile und ebenso eine gemischte, die mehr Attribute als Downloadzeilen traegt. Erst wenn keine solche Position mehr uebrig ist, liefert die Pruefung wieder `false`.
- `orders.content_type` bleibt bei allen drei Faellen unveraendert; das Modul schreibt die Spalte nicht.
- Storefront-Bestellung mit `orders.content_type = 'mixed'` pruefen; der Hinweis wird ausgegeben.
- Manuelle Bestellung mit leerem `orders.content_type` und genau einer Position anlegen, die ein Download- und ein koerperliches Attribut traegt; die Position hat dann zwei Attribute und eine Downloadzeile, gilt als gemischt und damit als koerperlich, und der Hinweis erscheint. Denselben Fall mit `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED = true` pruefen; dort macht schon die eine Downloadzeile die Position digital.
- Das letzte Attribut einer Position entfernen; alle Downloadzeilen der Position verschwinden und die Bestellung gilt danach als koerperlich.
- Von zwei Attributen eines entfernen, sodass mehr Zeilen als Attribute verbleiben; keine Zeile wird geloescht und der Shopbetreiber erhaelt die Warnung nach der Weiterleitung.
- Das einzige Downloadattribut einer Position entfernen, die auch ein koerperliches Attribut traegt; es bleiben ein Attribut und eine Zeile, es wird nichts geloescht und die Warnung erscheint.
- Ein koerperliches Attribut aus einer Position ohne Downloadzeilen entfernen; es erscheint keine Warnung.
- Ein Downloadattribut in der Bestellung umbenennen und danach ein anderes Attribut loeschen; die vorhandenen Downloadrechte bleiben unveraendert erhalten.
- Denselben Fall bei einer Bestellung aus dem Checkout pruefen: `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` nach der Bestellung umschalten und erneut versenden. Zusage, Anhang und Hinweis bleiben unveraendert, weil `orders.content_type` entscheidet.
- Einer Position einer manuell angelegten Bestellung nachtraeglich ein Downloadattribut hinzufuegen, sodass sie digital wird: Bestellansicht, Beleg, Kundenkonto und Auftragsbestaetigung fuehren fuer sie weder Zusage noch Anhang, die Zeile in `orders_products_guarantee` bleibt aber bestehen. Die Bestellbearbeitung zeigt sie weiter an, meldet den Zustand und erlaubt nur noch das Entfernen.
- Bestellung, die nach dieser Regel keine Ware mehr enthaelt: weder Auftragsbestaetigung noch Kundenkonto geben den Gewaehrleistungshinweis aus.
- `EMAIL_BILLING_ATTACHMENTS` auf `de::datei_de.pdf||en::datei_en.pdf` setzen und eine deutsche Bestellung mit Garantiebedingungen versenden; die Mail traegt `datei_de.pdf` und die Bedingungen, nicht `datei_en.pdf`.
- Den Kunden einer bestehenden Bestellung nachtraeglich einer B2B-Gruppe zuordnen; Zusage, Anhang und Hinweis bleiben in seinem Kundenkonto und in einem erneuten Versand unveraendert.
- Bei einer Checkout-Bestellung mit gemischter Position `DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED` umschalten und den Snapshot in der Bestellbearbeitung korrigieren; das Speichern gelingt weiterhin.
- `DOWNLOAD_ENABLED` abschalten und eine manuelle Bestellung mit einer liegengebliebenen Downloadzeile pruefen; Zusage, Anhang und Hinweis bleiben erhalten.
- Einen `garan_terms`-Anhang mit Datei und externem Link speichern; die Markierung wird abgelehnt und der Anhang als normaler Anhang gespeichert.
- Einen Artikel mit Garantiezusage auf den Merkzettel legen; das kompakte Label steht dort wie in der Artikelliste. Eine gemerkte reine Downloadvariante bekommt keines.
- Das Cacheverzeichnis `cache/guarantee_labels/` schreibgeschuetzt setzen und im Admin einen Snapshot speichern; das Label entsteht, der Vorgang gelingt, und der Shopbetreiber bekommt zusaetzlich eine Warnung ueber den `messageStack`.
- Dasselbe fuer eine alte Bestellung im Kundenkonto: Text und Link des Hinweises stehen weiter da, nur Grafik und Oeffnen-Schaltflaeche fehlen.
- Zwei Label gleichzeitig oeffnen, eines aus einer alten Bestellung und eines aus dem Katalog; beide behalten ihre eigene Darstellung, kein Clip-Pfad und keine Farbe springt ueber.
- Eine Bestellposition mit einer nicht zur Bestellung gehoerenden `opID` loeschen; der GARAN-Snapshot der fremden Bestellung bleibt unveraendert.
- `images/guarantee_labels/fonts/Inter-Regular.woff2` entfernen und das Modul installieren; die Installation lehnt ab und nennt die fehlende Datei.
- Einen Warenkorb mit Artikeln verschiedener Hersteller aufrufen; es entsteht eine Herstellerabfrage fuer den ganzen Warenkorb, nicht eine je Position.
- Modul deinstallieren, waehrend noch GARAN-Daten im Katalog stehen: Die Deinstallationsmaske nennt die verbleibende Erweiterung, ein Duplikat erbt weder Modellkennung noch Garantiedauer noch Garantiebedingungen. Die Erweiterung anschliessend unter Module > Artikelverwaltung deinstallieren und erneut duplizieren; die Daten werden dann mit uebernommen.
- Modul deinstallieren, waehrend der Katalog keine Garantiedauer und keinen `garan_terms`-Anhang mehr fuehrt: Alle drei Klassenerweiterungen werden abgemeldet.
- Den Status einer der drei Klassenerweiterungen in der Konfiguration auf `false` setzen: Die Moduldiagnose zeigt die Zeile rot, `Modul aktualisieren` stellt den Status wieder her. In der Modulmaske der Erweiterung selbst gibt es kein Statusfeld.
- Bezeichnung, Preis oder Gewicht eines Bestellattributs aendern; die Downloadzeilen der Position bleiben unveraendert, auch wenn der Katalog inzwischen etwas anderes sagt.
- Die ganze Bestellposition loeschen; ihre Downloadzeilen verschwinden mit.
- Die Einstufung fragt ausschliesslich Bestelldaten. Einen Artikel oder ein Attribut aus dem Katalog loeschen; die Einstufung bestehender Bestellungen aendert sich dadurch nicht.
- Einem frueher koerperlichen Attribut im Katalog die Datei eines anderen Attributs derselben Position zuordnen und dann das koerperliche Attribut aus der alten Bestellung loeschen; das Downloadrecht des anderen Attributs bleibt bestehen.
- Ein frueher koerperliches Attribut im Katalog zu einem Download machen und die alte Bestellung bearbeiten; es entsteht kein neues Downloadrecht.
- Den Katalogartikel loeschen und danach ein Attribut der alten Bestellposition entfernen; die vorhandenen Downloadrechte bleiben bestehen. Der Katalog wird dabei gar nicht gefragt.
- Dieselbe Bestellung mit `physical`, `virtual` und `virtual_weight` pruefen; nur die ersten beiden Faelle unterscheiden sich in der Ausgabe wie erwartet.
- Reine Download-Bestellung behaelt den Hinweis-Snapshot, gibt ihn in der Auftragsbestaetigung aber nicht aus.
- Physische oder gemischte Bestellung gibt den beim Anlegen gespeicherten Hinweis in der manuellen Auftragsbestaetigung aus.
- Leere Bestellung im Admin anlegen und einen Artikel ohne GARAN-Daten einfuegen.
- GARAN-Artikel im Admin einfuegen und alle sechs GARAN-Snapshotwerte der Bestellposition pruefen.
- Katalogdaten nach dem Einfuegen aendern und unveraenderten Bestellsnapshot pruefen.
- Artikel entfernen und erneut einfuegen; dabei die aktuellen Katalogdaten uebernehmen.
- GARAN-Snapshot im Admin korrigieren und Validierung pruefen.
- Optionale archivierte Garantieerklaerung in der Bestellposition hinzufuegen, ersetzen und entfernen; dabei `terms_hash` sowie `terms_filename` pruefen.
- Fehler beim Erzeugen oder Archivieren einer Snapshotaenderung ausloesen; die Aenderung wird abgelehnt, der bisherige Snapshot bleibt vollstaendig erhalten und der Admin erhaelt die Fehlermeldung ueber den `messageStack`.
- Bestellung bei nicht beschreibbarem Archiv manuell anlegen und eine Position einfuegen; beide Aktionen melden den Fehler ueber den `messageStack` und nicht nur im Protokoll.
- Position eines Artikels ohne GARAN-Daten einfuegen; es entsteht keine Zeile und keine Meldung.
- Herstellername, Hersteller-Modellkennung und Garantiedauer nur als vollstaendigen Datensatz speichern.
- Aenderung des Bestellsnapshots darf den Katalogartikel nicht veraendern.
- Aktion `Aus Artikeldaten uebernehmen` pruefen; Herstellername, Modellkennung, Dauer und der `garan_terms`-Anhang der Bestellsprache werden gemeinsam uebernommen. Hat der Artikel keinen Anhang, werden `terms_hash` und `terms_filename` auf `NULL` gesetzt statt den bisherigen Anhang zu behalten.
- Dieselbe Aktion mit einer fremden `pID` im Formular aufrufen; der Artikel der bearbeiteten Bestellposition entscheidet und die fremde Angabe bleibt wirkungslos.
- Vorschau des GARAN-Labels vor dem Speichern pruefen; sie zeigt die Werte aus der Maske, auch wenn sie noch von den gespeicherten abweichen. Ungueltige Eingaben ergeben keine Vorschau.
- Aenderung einer bereits versendeten Bestellung bestaetigen und in der Bestellhistorie pruefen.
- Bestehende Bestellung mit leeren GARAN-Feldern manuell ergaenzen.
- Bestehende Funktion `Auftragsbestaetigung senden` in der Bestellsprache pruefen.
- Reine Download-Bestellung ohne Ausgabe des Gewaehrleistungshinweises testen.
- Physische und gemischte Bestellung mit Ausgabe des gespeicherten Gewaehrleistungshinweises testen.
- Bestellbestaetigung und erneuten Mailversand aus dem Bestellsnapshot erzeugen.
- Geloeschten Katalogartikel testen; die Bestelldaten und die Mailausgabe muessen erhalten bleiben.

### Sprachen

- Deutsch verwendet die korrekte deutsche Gewaehrleistungsgrafik.
- Englisch verwendet die korrekte englische Gewaehrleistungsgrafik.
- Die jeweilige Grafik wird aus `lang/<Sprachverzeichnis>/notice.svg` geladen.
- Nachtraeglich installiertes, vollstaendig gepflegtes Sprachpaket verwenden, ohne Artikel neu zu speichern oder GARAN-Grafiken neu zu erzeugen.
- Beim Anlegen einer neuen Sprache normale Artikel-Anhaenge kopieren, Markierungen vom Typ `garan_terms` aber nicht uebernehmen.
- Dieselbe `content_file` in mehreren Sprachen als `garan_terms` markieren und die Pruefwarnung in der Moduldiagnose kontrollieren.
- QR-Code und direkter Link zeigen auf die passende Sprachseite.
- Fehlende Sprachgrafik, fehlenden Mailtext, fehlenden Linktext und fehlende Your-Europe-URL jeweils einzeln testen.
- Modul bei fehlender SVG-Vorlage, fehlender Schrift oder fehlender Klassenerweiterungsdatei aktivieren; die Aktivierung wird abgelehnt und nennt die fehlenden Bestandteile. Die Meldung enthaelt die Umlaute der Sprachdatei als Zeichen und nicht als Entity; nur die eingesetzten Datei- und Funktionsnamen werden maskiert.
- Einer Modultabelle den Defaultwert einer Spalte aendern und ihren Primaerschluessel auf eine andere Spalte legen; beides meldet die Schemapruefung.
- Jeder fehlende Sprachbestandteil erzeugt eine Warnung in der Moduldiagnose und wird darin namentlich genannt. Der Checkout laeuft ohne Gewaehrleistungshinweis und ohne sprachlichen Fallback weiter.
- Eine dritte Shopsprache ohne `lang/<Sprachverzeichnis>/extra/guarantee_labels.php` aufrufen; das sprachneutrale GARAN-Label erscheint weiterhin. Die Beschriftungen fallen auf `GARAN` zurueck und der Link zur Your-Europe-Seite entfaellt.
- In derselben Sprache nur `TEXT_GUARANTEE_LABEL_URL` pflegen und den Linktext weglassen; der Link erscheint nicht und es entsteht kein PHP-Fehler.
- Eine abgeschaltete Sprache anlegen; die Moduldiagnose prueft sie nicht.
- Jede Konstante aus `guarantee_labels_notice_constants()` einzeln entfernen; Moduldiagnose, Checkout und Snapshoterzeugung kommen jeweils zum selben Ergebnis. Insbesondere darf der Checkout keinen Hinweis zeigen, ohne dass die Bestellung einen Snapshot bekommt, und umgekehrt. `TEXT_GUARANTEE_NOTICE_MIXED` gehoert ausdruecklich dazu.
- Eine englische Bestellung im Kundenkonto einer deutschen Sitzung oeffnen; das GARAN-Label, der archivierte Gewaehrleistungstext, sein Linktext, seine Adresse und seine Ueberschrift erscheinen unveraendert. Nichts davon darf wegen der abweichenden Sitzungssprache verschwinden.
- Dieselbe Bestellung in ihrer eigenen Sprache oeffnen; zusaetzlich tragen auch die Bedienelemente des Blocks die Beschriftungen dieser Sprache.
- Eine Bestellung in einer unvollstaendig gepflegten Sprache erzeugt keine Zeile in `orders_guarantee` und nimmt den Hinweis auch bei einem spaeteren Mailversand nicht nachtraeglich auf.
- Wird eine Sprache spaeter vervollstaendigt, verwenden neue Bestellungen sie sofort; bestehende Bestellsnapshots bleiben unveraendert.
- Einen als `garan_terms` markierten Anhang auf eine Kundengruppe einschraenken und mit einer anderen Gruppe bestellen; es wird nichts archiviert, `terms_hash` und `terms_filename` bleiben `NULL` und das Label erscheint weiterhin.
- Bestellsprache im Admin wechseln und danach `Aus Artikeldaten uebernehmen` ausloesen; uebernommen wird der Anhang der neuen Bestellsprache.
- Moduldiagnose in einem Shop mit vielen `garan_terms`-Anhaengen aufrufen; die Kundengruppen werden einmal geladen und nicht je Anhang.
- Deutscher Auftrag verwendet den deutschen Artikel-Anhang vom Typ `garan_terms`.
- Englischer Auftrag verwendet den englischen Artikel-Anhang vom Typ `garan_terms`.
- Fehlender Garantie-Anhang verhindert die sprachneutrale GARAN-Ausgabe nicht und fuehrt zu einer Auftragsbestaetigung ohne GARAN-Anhang.

## Nicht im ersten Umfang

- Variantenabhaengige GARAN-Daten.
- Unterschiedliche Garantiedauern fuer einzelne Attribute oder Attributkombinationen.
- Laenderabhaengige Garantiedauern oder Garantiebedingungen.
- Automatische Recherche nach Garantien auf Herstellerwebsites.
- Erkennung von Garantiebedingungen aus Freitexten oder Dokumenten.
- Redaktion und rechtliche Pruefung der Garantiebedingungen des Herstellers. Die technische Zuordnung und Bereitstellung bleiben Bestandteil der Erweiterung.
- Ausgabe fuer stationaere Verkaufsstellen oder frei formulierte Angebote ausserhalb der Bestellbearbeitung.
- Neue oder automatische Routinen fuer den Mailversand.
- Automatische Uebertragung zu externen Marktplaetzen.
- Barrierefreiheitsanpassungen der bestehenden Templates. Dafuer ist ein separates neues Template vorgesehen.

modified speichert Produktvarianten nicht als eigenstaendige, eindeutig adressierbare Kombinationen. Im ersten Schritt gelten GARAN-Daten deshalb immer fuer den gesamten Artikel.

## Abnahmekriterien

Die Erweiterung ist fachlich fertig, wenn:

- der Gewaehrleistungshinweis bei koerperlichen Waren vor der Bestellung sichtbar ist,
- GARAN-Daten im Admin korrekt gepflegt und validiert werden,
- das GARAN-Label auf Produktseite und im Checkout eindeutig zugeordnet ist,
- nur die erlaubten Felder der offiziellen Vorlage veraendert werden,
- Bestellungen von spaeteren Katalogaenderungen unabhaengige Hersteller-, Modell- und Garantiedaten speichern,
- der einer Bestellung zugeordnete historische Gewaehrleistungshinweis bei einem erneuten Mailversand erhalten bleibt,
- GARAN-Label bei vollstaendigen GARAN-Kerndaten unabhaengig davon ausgeben, ob Garantiebedingungen als Artikel-Anhang hinterlegt sind, dabei auf einen fehlenden Anhang hinweisen und die Verantwortung des Shopbetreibers fuer einen anderen rechtskonformen Bereitstellungsweg klar benennen,
- manuell angelegte Bestellungen dieselben GARAN-Snapshots und Ausgaben erhalten,
- GARAN-Snapshotwerte in der Bestellbearbeitung validiert, protokolliert und ohne Auswirkung auf den Katalog korrigiert werden koennen,
- beim Loeschen einer Bestellung oder einer Bestellposition keine verwaisten Modulzeilen zurueckbleiben,
- manuelle Auftragsbestaetigungen ausschliesslich ueber den bestehenden, vom Admin ausgeloesten Versandweg versendet werden,
- Bestellbestaetigungen die erforderlichen Hinweise enthalten,
- Auftragsbestaetigungen vorhandene archivierte Garantiebedingungen ueber den bestehenden Anhangsweg bereitstellen, bei fehlenden Bedingungen keinen GARAN-Anhang erzeugen und weder GARAN-Grafik noch Hinweisgrafik versenden; der Gewaehrleistungshinweis geht als Wortlaut mit Link mit,
- die Standardsprachen korrekt unterstuetzt werden und
- die Ausgabe auf Desktop und Mobilgeraet funktioniert.

## Geklaerte technische Punkte

- Der bestehende Mailweg unterstuetzt regulaere Anhaenge. Die Garantiebedingungen werden ueber `$email_attachments` in `includes/extra/send_order/data/` ergaenzt. HTML- und Text-Mail geben den sprachabhaengigen Gewaehrleistungstext mit direktem Link aus. `notice.svg`, daraus erzeugte Rastergrafiken und GARAN-Grafiken werden weder eingebettet noch angehaengt. Es gibt keine neue Mailroutine und keine CID-Einbettung.
- Die Frage, ob zusaetzlich die offizielle Grafik des Gewaehrleistungshinweises in die Bestellbestaetigung gehoert, wurde ausdruecklich geprueft und verneint. Der Praxisleitfaden nennt auf Seite 21 die Aufnahme des Hinweises in die Bestaetigungsmail, und § 312f Abs. 2 BGB verlangt die Vertragsinformationen auf einem dauerhaften Datentraeger. Beides erfuellt der mitgesendete Text mit Link. Die Grafik liegt als SVG vor, und SVG stellen Mailprogramme nicht verlaesslich dar; ein Anhang, den der Empfaenger nicht oeffnen kann, verbessert die Information nicht, sondern verschlechtert sie. Eine Rastergrafik daraus zu erzeugen waere technisch moeglich, waere aber nicht mehr die offizielle Datei und bringt gegenueber dem Wortlaut keinen Mehrwert.
- Technisch waere ein Anhang billig: `notice.svg` liegt je Bestellung archiviert unter `notice_hash`, und `$email_attachments` traegt bereits die Garantiebedingungen. Die Entscheidung ist also keine Aufwandsfrage, sondern eine bewusste inhaltliche.
- Garantiebedingungen verwenden die vorhandenen Artikel-Anhaenge. `products_content.content_type = 'garan_terms'` kennzeichnet je Artikel und Sprache die optional zu verwendende Datei; ein separater Upload oder Content-Manager-Datensatz ist nicht erforderlich.
- Die technische Zuordnung eines Garantie-Anhangs bleibt optional. Das Modul warnt bei fehlender Zuordnung, erzwingt § 479 BGB aber nicht, weil der Shopbetreiber die Garantieerklaerung auch ueber einen anderen dauerhaften Datentraeger bereitstellen kann und fuer diesen Bereitstellungsweg verantwortlich bleibt.
- Fuer `garan_terms` gelten die bereits vorhandenen erlaubten Dateiformate der Artikel-Anhangsverwaltung. Der Bestellsnapshot speichert mit `terms_filename` den bereinigten Dateinamen samt Erweiterung; eine PDF-Erzeugung oder zusaetzliche Formateinschraenkung gibt es nicht. Kommas im Dateinamen sind wegen der kommaseparierten Anhangsliste unzulaessig.
- Vorhandene Garantiebedingungen werden unter `DIR_FS_DOCUMENT_ROOT . 'media/guarantee_labels/archive/terms/<terms_hash>/<terms_filename>'` atomar auf Dateiebene archiviert. Dadurch koennen Dateien mit identischem Inhalt und unterschiedlichen Namen dasselbe Hashverzeichnis verwenden. Der Mailweg verwendet den Pfad und damit den tatsaechlichen Namen der Archivdatei; er vergibt keinen abweichenden Anhangsnamen.
- `products.products_garan_duration` und `products_content.content_type` werden ueber Installationsschema, Datenbankupdate und idempotent in `install()` angelegt. Die Modulinstallation prueft Modultabellen, Indizes und beide Core-Spalten, legt nur fehlende Bestandteile an und traegt den aktiven Status erst nach erfolgreicher Pruefung des gesamten Modulschemas ein. `add_db_fields` registriert nur `products_garan_duration` fuer den Speicherweg der Artikelverwaltung und ersetzt keine Schemaaenderung.
- Die Modulklasse verwendet `$this->code = 'guarantee_labels'` und anfangs `$this->version = '1.00'`; funktionale und schemarelevante Modulupdates erhoehen die Version. Das Systemmodul implementiert `update()` und verwendet dort dieselbe zentrale, idempotente Schemaroutine wie in `install()`. Der Konstruktor stellt dafuer ueber `$this->properties['button_update']` die vorhandene Admin-Aktion `Modul aktualisieren` in `admin/module_export.php?set=system` bereit. Bereits installierte Shops erhalten so spaetere Tabellen-, Spalten- und Indexaenderungen; eine Deinstallation ist nicht erforderlich.
- Beim Duplizieren eines GARAN-Artikels wird `products_garan_duration` auf `NULL` gesetzt, `products_manufacturers_model` geleert und `garan_terms` nicht mitkopiert. Das gilt unabhaengig vom Modulstatus, weil beide Felder dem Ursprungsartikel gehoeren. Bei Artikeln ohne GARAN-Daten bleibt das allgemeine Kopierverhalten unveraendert. Beim Anlegen einer Sprache werden Markierungen vom Typ `garan_terms` ebenfalls nicht aus einer anderen Sprache uebernommen.
- Der Produkt-CSV-Import und -Export verwendet ausschliesslich `p_garan_duration` fuer die Garantiedauer. `content_type` und Garantie-Anhaenge bleiben ausserhalb des CSV-Formats. Die Pruefung laeuft auch ohne diese Spalte in der Datei, weil schon eine geaenderte Modellkennung eine gespeicherte Dauer unbrauchbar machen kann.
- Eine abgelehnte Garantiedauer erreicht die Spalte weder ueber die Artikelverwaltung noch ueber den Import. Der Schluessel wird aus dem Datensatz entfernt statt `NULL` einzutragen, damit ein misslungener Speichervorgang keine gueltige gespeicherte Garantie loescht.
- Das sprachneutrale GARAN-Label haengt an keiner Sprachkonstante. Fehlen die Modultexte, faellt die Beschriftung auf `GARAN` zurueck und der Link zur Your-Europe-Seite entfaellt; die Grafik erscheint unveraendert.
- Cache- und Archivdateien werden vor jeder Verwendung gegen ihre `checksums.json` geprueft. Ein beschaedigtes Verzeichnis wird nicht ausgeliefert, sondern beim naechsten Schreibvorgang neu erzeugt; der Mailanhang wird zusaetzlich gegen `terms_hash` geprueft und im Zweifel weggelassen, wobei der Mailtext ihn dann auch nicht nennt.
- `includes/modules/products_media.php` stellt einen sichtbaren `garan_terms`-Anhang ueber den bestehenden Medienbereich bereit; ein neuer Storefront-Downloadweg ist nicht erforderlich.
- Die Anzahl der Herstellerabfragen richtet sich nach der Ausgabestelle. Wo der ganze Ergebnisblock vorliegt, genuegt eine Abfrage: Produktdetailseite, Warenkorbdetail und Merkzettel sammeln die Hersteller ihrer Positionen einmal vorab. Wo er nicht vorliegt, kommt der Puffer nach der zweiten Nachfrage auf zwei. Warenkorb und Merkzettel rufen ihren Haken allerdings innerhalb der Schleife auf, das Ergebnisarray ist beim ersten Aufruf einelementig. Gesammelt wird deshalb aus `$products`, das beide Kerndateien vor der Schleife vollstaendig aufbauen. Artikellisten ueber `buildDataArray()` und Bestellpositionen ueber `getOrderData()` sehen je Aufruf nur eine Zeile; dort bleibt es bei einer Abfrage je unterschiedlichem Hersteller der Seite. Fuehrt jeder Artikel einen eigenen Hersteller, entspricht das einer Abfrage je Artikel.
- Die Labeldaten werden nach dem Sammelprinzip bereitgestellt. Die vorhandenen `ADD_SELECT_*`-Arrays liefern Garantiedauer, Hersteller-Modellkennung und Hersteller-ID. Eine zentrale GARAN-Hilfsfunktion laedt die Namen der betroffenen aktiven Hersteller nach. Wo der ganze Ergebnisblock vorliegt, genuegt eine Abfrage; Artikellisten sehen je Artikel nur eine Zeile und kommen mit einer Abfrage je unterschiedlichem Hersteller der Seite aus. Ausschliesslich diese Namen werden fuer GARAN verwendet; bereits von anderen Abfragen gelieferte Herstellernamen bleiben unberuecksichtigt. Es werden weder Hersteller-JOINs in alle Kernabfragen noch Einzelabfragen je Artikel eingefuehrt.
- Das Modul leert keinen Cache. Der eigene Grafikcache liegt unter dem Inhaltshash und kann verwaisen, aber nicht falsch werden. Nur die Smarty-Blockcaches koennen ein veraltetes Label bis zum Ablauf von `CACHE_LIFETIME` weiter ausliefern; dafuer leert der Shopbetreiber den Cache ueber `admin/configuration.php?action=delcache`. Die Moduldiagnose weist darauf hin. Die historischen Archive werden dabei nicht geloescht.
- `clear_dir(DIR_FS_CATALOG.'cache/')` entfernt `cache/guarantee_labels/` einschliesslich aller darin liegenden Schutzdateien und danach das Verzeichnis selbst. Der Renderer legt das Basisverzeichnis vor einem Schreibvorgang bei Bedarf rekursiv neu an. Dauerhafte `.htaccess`- oder `index.html`-Dateien sind in diesem Cache-Unterverzeichnis nicht vorgesehen.
- Bei manuellen Bestellungen stammen `orders.language` und `orders.languages_id` aus der Backend-Sitzung beim Anlegen. Das Modul verwendet `orders.language` als massgebliche Bestellsprache fuer den Hinweis-Snapshot und die Auswahl von `garan_terms`; es ermittelt keine abweichende Kundensprache. Auch die benoetigte `languages_id` leitet es aus `orders.language` ab und liest sie nicht aus `orders.languages_id`: `orders_address_edit()` aendert beim Sprachwechsel nur die erste Spalte, die zweite bliebe auf der Sprache stehen, in der die Bestellung angelegt wurde.
- Der bestehende Admin-Ablauf befuellt `orders.content_type` beim Anlegen einer leeren Bestellung nicht. Das Modul liest die Spalte, wenn sie gefuellt ist, und schreibt sie nie. Bei leerer Spalte entscheiden die Positionen der Bestellung. Dadurch braucht es keinen zusaetzlich gepflegten Zustand, der nach einer Positionsaenderung falsch stehen bleiben koennte.
- Storefront und Checkout erzeugen `colour.svg` und `nested.svg` gemeinsam unter `cache/guarantee_labels/<garan_hash>/`. Beim Bestellabschluss werden beide Dateien einmalig unter `media/guarantee_labels/archive/garan/<garan_hash>/` archiviert. Das Archiv ist per eigener `.htaccess` nicht direkt ueber HTTP erreichbar.
- Der historische Gewaehrleistungshinweis liegt unter `media/guarantee_labels/archive/notice/<notice_hash>/`. `notice.svg` bewahrt die angezeigte Grafik; `notice.json` bewahrt Sprache, Mailtext, Linktext, Ueberschrift, Your-Europe-URL und Version. Ein erneuter Mailversand liest Text, Link und Ueberschrift aus diesem Snapshot statt aus aktuellen Sprachkonstanten. Fuer den aktuellen Hinweis im Storefront ist kein zusaetzlicher Cache erforderlich.
- Die zulaessige Laenge von Herstellername und Modellkennung wird nicht ueber eine feste Zeichenzahl entschieden. Der Renderer misst die tatsaechliche Textbreite serverseitig mit `imagettfbbox()`, der jeweiligen Inter-TTF-Datei, der vorgegebenen Schriftgroesse und einer festen Sicherheitstoleranz. Nicht passende Werte werden abgelehnt. GD mit FreeType ist Voraussetzung; ImageMagick und eine neue PHP-Bibliothek werden nicht benoetigt.
- Storefront und Checkout fuegen die gecachten SVGs inline ein. Eine zentrale Modul-CSS-Datei bindet die passenden Inter-WOFF2-Dateien per `@font-face` ein. Die Fonts werden nicht in jedes SVG kopiert; `images/.htaccess` erlaubt nur den HTTP-Zugriff auf WOFF2 und nicht auf TTF.
- B2B-Kundengruppen werden unter `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS` kommasepariert gespeichert. Die Modulkonfiguration verwendet dafuer `xtc_cfg_multi_checkbox('xtc_get_customers_statuses', 'chr(44)', ...)`; eine neue Auswahlfunktion ist nicht erforderlich. Standardmaessig ist keine Kundengruppe ausgeschlossen. Gastzugriffe bleiben B2C, solange ihre Kundengruppe nicht ausdruecklich ausgewaehlt wird.
- Die Moduldiagnose ist keine Konfigurationsoption und wird nur erzeugt, wenn `isset($_GET['module']) && $_GET['module'] == $this->code && $this->check() > 0` gilt. Dann wird sie ueber `$this->properties['add_content']` direkt unter der Modul-Infobox in `admin/module_export.php?set=system` ausgegeben. Sie besitzt keine eigene Adminseite und prueft das Schema, bevor sie weitere Diagnoseabfragen ausfuehrt.
- Die benoetigten Storefront-Process-Hooks `checkout_process_products_end/` und `checkout_process_order/` existieren bereits.
- Manuell eingefuegte Bestellpositionen werden ueber die neue Erweiterungsstelle `orders_functions/product_insert/` in `admin/includes/functions/orders_functions.php` verarbeitet. Sie liegt am Ende von `orders_product_insert()`, wo die `orders_products_id` bereits ermittelt ist, und folgt dem Muster der vorhandenen Stellen `product_edit/` und `product_delete/`.
- Allgemeine Bestellfunktionen und `includes/classes/order.php` greifen nur auf die Modultabellen zu, wenn diese existieren. Dadurch funktionieren Bestellansicht, manuelles Einfuegen und Loeschen auch bei nie installiertem oder deinstalliertem Modul.
- `orders_guarantee` erhaelt kein zusaetzliches Feld fuer den Bestellursprung und kein Feld wie `notice_displayed`. Eine vorhandene Zeile kennzeichnet ausschliesslich einen historischen Hinweis-Snapshot.
- `orders_guarantee` darf nicht als allgemeiner Nachweis einer Checkout-Anzeige oder eines Mailversands ausgewertet werden. Der Storefront erzeugt den Snapshot im Checkout fuer koerperliche Ware; der Admin erzeugt ihn bereits beim Anlegen der leeren Bestellung.
- Fuer die Checkout-Ausgabe wird kein neuer allgemeiner Hook benoetigt. Der Core weist eigene Smarty-Variablen zu; die mitgelieferten Templates erhalten feste Platzhalter. Externe Templates muessen diese Platzhalter uebernehmen.
- `xtc_remove_order()` erhaelt einen eigenen GARAN-Loeschblock mit Tabellenexistenzpruefung. Dadurch werden Bestellsnapshots auch nach einer Modul-Deinstallation entfernt. Bestehende Loeschlogik anderer Module bleibt unveraendert.
- Die GARAN-Ausgabe in Artikellisten folgt `SHOW_BUTTON_BUY_NOW`: mit direktem Warenkorb-Button kompaktes Label am Artikel, ohne direkten Warenkorb-Button erst in der Produktdetailansicht. Produktdetailansicht und Checkout zeigen das Label immer. Diese Regel gilt fuer alle vier mitgelieferten Templates.
- Der erste Umfang unterstuetzt keine laenderabhaengigen Garantien. Die Garantie muss fuer einen Artikel in allen vom Shop belieferten Laendern mit derselben Dauer und denselben Bedingungen gelten; deshalb gibt es keine Laenderfelder und keine Laufzeitpruefung gegen das Lieferland.
- Manuell angelegte Bestellungen benoetigen keinen Sperrzeitpunkt. Die GARAN-Daten werden beim Einfuegen des Artikels als Bestellsnapshot gespeichert; der Mailversand liest diesen Stand nur. Spaetere bewusste Korrekturen erfolgen ausschliesslich in der Bestellbearbeitung und werden protokolliert.
- Beim Anlegen einer manuellen B2C-Bestellung wird `orders_guarantee` sofort befuellt, wenn das Modul aktiv und die Bestellsprache vollstaendig gepflegt ist. Der Snapshot entsteht unabhaengig von den noch leeren Positionen. Reine virtuelle Bestellungen behalten ihn, unterdruecken aber seine Ausgabe in der Mail. Spaetere Aenderungen von Modulstatus, Kundengruppe oder Sprachdaten aktualisieren ihn nicht automatisch.
- `orders` benoetigt keine Spalte fuer das Vorhandensein rechtlicher Hinweise. Der Zustand ergibt sich indiziert aus `orders_guarantee` und `orders_products_guarantee`; so kann kein redundantes Flag vom tatsaechlichen Snapshot abweichen.
- `MODULE_GUARANTEE_LABELS_STATUS` ist der einzige Statusschluessel und Ausgabeschalter. Es gibt weder ein Aktivierungsdatum noch getrennte Schalter fuer einzelne Ausgabestellen. Bei aktivem Modul werden alle erforderlichen Ausgaben erzeugt; bei inaktivem Modul bleiben vorhandene Bestellsnapshots lediglich gespeichert.
- Garantie-Anhaenge sind optional. Fehlende Anhaenge blockieren weder die Pflege der GARAN-Kerndaten noch das sprachneutrale GARAN-Label. Je Bestellposition wird hoechstens ein in der Bestellsprache tatsaechlich vorhandener Anhang archiviert; andernfalls bleiben `terms_hash` und `terms_filename` `NULL`. Eine Bestellung kann damit mehrere unterschiedliche Anhaenge fuehren, die Mail haengt jede Datei genau einmal an.
- Sprachabhaengige Gewaehrleistungsgrafiken liegen direkt unter `lang/<Sprachverzeichnis>/notice.svg`. Mailtext, Linktext und Your-Europe-URL liegen in `lang/<Sprachverzeichnis>/extra/guarantee_labels.php`; Adminroutinen laden diese Datei fuer die Bestellsprache ausdruecklich. Titel, Beschreibung und Beschriftungen der Moduloptionen liegen in `lang/<Sprachverzeichnis>/modules/system/guarantee_labels.php`, weitere Admintexte unter `lang/<Sprachverzeichnis>/extra/admin/guarantee_labels.php`. Der Content Manager wird dafuer nicht verwendet. Die sprachneutralen GARAN-Vorlagen liegen unter `images/guarantee_labels/assets/`, die Inter-Schriften unter `images/guarantee_labels/fonts/` und erzeugte SVGs unter `cache/guarantee_labels/`. Dadurch kann ein nachtraeglich installiertes Sprachpaket die Funktion ohne Aenderung der Artikel vollstaendig bereitstellen.
- Fehlt in einer Sprache `notice.svg`, Mailtext, Linktext oder Your-Europe-URL, laeuft der Checkout ohne Gewaehrleistungshinweis weiter. Es gibt keinen Fallback und keine Checkout-Sperre. Die Moduldiagnose zeigt eine Warnung und `orders_guarantee` bleibt fuer diese Bestellung leer. Das sprachneutrale GARAN-Label wird weiterhin angezeigt.
- GARAN-Grafiken und Hinweis-Snapshots werden vollstaendig in temporaeren Nachbarverzeichnissen geschrieben, geprueft und erst danach atomar auf ihren Hashpfad umbenannt. Garantie-Anhaenge werden entsprechend atomar auf Dateiebene geschrieben. Fehler blockieren Bestellung und Mailversand nicht und erzeugen keine Dateiverweise auf fehlende Dateien. Ereignisse stehen in `mod_guarantee_labels_<level>_<datum>.log`; die Moduldiagnose zeigt aktuell feststellbare Fehler.
- Alle Fehler aus Admin-Aktionen werden ueber den bestehenden `messageStack` ausgegeben. Es gibt keinen eigenen Admin-Fehlermechanismus. Beim Aendern eines vorhandenen Bestellsnapshots bleibt der bisherige vollstaendige Stand erhalten; Teilaktualisierungen finden nicht statt.
- Admin-Aktionen mit Redirect verwenden fuer eigene Meldungen `messageStack->add_session()`, Aktionen ohne Redirect `messageStack->add()`. Das Artikelspeichern leitet weiter und verwendet deshalb `add_session()`, der Import leitet nicht weiter und verwendet `add()`. Insbesondere meldet `install()` Erfolg und Fehler selbst per Session. `update()` ueberlaesst die Erfolgsmeldung seinem Framework-Aufrufer; bei Fehlern setzt es eine Session-Meldung und gibt `false` zurueck.

## Bekannte Einschraenkung und Folgeprojekt

### Stabile Zuordnung von Bestellattribut und Downloadzeile

`orders_products_download` kennt keine Referenz auf `orders_products_attributes`. Die einzige Verbindung ist der Dateiname, und der steht nur im Katalog, der sich unabhaengig von der Bestellung aendert.

Ueber den Optionsnamen zu gehen waere naheliegend, weil die Attributverwaltung die Downloadfelder nur fuer eine Option namens `Downloads` anbietet. Es wurde erprobt und wieder verworfen: Benennt ein Shopbetreiber das Attribut einer Bestellung um, erkennt die Abfrage kein Downloadattribut mehr und wuerde alle gueltigen Zeilen der Position loeschen. Solange der Umsetzungsstand nicht sicher zuordnen kann, wird deshalb nichts entfernt und gemeldet; die Alternative wuerde Downloadrechte von Kunden entziehen oder erfinden.

Eine exakte Loesung braucht eine Spalte `orders_products_attributes_id` in `orders_products_download`, gefuellt beim Anlegen der Zeile und nachtraeglich fuer bestehende Bestellungen soweit ermittelbar. Damit entfaellt zugleich die Pflicht, die Option `Downloads` zu nennen: Ob ein Attribut ein Download ist, folgt dann aus dem Verweis und nicht aus einer Bezeichnung. Das ist eine Schemaaenderung an einer Core-Tabelle mit eigenem Datenbankupdate und gehoert nicht in dieses Modul.

Die Entscheidung ist getroffen: Der Stand bleibt beim sicheren Aufraeumen ohne Zuordnung. Die exakte Loesung ist als Issue #3280 festgehalten. Fuer GARAN bleibt offen, dass nach dem Loeschen eines Downloadattributs eine Zeile stehen bleiben kann; die Position gilt dann als digital, der Gewaehrleistungshinweis entfaellt und der Shopbetreiber erhaelt eine Meldung.
