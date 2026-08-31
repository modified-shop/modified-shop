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

- Gewaehrleistungshinweis im Checkout vollstaendig und direkt anzeigen.
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
- Vollstaendiges Label beim ersten Klick, Touch oder Mouseover anzeigen.
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

Die Trennung von Speichern und Ausgeben ist bewusst: Beim direkten Lesen der Datenbank oder beim Abgleich mit einem Fremdsystem ist `2` eine eindeutige Aussage, `NULL` dagegen nur die Abwesenheit einer Aussage. Fremdsysteme liefern die Anzahl Jahre in beliebiger Hoehe; ein Import darf an keinem dieser Werte scheitern und soll den gelieferten Stand unveraendert abbilden. Ob ein Label entsteht, entscheidet allein die Pruefung auf mehr als zwei Jahre an einer Stelle im Renderer.
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
| Optional vorhandene Datei des sprachabhaengigen Artikel-Anhangs | Archivdatei unter `media/products/garan_archive/<terms_hash>/<terms_filename>` |
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
notice_hash = sha256( Inhalt notice.svg + Mailtext + Linktext + Your-Europe-URL + Version )
```

Die Schreibweise mit `+` beschreibt nur die fachlichen Bestandteile. Technisch werden die Werte als UTF-8 in einer fest definierten Reihenfolge und mit eindeutigen Feldgrenzen serialisiert. Dezimalwerte werden vorher kanonisch normalisiert. Vorlagen und Schriften gehen mit dem SHA-256-Hash ihres tatsaechlichen Dateiinhalts ein. Dadurch koennen unterschiedliche Feldaufteilungen nicht denselben Eingabestrom erzeugen.

Aendert sich ein Bestandteil, aendert sich der zugehoerige Hash. `garan_hash` ist der gemeinsame Cache- und Archivschluessel fuer die zusammengehoerige farbige und kompakte Variante. `notice_hash` referenziert den vollstaendigen historischen Sprachstand aus Grafik, Mailtext, Linktext und URL.

Mailtext und Anhangsliste fragen dieselbe Funktion `guarantee_labels_terms_file()`. Getrennt gefragt koennte der Text ein Dokument nennen, das die Anhangspruefung anschliessend als beschaedigt verwirft, und die Mail wuerde etwas behaupten, das sie nicht mitfuehrt.

Ist in der Bestellsprache ein Artikel-Anhang vom Typ `garan_terms` vorhanden, enthaelt `orders_products_guarantee.terms_hash` den SHA-256-Hash seines Inhalts. Der Hash referenziert die Fassung der Garantiebedingungen, die zur Bestellposition gehoert. Ein erneuter Mailversand haengt genau diese archivierte Fassung an, unabhaengig davon, ob der Artikel-Anhang inzwischen ersetzt oder geloescht wurde. Fehlt ein solcher Anhang, bleiben `terms_hash` und `terms_filename` `NULL` und es wird keine Garantieerklaerung angehaengt.

`terms_filename` speichert den bereinigten Dateinamen einschliesslich Erweiterung. Der bestehende Mailweg bietet keinen separaten Namen fuer den Anhang an, sondern uebergibt nur den Dateipfad an PHPMailer. Die Archivdatei muss deshalb selbst unter diesem Dateinamen gespeichert werden. Pfadbestandteile, Steuerzeichen, Kommas und sonstige unzulaessige Dateinamen sind abzulehnen. Ein Komma darf nicht ersetzt oder maskiert werden, weil `check_attachments()` die Anhangsliste mit `explode(',', ...)` zerlegt.

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
└── notice/
    └── <notice_hash>/
        ├── notice.svg
        └── notice.json
```

`notice.json` speichert Sprache, Mailtext, Linktext, Your-Europe-URL und Version des bei der Snapshoterzeugung verwendeten Hinweises. Die erste und jede erneute Bestellbestaetigung lesen Text und Link anhand von `orders_guarantee.notice_hash` aus diesem Snapshot. Spaetere Aenderungen an Sprachkonstanten oder URLs veraendern bestehende Bestellungen nicht.

Jedes Hashverzeichnis erhaelt beim Schreiben eine `checksums.json` mit dem SHA-256 jeder abgelegten Datei. Beim Lesen wird jede Datei gegen ihren Eintrag geprueft; ein Verzeichnis mit abweichendem Inhalt gilt als nicht lesbar. Ein unlesbarer Sidecar zaehlt als Schaden und nicht als Archiv ohne Sidecar. Ist ein Sidecar vorhanden, muss er jede gelesene Datei nennen; ein entfernter Eintrag wuerde sonst eine ersetzte Datei decken. Ein beschaedigtes Hashverzeichnis wird beim naechsten Schreibvorgang verworfen und neu angelegt, weil sein Inhalt aus seinem Namen folgt.

Auch die Cache-URL des vollstaendigen Labels wird gegen den Sidecar geprueft. Faellt die Pruefung durch, verweist die Seite nicht auf die Datei, sondern bettet die Grafik der aktuellen Anfrage ein. Der Verzeichnisname deckt die Eingangsdaten des Labels ab und nicht die Bytes der erzeugten Dateien, deshalb ist die Pruefsumme der einzige Weg, ein beschaedigtes Archiv von einem intakten zu unterscheiden. Aeltere Archive ohne Datei bleiben unveraendert lesbar.

Der Anhang mit den Garantiebedingungen wird beim Mailversand zusaetzlich gegen `terms_hash` geprueft. Passt der Inhalt nicht mehr, protokolliert das Modul den Fall und laesst den Anhang weg, statt eine ersetzte Datei zu versenden.

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

Das getrennte Archiv fuer Garantie-Anhaenge liegt unterhalb von `DIR_FS_DOCUMENT_ROOT`, damit `check_attachments()` die Datei ueber den bestehenden Mailweg findet. Garantie-Anhaenge werden weiterhin unter `media/products/garan_archive/<terms_hash>/<terms_filename>` archiviert. Der Inhalts-Hash bildet das Verzeichnis; die Datei selbst traegt den in `terms_filename` gespeicherten Namen. Der Dateipicker in `admin/includes/modules/content_manager_products.php` liest nur Dateien direkt unter `media/products/` und ueberspringt Verzeichnisse. Das Unterverzeichnis `garan_archive/` und seine archivierten Dateien erscheinen deshalb nicht als unbenutzte Artikeldateien im Picker.

### Fehlerverhalten bei Cache und Archiv

Fehler beim erstmaligen Erzeugen oder Archivieren eines Snapshots blockieren weder den normalen Checkout noch das manuelle Anlegen einer Bestellung.

- Vor einem Cache-Schreibvorgang legt der Renderer `cache/guarantee_labels/` bei Bedarf rekursiv neu an. Danach werden die Dateien zuerst in einem eindeutigen temporaeren Verzeichnis darunter vollstaendig geschrieben und geprueft. Erst anschliessend wird das Verzeichnis atomar auf `<garan_hash>` umbenannt. Existiert das Ziel bereits, werden die vorhandenen Dateien geprueft und wiederverwendet. Ist ein vorhandener Cache unvollstaendig oder beschaedigt, wird er nicht verwendet; die aktuelle Anfrage verwendet direkt erzeugte SVGs und ein spaeterer Schreibversuch darf den Cache kontrolliert neu aufbauen.
- Kann lediglich der Cache nicht geschrieben werden, darf der Renderer die erzeugten SVGs fuer die aktuelle Anfrage direkt verwenden. Der Fehler wird protokolliert und in der Moduldiagnose angezeigt.
- Die zusammengehoerigen GARAN-Grafiken sowie `notice.svg` und `notice.json` werden zuerst in einem eindeutigen temporaeren Verzeichnis neben dem endgueltigen Ziel vollstaendig geschrieben und geprueft. Erst danach wird das komplette Verzeichnis atomar auf den Hashpfad umbenannt.
- Garantie-Anhaenge werden atomar auf Dateiebene archiviert. Das Hashverzeichnis wird bei Bedarf angelegt. Die Datei wird unter einem eindeutigen temporaeren Namen im selben Verzeichnis geschrieben, anhand von `terms_hash` geprueft und danach auf `<terms_filename>` umbenannt. Existiert die Zieldatei bereits, wird sie geprueft und wiederverwendet. Bei gleichem Dateiinhalt und unterschiedlichem Dateinamen duerfen mehrere Dateien im selben Hashverzeichnis liegen.
- Eine Zeile in `orders_guarantee` wird erst geschrieben, wenn `notice.svg` und `notice.json` vollstaendig archiviert und geprueft sind. Schlaegt dies fehl, laeuft die Bestellung weiter und es entsteht keine Zeile.
- Eine Zeile in `orders_products_guarantee` wird erst geschrieben, wenn `colour.svg` und `nested.svg` vollstaendig archiviert und geprueft sind. Schlaegt dies fehl, laeuft die Bestellung weiter und es entsteht keine Zeile fuer die betroffene Position.
- Ist beim erstmaligen Erzeugen eines Snapshots ein optionaler Garantie-Anhang vorhanden, kann aber nicht archiviert werden, wird der GARAN-Kerndatensatz ohne Dateiverweis gespeichert. `terms_hash` und `terms_filename` bleiben beide `NULL`. Der Fehler darf nicht wie ein regulaer fehlender Anhang behandelt werden, sondern wird ausdruecklich protokolliert. Besteht die Ursache weiterhin, zeigt sie auch die Moduldiagnose.
- Fehlerprotokolle enthalten mindestens Bestell-ID, gegebenenfalls Bestellpositions-ID, Hash, Zielpfad, Fehlerart und Zeitpunkt. Sie enthalten keine vollstaendigen Kunden- oder Zahlungsdaten.
- Ein fehlendes oder beschaedigtes `notice.json` oder ein fehlender Garantie-Anhang wird beim Mailversand nicht durch aktuelle Katalog- oder Sprachdaten ersetzt. Der betroffene Mailinhalt wird uebersprungen, der Versand laeuft weiter und der Fehler wird protokolliert. Fehlende GARAN-Archivgrafiken betreffen nur die historische Anzeige im Admin und haben keinen Einfluss auf den Mailversand.
- Der Admin erhaelt fuer Fehler bei jeder manuell ausgeloesten Aktion eine Fehlermeldung ueber den bestehenden `messageStack`. Bei Aktionen mit anschliessendem Redirect verwendet GARAN `$messageStack->add_session()`, damit die Meldung den naechsten Request erreicht. Das gilt insbesondere fuer `install()`, fehlgeschlagene `update()`-Aufrufe und Herstelleraktionen. Aktionen ohne Redirect wie Artikelspeichern, Bestellbearbeitung und Import verwenden `$messageStack->add()`. Eine zusaetzliche Admin-Fehlerverwaltung ist nicht erforderlich. GARAN verwendet den vorhandenen `LoggingManager` mit dem Dateimuster `DIR_FS_LOG . 'mod_guarantee_labels_%s_%s.log'`. Daraus entstehen Dateien wie `mod_guarantee_labels_error_2026-08-27.log`, die von der bestehenden Logverwaltung angezeigt und von der vorhandenen Logpflege erfasst werden. Die Moduldiagnose zeigt aktuell feststellbare Fehler wie fehlende Schreibrechte, unvollstaendige Sprachen oder fehlende Archivdateien. Ein zusaetzlicher persistenter Fehlerspeicher und ein Status `geprueft` gehoeren nicht zum Umfang.

Durch diese Reihenfolge verweist keine Datenbankzeile auf ein nur teilweise geschriebenes Archiv. Es gibt keine automatische nachtraegliche Neuerzeugung eines fehlgeschlagenen Bestellsnapshots.

### Installation der Tabellen

Die Tabellen `orders_guarantee` und `orders_products_guarantee` entstehen in der `install()`-Routine des Systemmoduls per `CREATE TABLE IF NOT EXISTS`, nicht im Installationsschema. Fuer diese beiden Modultabellen ist deshalb kein Datenbankupdate erforderlich. Die Modulkonfiguration wird erst eingetragen, nachdem auch diese Tabellen und ihre erforderlichen Indizes geprueft wurden.

Die beiden Core-Felder `products.products_garan_duration` und `products_content.content_type` stehen weiterhin im Installationsschema und im regulaeren Datenbankupdate. Zusaetzlich legt die `install()`-Routine des Systemmoduls beide Spalten selbst idempotent an. Sie folgt dem vorhandenen Muster aus `admin/includes/modules/system/products_tariff.php`:

1. Modultabellen mit `CREATE TABLE IF NOT EXISTS` anlegen und ihre Indizes pruefen.
2. Core-Spalte mit `SHOW COLUMNS FROM <Tabelle> LIKE <Spalte>` pruefen.
3. Nur eine fehlende Core-Spalte mit `ALTER TABLE` anlegen.
4. Danach erneut pruefen, dass Modultabellen, Indizes und beide Core-Spalten mit dem vorgesehenen Schema vorhanden sind.
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

- Das Feld gilt ausschliesslich fuer eine Haltbarkeitsgarantie des Herstellers, die ueber die gesetzliche Gewaehrleistung hinausgeht.
- Die gesetzlichen zwei Jahre gehoeren nicht in dieses Feld. Auf sie weist der Gewaehrleistungshinweis im Checkout fuer die gesamte Bestellung hin, ohne Pflege am Artikel.
- Ein leeres Feld ist der Normalfall. Ein Artikel mit ausschliesslich der gesetzlichen Gewaehrleistung erhaelt korrekterweise kein GARAN-Label.
- Genau zwei Jahre Herstellergarantie reichen nicht, weil sie dem Verbraucher nichts ueber die gesetzliche Gewaehrleistung hinaus geben. Auch die Fehlermeldung bei einer abgelehnten Eingabe nennt diesen Grund.

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

Eine abgelehnte Eingabe erreicht die Spalte nicht. `insert_product_before()` entfernt `products_garan_duration` bei einem Fehler aus dem Datensatz, statt `NULL` einzutragen. Die Artikelverwaltung schreibt die Zeile, bevor sie ueber `insert_product_error()` vom Fehler erfaehrt; ein Eintrag an dieser Stelle wuerde also eine gueltige gespeicherte Garantie bei einem Speichervorgang loeschen, den der Shopbetreiber gar nicht durchbekommen hat. Ein bewusst geleertes Feld bleibt davon unberuehrt und setzt weiterhin `NULL`.

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
- Ist der resultierende GARAN-Datensatz unvollstaendig oder ungueltig, laesst der Import `products_garan_duration` unangetastet. Ein bestehender Artikel behaelt seine gespeicherte Dauer, ein neuer Artikel bekommt den Standardwert der Spalte. Der Admin erhaelt die konkrete Fehlermeldung ueber den `messageStack`.
- Die uebrigen Felder der Produktzeile werden regulaer importiert. Die Erweiterungsstelle `insert_before` kann den Import einer Zeile nicht abbrechen, und ein ganzer Artikelimport soll nicht an einem einzelnen GARAN-Feld scheitern. Ausdruecklich nicht vorgesehen ist der umgekehrte Weg: den geprueften Wert trotzdem zu schreiben. Er wuerde eine gute gespeicherte Dauer durch `NULL` ersetzen, nur weil die CSV in dieser Zeile eine ungueltige Angabe enthielt.
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

Das Modul prueft bei der Aktivierung, ob GD mit FreeType und `imagettfbbox()` verfuegbar ist. Fehlt diese Voraussetzung, kann das Modul nicht aktiviert werden und nennt die fehlende Funktion ueber den `messageStack`. Die Moduldiagnose zeigt den Zustand zusaetzlich an. ImageMagick und eine zusaetzliche PHP-Bibliothek werden nicht benoetigt.

Die WOFF2-Dateien dienen der Browserausgabe. Eine zentrale Modul-CSS-Datei bindet sie per `@font-face` ein. Die gecachten SVGs werden inline in das HTML eingefuegt, damit sie dieselben zentral geladenen Schriften verwenden und die Interaktion zwischen kompakter und vollstaendiger Darstellung ohne eingebettete Fontkopien funktioniert. `images/.htaccess` wird um die Dateiendung `.woff2` erweitert. Dadurch bleibt `.ttf` fuer direkte HTTP-Aufrufe gesperrt und steht PHP weiterhin lokal fuer die Messung zur Verfuegung.

Der Renderer muss Eingaben XML-sicher maskieren. Produktdaten duerfen keinen eigenen SVG- oder HTML-Code einschleusen.

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
- Artikellisten laufen ueber die Klassenerweiterung `includes/modules/product/guarantee_labels_listing.php`. `buildDataArray()` wird je Artikel aufgerufen; der Block liegt dort nicht vor. Die Zahl der Abfragen bleibt durch den Puffer auf die unterschiedlichen Hersteller der Seite begrenzt, nicht auf eine. Eine Abfrage je Artikel entsteht nicht.
- Die GARAN-Ausgabe verwendet ausschliesslich den Namen aus dieser eigenen Abfrage aktiver Hersteller. Bereits von einer anderen Produktabfrage mitgelieferte Werte in `manufacturers_name` werden fuer das Label ignoriert. Das gilt insbesondere fuer `includes/modules/new_products.php`, dessen vorhandener `LEFT JOIN` den Herstellerstatus nicht prueft.
- Fehlt die Hersteller-ID, ist der Hersteller inaktiv oder liefert die Abfrage keinen Namen, ist der GARAN-Datensatz unvollstaendig und der Artikel erhaelt kein Label.
- Bei deaktiviertem Modul sowie in Artikellisten mit `SHOW_BUTTON_BUY_NOW = false` erfolgt keine GARAN-Nachladeabfrage.

Die Nachladelogik wird in den vorhandenen Ausgabepipelines aufgerufen:

- alle Ausgaben ueber `product->buildDataArray()`: Produktlisting fuer Kategorie, Suche, Sonderangebote und neue Artikel, Cross-Selling, Reverse-Cross-Selling, ebenfalls gekaufte Artikel, Artikel einer Kategorie und die kommenden Artikel,
- Produktdetailseite ueber `includes/extra/modules/product_info_end/`; der dort bereits geladene Herstellername wird fuer das GARAN-Label ebenfalls nicht direkt verwendet,
- Warenkorb und daraus erzeugte Checkout-Produktdaten,
- weitere direkte Bestelllisten, die `$product->default_select` verwenden.

Der Merkzettel baut seine Produktdaten in `inc/get_wishlist_content.inc.php` selbst auf und laeuft nicht ueber `buildDataArray()`. Er bleibt deshalb ohne Label.

Die Sonderangebotsseite besitzt in diesem Stand keine eigene Abfrage unter `includes/modules/specials.php`, sondern verwendet `includes/modules/default.php` und `includes/modules/product_listing.php`. Cross-Selling verwendet `includes/classes/product.php` und `includes/modules/cross_selling.php`; eine Datei `includes/modules/xsell_products.php` existiert nicht. Die Roadmap verwendet deshalb die tatsaechlichen Erweiterungs- und Ausgabestellen dieses Quellstands.

### Checkout

Der Checkout zeigt:

1. Beim jeweiligen Artikel das GARAN-Label, sofern vorhanden.
2. Den vollstaendigen Gewaehrleistungshinweis vor Abgabe der Bestellung.

Der Hinweis steht in jedem mitgelieferten Template als eigener Block neben Versandart, Zahlungsweise und Bemerkungen, im jeweiligen Aufbau des Templates. Das Modul liefert ihn dafuer in Bestandteilen: `GUARANTEE_NOTICE_TITLE` und `GUARANTEE_NOTICE_BODY`. `GUARANTEE_NOTICE` enthaelt denselben Inhalt mit eigenem Rahmen und eigener Ueberschrift, fuer ein Template, das den Hinweis als fertiges Stueck setzen will.

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

Die drei GARAN-Werte werden ueber `ADD_SELECT_CART` und die gesammelte Herstellerabfrage in die Warenkorb- und Checkout-Produktdaten aufgenommen und dort zentral validiert. Der Snapshot verwendet diese ausdruecklich erweiterten Daten. Er darf nicht voraussetzen, dass die bestehende Warenkorbabfrage den Herstellernamen bereits liefert.

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
- Nur bei leerem `orders.content_type`, also bei einer manuell angelegten Bestellung, entscheiden die Positionen: Eine Position ohne Zeile in `orders_products_download` ist koerperliche Ware. Sobald mindestens eine solche Position existiert, gilt die Bestellung als koerperlich. Eine Bestellung ohne Positionen gilt als nicht koerperlich.
- Das Modul schreibt die Spalte nie. Eine spaetere Positionsaenderung wirkt sofort, weil nichts zwischengespeichert wird, das nachgezogen werden muesste.
- Der Gewaehrleistungshinweis wird nur ausgegeben, wenn diese Pruefung koerperliche Ware findet.

Nach dem Insert in `orders_products` wird der GARAN-Snapshot direkt in `orders_product_insert()` in `admin/includes/functions/orders_functions.php` erzeugt. Direkt hinter `xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array)` wird die neue `orders_products_id` mit `xtc_db_insert_id()` ermittelt und die zugehoerige Zeile in `orders_products_guarantee` geschrieben. Dafuer wird keine neue Erweiterungsstelle angelegt.

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
- Bestellung drucken (`admin/print_order.php`): ebenso als Textzeile, ohne den Anhang. Der Beleg fuehrt keine Datei mit.
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
- Historischen Linktext und die historische Your-Europe-URL aus demselben Snapshot bereitstellen.
- Weder `notice.svg` noch eine daraus erzeugte Rastergrafik einbetten oder anhaengen.
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

Eine Sprache ist fuer den Gewaehrleistungshinweis nur vollstaendig gepflegt, wenn sie alle folgenden Bestandteile bereitstellt:

- `lang/<Sprachverzeichnis>/notice.svg` fuer Storefront und Checkout,
- eine Sprachkonstante fuer den Mailtext,
- eine getrennte Sprachkonstante fuer den Linktext,
- eine getrennte Sprachkonstante fuer die sprachabhaengige Your-Europe-URL.

Die mitgelieferten Storefront- und Mailkonstanten liegen in diesen automatisch geladenen Dateien:

```text
lang/german/extra/guarantee_labels.php
lang/english/extra/guarantee_labels.php
```

Sie definieren mindestens `TEXT_GUARANTEE_NOTICE_MAIL`, `TEXT_GUARANTEE_NOTICE_LINK` und `TEXT_GUARANTEE_NOTICE_URL`. Alle Storefront-Konstanten des Moduls tragen das Praefix `TEXT_GUARANTEE_`; `MODULE_GUARANTEE_LABELS_` bleibt der Modulkonfiguration vorbehalten. Storefront-Requests laden diese Dateien ueber die vorhandene `extra/`-Sprachlogik. Adminroutinen, die einen Hinweis-Snapshot erzeugen, laden die Datei aus `lang/<orders.language>/extra/guarantee_labels.php` ausdruecklich, weil die Admin-Sprachinitialisierung die Storefront-Datei nicht automatisch einbindet. Admintexte ausserhalb der Modulverwaltung liegen entsprechend unter `lang/german/extra/admin/guarantee_labels.php` und `lang/english/extra/admin/guarantee_labels.php`.

`guarantee_labels_language()` gibt bereits geladene Konstanten nur frei, wenn sie zur angefragten Sprache gehoeren. Im Storefront stammen sie aus der Sprache der Sitzung, also der Sprache, in der der Kunde gerade blaettert, und nicht zwingend aus der Bestellsprache. Ohne diese Bindung wuerde die Bestellansicht im Kundenkonto eine englische Bestellung mit deutschen Beschriftungen versehen.

Die Konfigurationssprache des Systemmoduls liegt getrennt unter:

```text
lang/german/modules/system/guarantee_labels.php
lang/english/modules/system/guarantee_labels.php
```

Diese beiden Dateien definieren mindestens `MODULE_GUARANTEE_LABELS_TEXT_TITLE`, `MODULE_GUARANTEE_LABELS_TEXT_DESCRIPTION`, `MODULE_GUARANTEE_LABELS_STATUS_TITLE`, `MODULE_GUARANTEE_LABELS_STATUS_DESC`, `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_TITLE` und `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_DESC`. Damit kann der Konstruktor Titel und Beschreibung zuweisen und die vorhandene Modulverwaltung beide Konfigurationsschluessel beschriften.

Der Text kommt nicht aus dem Content Manager. HTML- und Text-Mail verwenden dieselben Sprachkonstanten und formatieren Text und Link passend zum jeweiligen Mailformat. Der Link wird nicht als HTML in den Mailtext eingebettet.

Das GARAN-Label enthaelt bereits alle erforderlichen Sprachfassungen und ist sprachneutral. Seine offiziellen Vorlagen liegen zentral unter `images/guarantee_labels/assets/` und nicht in den Sprachordnern. Die benoetigten Inter-Schriften liegen unter `images/guarantee_labels/fonts/`. Weitere Shopsprachen gehoeren nicht zum mitgelieferten Umfang der Erweiterung. Ein stiller Fallback auf Deutsch oder Englisch ist nicht vorgesehen.

Beim Anlegen einer neuen Sprache kopiert `admin/languages.php` auf Wunsch vorhandene Artikel-Anhaenge. Zeilen mit `content_type = 'garan_terms'` werden dabei nicht in die neue Sprache kopiert. Der Shopbetreiber muss die Garantieerklaerung fuer die neue Sprache ausdruecklich zuordnen. Andere Artikel-Anhaenge folgen weiterhin dem bestehenden Kopierverhalten.

Diese Stelle besitzt keine Erweiterungsstelle und wird deshalb als gezielter Eingriff in `admin/languages.php` umgesetzt. Sie prueft ausnahmsweise nicht den Modulstatus: `products_content.content_type` ist ein Core-Feld, und eine Zeile sagt ausdruecklich, dass diese Datei die Garantieerklaerung einer bestimmten Sprache ist. Sie in eine andere Sprache zu kopieren waere unabhaengig vom Modulstatus eine falsche Zuordnung. Bei inaktivem Modul liest ohnehin niemand das Feld, ein Schaden entsteht durch die Ausnahme also nicht.

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
- fehlende oder beschaedigte historische Archivdateien.

`ausgewaehlt` bedeutet damit ausdruecklich, dass `$_GET['module']` vorhanden ist und exakt dem Modulcode entspricht. Ist kein Modulparameter gesetzt, kann das Framework zwar das erste Modul als `$mInfo` anzeigen, GARAN erzeugt in diesem Fall aber weder Diagnoseinhalt noch Diagnoseabfragen. Dasselbe gilt, wenn ein anderes Modul ausgewaehlt ist oder GARAN noch nicht installiert wurde.

Die Diagnose prueft zuerst Tabellen und Core-Spalten. Fehlen Schemaelemente, zeigt sie diesen Zustand an und fuehrt keine Abfrage gegen die fehlenden Tabellen oder Spalten aus. Dynamische Namen, Pfade und Meldungen werden vor der HTML-Ausgabe maskiert. Die Diagnose aendert keine Daten und blockiert weder Storefront noch Checkout.

Fuer alle Admin-Bereiche gilt einheitlich: Fehler aus einer vom Admin ausgeloesten Aktion werden ueber den bestehenden `messageStack` ausgegeben. Pfade mit anschliessendem Redirect verwenden `add_session()`, Pfade ohne Redirect verwenden `add()`. Das betrifft insbesondere Modulinstallation, Modulaktualisierung und -aktivierung, Artikel- und Anhangspflege, Import, manuelles Anlegen einer Bestellung und Bestellbearbeitung. Es wird kein eigener Mechanismus fuer Admin-Fehlermeldungen eingefuehrt.

Der einheitliche Konfigurationsschluessel fuer den Modulstatus lautet:

```text
MODULE_GUARANTEE_LABELS_STATUS
```

`install()` legt diesen Schluessel nach erfolgreicher Schemaanlage mit dem Wert `true` an. Konstruktor, `check()` und `keys()` des Systemmoduls verwenden exakt denselben Namen. `remove()` entfernt die Modulkonfiguration ueber das gemeinsame Praefix `MODULE_GUARANTEE_LABELS_`, laesst aber Tabellen und Spalten bestehen.

`add_db_fields`, `define_add_select`, Storefront-Ausgaben, Checkout, das Erzeugen neuer Bestellsnapshots und die ausgabebezogenen Admin-Hooks pruefen ausschliesslich `defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true'`. Abweichende oder verkuerzte Statusnamen werden nicht verwendet. Installation, Entfernung, ein Wechsel des Statuswerts und die Bereinigung bereits vorhandener Bestelldaten muessen auch ohne aktuell aktiven Status sicher arbeiten; diese Lebenszyklus- und Aufraeumvorgaenge werden deshalb nicht durch die Aktivpruefung unterdrueckt.

Dieser Modulstatus ist der einzige Schalter fuer die Ausgabe. Ein Aktivierungsdatum und einzelne Schalter fuer Produktseite, Artikellisten, Checkout oder Bestellbestaetigung gibt es nicht.

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
- `admin/content_manager.php` und `admin/includes/modules/content_manager_products.php` fuer den Typ des Artikel-Anhangs.
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
- `media/guarantee_labels/archive/` einschliesslich eigener `.htaccess` fuer historische GARAN-Dateien und Gewaehrleistungshinweise.
- `media/products/garan_archive/` mit eigener `.htaccess` fuer atomar archivierte Garantie-Anhaenge. Die uebergeordnete `media/.htaccess` sperrt nur Skriptendungen; das historische Archiv braucht eine eigene Sperre, waehrend die Katalogdatei unter `media/products/` erreichbar bleibt.
- `includes/classes/class.logger.php`, `admin/logs.php` und die vorhandene Logpflege fuer `mod_guarantee_labels_<level>_<datum>.log`.
- `admin/configuration.php` als vorhandener Weg, den Shopcache zu leeren. Das Modul leert selbst keinen Cache.

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
- Modulstatus und B2B-Auswahl ueber `admin/module_export.php?set=system` speichern; `process()` wird nach dem Speichern aufgerufen, liest den neuen Status aus `TABLE_CONFIGURATION` statt aus der in diesem Request veralteten Konstante und leert keinen Cache. Wurde das Modul dabei ohne GD mit FreeType aktiviert, setzt `process()` den Status wieder auf `false` und meldet die fehlende Voraussetzung.
- GARAN-Systemmodul ueber den regulaeren Adminweg aufrufen; Installation, Bearbeitung, Aktualisierung und Diagnose laufen ausschliesslich ueber `admin/module_export.php?set=system`. `admin/modules.php` benoetigt fuer GARAN weder einen Speicherpfad noch eine Cache-Erweiterung.
- B2B-Mehrfachauswahl in deutscher und englischer Adminsprache anzeigen; `xtc_cfg_multi_checkbox('xtc_get_customers_statuses', 'chr(44)', ...)` verwendet die lokalisierten Kundengruppen aus `xtc_get_customers_statuses()`.
- Keine, eine und mehrere B2B-Kundengruppen speichern; `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS` enthaelt entsprechend `''`, eine ID oder kommaseparierte IDs und wird beim Lesen in eindeutige positive Integerwerte normalisiert.
- Gastzugriff ohne ausdruecklich ausgewaehlte Gast-Kundengruppe bleibt B2C; nach ausdruecklicher Auswahl folgt er der B2B-Ausschlussregel.
- Installiertes GARAN-Modul in `admin/module_export.php?set=system&module=guarantee_labels` ausdruecklich auswaehlen; die Bedingung aus `$_GET['module']`, `$this->code` und `check()` ist erfuellt und `properties['add_content']` zeigt die Diagnose direkt unter der Modul-Infobox.
- Diagnose bei fehlender Modultabelle oder Core-Spalte aufrufen; sie meldet das fehlende Schema, ohne eine Abfrage gegen das fehlende Element auszufuehren.
- Diagnose mit fehlendem GD-FreeType, nicht beschreibbarem Cache oder Archiv, unvollstaendiger Sprache, unvollstaendigem Produkt, mehrfach markierter `content_file` und fehlender Archivdatei pruefen.
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
- Eingaben mit `2,5` und `2.5` identisch normalisieren.
- Ganzjahreswert groesser als `99` ablehnen.
- Halbjahreswert groesser als `99.5` ablehnen.
- Halbe Jahre im Label mit Komma darstellen, zum Beispiel `2,5` statt `2.5`.
- GARAN-Daten ohne Garantie-Anhang speichern und das sprachneutrale Label trotzdem ausgeben.
- Vorhandenen Artikel-Anhang je Sprache als `garan_terms` markieren.
- Reinen externen Link als Garantieerklaerung ablehnen.
- Anhang eines anderen Artikels oder einer anderen Sprache ablehnen.
- Unterschiedliche von der vorhandenen Artikel-Anhangsverwaltung erlaubte Dateiformate als `garan_terms` verwenden und unveraendert archivieren.
- Dateiname und Erweiterung sicher in `terms_filename` uebernehmen; Pfadbestandteile, Steuerzeichen und Kommas ablehnen.
- Archivdatei unter `DIR_FS_DOCUMENT_ROOT . 'media/products/garan_archive/<terms_hash>/<terms_filename>'` anlegen und mit ihrem gespeicherten Dateinamen versenden.
- Zwei inhaltlich identische Garantie-Anhaenge mit unterschiedlichen Dateinamen atomar als zwei Dateien im selben `terms_hash`-Verzeichnis archivieren.
- Dateinamen mit Komma ablehnen und pruefen, dass sie die kommaseparierte Anhangsliste nicht erreichen.
- Loeschen eines zugeordneten Anhangs setzt beim naechsten Bestellsnapshot `terms_hash` und `terms_filename` auf `NULL`, ohne die GARAN-Ausgabe zu deaktivieren.
- Artikel mit je Variante abweichender Garantie ablehnen.
- Artikel mit GARAN-Daten duplizieren; das Duplikat erhaelt `products_garan_duration = NULL`, eine leere `products_manufacturers_model` und keine als `garan_terms` markierten Anhaenge. Andere zum Kopieren ausgewaehlte Artikel-Anhaenge werden weiterhin uebernommen.
- `p_garan_duration` leer, mit Komma, mit Punkt sowie mit gueltigen und ungueltigen Werten importieren. Bei ungueltigem resultierendem GARAN-Datensatz bleibt `products_garan_duration` unveraendert und der Fehler erscheint im `messageStack`.
- Einen Artikel mit gueltiger gespeicherter Garantiedauer mit einer CSV-Zeile importieren, deren GARAN-Daten unvollstaendig sind; die gespeicherte Dauer bleibt erhalten und wird nicht auf `NULL` gesetzt. Die uebrigen Felder der Zeile werden regulaer uebernommen.
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
- Mehrere sichtbare GARAN-Artikel verschiedener Hersteller pruefen; pro Ergebnisblock erfolgt hoechstens eine gesammelte Herstellerabfrage und keine Abfrage je Artikel.
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
- Direkten HTTP-Aufruf einer Datei unter `media/guarantee_labels/archive/` durch die eigene `.htaccess` blockieren.
- Zwei Bestellungen mit demselben `garan_hash` und `notice_hash` verwenden dieselben Archivverzeichnisse, ohne vorhandene Dateien zu ueberschreiben.
- Zwei parallele Schreibvorgaenge fuer denselben Hash erzeugen durch temporaere Nachbarverzeichnisse und atomare Umbenennung keine unvollstaendigen Archivverzeichnisse.
- Nicht beschreibbaren GARAN-Cache testen; die aktuelle Ausgabe verwendet die direkt erzeugten SVGs, der Fehler erscheint im Protokoll und in der Moduldiagnose.
- Unvollstaendigen oder beschaedigten GARAN-Cache testen; er wird nicht ausgegeben und die aktuelle Anfrage verwendet direkt erzeugte SVGs.
- Nicht beschreibbares oder unvollstaendig geschriebenes Archiv testen; die Bestellung laeuft weiter und keine Datenbankzeile verweist auf fehlende oder teilweise geschriebene Dateien.
- Erneuter Mailversand nach dem Leeren des Cache liefert dieselben vorhandenen Garantieerklaerungen und keine GARAN-Grafik. Waren keine Bedingungen archiviert, bleibt der Versand ohne GARAN-Anhang.

### Manuell angelegte Bestellungen

- Leere B2C-Bestellung bei aktivem Modul und vollstaendig gepflegter Bestellsprache anlegen; `orders_guarantee`, `notice.svg` und `notice.json` entstehen bereits vor dem Einfuegen einer Position.
- Manuelle Bestellung in einer deutschen Backend-Sitzung anlegen; unabhaengig von der bevorzugten Kontaktsprache des Kunden verwendet der Hinweis-Snapshot Deutsch und ein GARAN-Artikel den deutschen `garan_terms`-Anhang.
- Manuelle Bestellung in einer englischen Backend-Sitzung anlegen; Hinweis-Snapshot und `garan_terms`-Anhang verwenden Englisch.
- Sprache der Backend-Sitzung nach dem Anlegen wechseln; die in `orders.language` gespeicherte Bestellsprache bleibt massgeblich, auch fuer danach eingefuegte Positionen, und bestehende Snapshots bleiben unveraendert.
- Leere Bestellung bei inaktivem Modul, ausgeschlossener B2B-Kundengruppe oder unvollstaendiger Sprache anlegen; `orders_guarantee` bleibt leer.
- Nach dem Anlegen Modulstatus, Kundengruppe oder Sprachdaten aendern; der bestehende Hinweis-Snapshot wird nicht automatisch erzeugt, ersetzt oder entfernt.
- Frisch angelegte Bestellung ohne Positionen pruefen; `guarantee_labels_order_physical()` liefert `false` und der Gewaehrleistungshinweis wird nicht ausgegeben.
- Nur virtuelle Positionen einfuegen und entfernen; die Pruefung liefert durchgehend `false` und die Auftragsbestaetigung enthaelt keinen Hinweis.
- Physische sowie gemischte Positionen einfuegen und entfernen; die Pruefung liefert `true`, solange mindestens eine Position ohne Downloadzeile verbleibt, und nach dem Entfernen der letzten solchen Position wieder `false`.
- `orders.content_type` bleibt bei allen drei Faellen unveraendert; das Modul schreibt die Spalte nicht.
- Reine Download-Bestellung behaelt den Hinweis-Snapshot, gibt ihn in der Auftragsbestaetigung aber nicht aus.
- Physische oder gemischte Bestellung gibt den beim Anlegen gespeicherten Hinweis in der manuellen Auftragsbestaetigung aus.
- Leere Bestellung im Admin anlegen und einen Artikel ohne GARAN-Daten einfuegen.
- GARAN-Artikel im Admin einfuegen und alle sechs GARAN-Snapshotwerte der Bestellposition pruefen.
- Katalogdaten nach dem Einfuegen aendern und unveraenderten Bestellsnapshot pruefen.
- Artikel entfernen und erneut einfuegen; dabei die aktuellen Katalogdaten uebernehmen.
- GARAN-Snapshot im Admin korrigieren und Validierung pruefen.
- Optionale archivierte Garantieerklaerung in der Bestellposition hinzufuegen, ersetzen und entfernen; dabei `terms_hash` sowie `terms_filename` pruefen.
- Fehler beim Erzeugen oder Archivieren einer Snapshotaenderung ausloesen; die Aenderung wird abgelehnt, der bisherige Snapshot bleibt vollstaendig erhalten und der Admin erhaelt die Fehlermeldung ueber den `messageStack`.
- Herstellername, Hersteller-Modellkennung und Garantiedauer nur als vollstaendigen Datensatz speichern.
- Aenderung des Bestellsnapshots darf den Katalogartikel nicht veraendern.
- Aktion `Aus Artikeldaten uebernehmen` pruefen.
- Vorschau des GARAN-Labels vor dem Speichern pruefen.
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
- Jeder fehlende Sprachbestandteil erzeugt eine Warnung in der Moduldiagnose. Der Checkout laeuft ohne Gewaehrleistungshinweis und ohne sprachlichen Fallback weiter.
- Eine Bestellung in einer unvollstaendig gepflegten Sprache erzeugt keine Zeile in `orders_guarantee` und nimmt den Hinweis auch bei einem spaeteren Mailversand nicht nachtraeglich auf.
- Wird eine Sprache spaeter vervollstaendigt, verwenden neue Bestellungen sie sofort; bestehende Bestellsnapshots bleiben unveraendert.
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
- Auftragsbestaetigungen vorhandene archivierte Garantiebedingungen ueber den bestehenden Anhangsweg bereitstellen, bei fehlenden Bedingungen keinen GARAN-Anhang erzeugen und keine GARAN-Grafik versenden,
- die Standardsprachen korrekt unterstuetzt werden und
- die Ausgabe auf Desktop und Mobilgeraet funktioniert.

## Geklaerte technische Punkte

- Der bestehende Mailweg unterstuetzt regulaere Anhaenge. Die Garantiebedingungen werden ueber `$email_attachments` in `includes/extra/send_order/data/` ergaenzt. HTML- und Text-Mail geben den sprachabhaengigen Gewaehrleistungstext mit direktem Link aus. `notice.svg`, daraus erzeugte Rastergrafiken und GARAN-Grafiken werden weder eingebettet noch angehaengt. Es gibt keine neue Mailroutine und keine CID-Einbettung.
- Garantiebedingungen verwenden die vorhandenen Artikel-Anhaenge. `products_content.content_type = 'garan_terms'` kennzeichnet je Artikel und Sprache die optional zu verwendende Datei; ein separater Upload oder Content-Manager-Datensatz ist nicht erforderlich.
- Die technische Zuordnung eines Garantie-Anhangs bleibt optional. Das Modul warnt bei fehlender Zuordnung, erzwingt § 479 BGB aber nicht, weil der Shopbetreiber die Garantieerklaerung auch ueber einen anderen dauerhaften Datentraeger bereitstellen kann und fuer diesen Bereitstellungsweg verantwortlich bleibt.
- Fuer `garan_terms` gelten die bereits vorhandenen erlaubten Dateiformate der Artikel-Anhangsverwaltung. Der Bestellsnapshot speichert mit `terms_filename` den bereinigten Dateinamen samt Erweiterung; eine PDF-Erzeugung oder zusaetzliche Formateinschraenkung gibt es nicht. Kommas im Dateinamen sind wegen der kommaseparierten Anhangsliste unzulaessig.
- Vorhandene Garantiebedingungen werden unter `DIR_FS_DOCUMENT_ROOT . 'media/products/garan_archive/<terms_hash>/<terms_filename>'` atomar auf Dateiebene archiviert. Dadurch koennen Dateien mit identischem Inhalt und unterschiedlichen Namen dasselbe Hashverzeichnis verwenden. Der Mailweg verwendet den Pfad und damit den tatsaechlichen Namen der Archivdatei; er vergibt keinen abweichenden Anhangsnamen.
- `products.products_garan_duration` und `products_content.content_type` werden ueber Installationsschema, Datenbankupdate und idempotent in `install()` angelegt. Die Modulinstallation prueft Modultabellen, Indizes und beide Core-Spalten, legt nur fehlende Bestandteile an und traegt den aktiven Status erst nach erfolgreicher Pruefung des gesamten Modulschemas ein. `add_db_fields` registriert nur `products_garan_duration` fuer den Speicherweg der Artikelverwaltung und ersetzt keine Schemaaenderung.
- Die Modulklasse verwendet `$this->code = 'guarantee_labels'` und anfangs `$this->version = '1.00'`; funktionale und schemarelevante Modulupdates erhoehen die Version. Das Systemmodul implementiert `update()` und verwendet dort dieselbe zentrale, idempotente Schemaroutine wie in `install()`. Der Konstruktor stellt dafuer ueber `$this->properties['button_update']` die vorhandene Admin-Aktion `Modul aktualisieren` in `admin/module_export.php?set=system` bereit. Bereits installierte Shops erhalten so spaetere Tabellen-, Spalten- und Indexaenderungen; eine Deinstallation ist nicht erforderlich.
- Beim Duplizieren eines GARAN-Artikels wird `products_garan_duration` auf `NULL` gesetzt, `products_manufacturers_model` geleert und `garan_terms` nicht mitkopiert. Bei Artikeln ohne GARAN-Daten bleibt das allgemeine Kopierverhalten unveraendert. Beim Anlegen einer Sprache werden Markierungen vom Typ `garan_terms` ebenfalls nicht aus einer anderen Sprache uebernommen.
- Der Produkt-CSV-Import und -Export verwendet ausschliesslich `p_garan_duration` fuer die Garantiedauer. `content_type` und Garantie-Anhaenge bleiben ausserhalb des CSV-Formats.
- `includes/modules/products_media.php` stellt einen sichtbaren `garan_terms`-Anhang ueber den bestehenden Medienbereich bereit; ein neuer Storefront-Downloadweg ist nicht erforderlich.
- Die Labeldaten werden nach dem Sammelprinzip bereitgestellt. Die vorhandenen `ADD_SELECT_*`-Arrays liefern Garantiedauer, Hersteller-Modellkennung und Hersteller-ID. Eine zentrale GARAN-Hilfsfunktion laedt die Namen der betroffenen aktiven Hersteller mit hoechstens einer Abfrage je Ergebnisblock nach. Ausschliesslich diese Namen werden fuer GARAN verwendet; bereits von anderen Abfragen gelieferte Herstellernamen bleiben unberuecksichtigt. Es werden weder Hersteller-JOINs in alle Kernabfragen noch Einzelabfragen je Artikel eingefuehrt.
- Das Modul leert keinen Cache. Der eigene Grafikcache liegt unter dem Inhaltshash und kann verwaisen, aber nicht falsch werden. Nur die Smarty-Blockcaches koennen ein veraltetes Label bis zum Ablauf von `CACHE_LIFETIME` weiter ausliefern; dafuer leert der Shopbetreiber den Cache ueber `admin/configuration.php?action=delcache`. Die Moduldiagnose weist darauf hin. Die historischen Archive werden dabei nicht geloescht.
- `clear_dir(DIR_FS_CATALOG.'cache/')` entfernt `cache/guarantee_labels/` einschliesslich aller darin liegenden Schutzdateien und danach das Verzeichnis selbst. Der Renderer legt das Basisverzeichnis vor einem Schreibvorgang bei Bedarf rekursiv neu an. Dauerhafte `.htaccess`- oder `index.html`-Dateien sind in diesem Cache-Unterverzeichnis nicht vorgesehen.
- Bei manuellen Bestellungen stammen `orders.language` und `orders.languages_id` aus der Backend-Sitzung beim Anlegen. Das Modul verwendet `orders.language` als massgebliche Bestellsprache fuer den Hinweis-Snapshot und die Auswahl von `garan_terms`; es ermittelt keine abweichende Kundensprache.
- Der bestehende Admin-Ablauf befuellt `orders.content_type` beim Anlegen einer leeren Bestellung nicht. Das Modul liest die Spalte, wenn sie gefuellt ist, und schreibt sie nie. Bei leerer Spalte entscheiden die Positionen der Bestellung. Dadurch braucht es keinen zusaetzlich gepflegten Zustand, der nach einer Positionsaenderung falsch stehen bleiben koennte.
- Storefront und Checkout erzeugen `colour.svg` und `nested.svg` gemeinsam unter `cache/guarantee_labels/<garan_hash>/`. Beim Bestellabschluss werden beide Dateien einmalig unter `media/guarantee_labels/archive/garan/<garan_hash>/` archiviert. Das Archiv ist per eigener `.htaccess` nicht direkt ueber HTTP erreichbar.
- Der historische Gewaehrleistungshinweis liegt unter `media/guarantee_labels/archive/notice/<notice_hash>/`. `notice.svg` bewahrt die angezeigte Grafik; `notice.json` bewahrt Sprache, Mailtext, Linktext, Your-Europe-URL und Version. Ein erneuter Mailversand liest Text und Link aus diesem Snapshot statt aus aktuellen Sprachkonstanten. Fuer den aktuellen Hinweis im Storefront ist kein zusaetzlicher Cache erforderlich.
- Die zulaessige Laenge von Herstellername und Modellkennung wird nicht ueber eine feste Zeichenzahl entschieden. Der Renderer misst die tatsaechliche Textbreite serverseitig mit `imagettfbbox()`, der jeweiligen Inter-TTF-Datei, der vorgegebenen Schriftgroesse und einer festen Sicherheitstoleranz. Nicht passende Werte werden abgelehnt. GD mit FreeType ist Voraussetzung; ImageMagick und eine neue PHP-Bibliothek werden nicht benoetigt.
- Storefront und Checkout fuegen die gecachten SVGs inline ein. Eine zentrale Modul-CSS-Datei bindet die passenden Inter-WOFF2-Dateien per `@font-face` ein. Die Fonts werden nicht in jedes SVG kopiert; `images/.htaccess` erlaubt nur den HTTP-Zugriff auf WOFF2 und nicht auf TTF.
- B2B-Kundengruppen werden unter `MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS` kommasepariert gespeichert. Die Modulkonfiguration verwendet dafuer `xtc_cfg_multi_checkbox('xtc_get_customers_statuses', 'chr(44)', ...)`; eine neue Auswahlfunktion ist nicht erforderlich. Standardmaessig ist keine Kundengruppe ausgeschlossen. Gastzugriffe bleiben B2C, solange ihre Kundengruppe nicht ausdruecklich ausgewaehlt wird.
- Die Moduldiagnose ist keine Konfigurationsoption und wird nur erzeugt, wenn `isset($_GET['module']) && $_GET['module'] == $this->code && $this->check() > 0` gilt. Dann wird sie ueber `$this->properties['add_content']` direkt unter der Modul-Infobox in `admin/module_export.php?set=system` ausgegeben. Sie besitzt keine eigene Adminseite und prueft das Schema, bevor sie weitere Diagnoseabfragen ausfuehrt.
- Die benoetigten Storefront-Process-Hooks `checkout_process_products_end/` und `checkout_process_order/` existieren bereits.
- Manuell eingefuegte Bestellpositionen werden direkt in `orders_product_insert()` in `admin/includes/functions/orders_functions.php` verarbeitet. Nach dem Insert wird die `orders_products_id` mit `xtc_db_insert_id()` ermittelt und der GARAN-Snapshot ohne neue Erweiterungsstelle gespeichert.
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
- Garantie-Anhaenge sind optional. Fehlende Anhaenge blockieren weder die Pflege der GARAN-Kerndaten noch das sprachneutrale GARAN-Label. Pro Bestellung wird nur ein in der Bestellsprache tatsaechlich vorhandener Anhang archiviert und versendet; andernfalls bleiben `terms_hash` und `terms_filename` `NULL`.
- Sprachabhaengige Gewaehrleistungsgrafiken liegen direkt unter `lang/<Sprachverzeichnis>/notice.svg`. Mailtext, Linktext und Your-Europe-URL liegen in `lang/<Sprachverzeichnis>/extra/guarantee_labels.php`; Adminroutinen laden diese Datei fuer die Bestellsprache ausdruecklich. Titel, Beschreibung und Beschriftungen der Moduloptionen liegen in `lang/<Sprachverzeichnis>/modules/system/guarantee_labels.php`, weitere Admintexte unter `lang/<Sprachverzeichnis>/extra/admin/guarantee_labels.php`. Der Content Manager wird dafuer nicht verwendet. Die sprachneutralen GARAN-Vorlagen liegen unter `images/guarantee_labels/assets/`, die Inter-Schriften unter `images/guarantee_labels/fonts/` und erzeugte SVGs unter `cache/guarantee_labels/`. Dadurch kann ein nachtraeglich installiertes Sprachpaket die Funktion ohne Aenderung der Artikel vollstaendig bereitstellen.
- Fehlt in einer Sprache `notice.svg`, Mailtext, Linktext oder Your-Europe-URL, laeuft der Checkout ohne Gewaehrleistungshinweis weiter. Es gibt keinen Fallback und keine Checkout-Sperre. Die Moduldiagnose zeigt eine Warnung und `orders_guarantee` bleibt fuer diese Bestellung leer. Das sprachneutrale GARAN-Label wird weiterhin angezeigt.
- GARAN-Grafiken und Hinweis-Snapshots werden vollstaendig in temporaeren Nachbarverzeichnissen geschrieben, geprueft und erst danach atomar auf ihren Hashpfad umbenannt. Garantie-Anhaenge werden entsprechend atomar auf Dateiebene geschrieben. Fehler blockieren Bestellung und Mailversand nicht und erzeugen keine Dateiverweise auf fehlende Dateien. Ereignisse stehen in `mod_guarantee_labels_<level>_<datum>.log`; die Moduldiagnose zeigt aktuell feststellbare Fehler.
- Alle Fehler aus Admin-Aktionen werden ueber den bestehenden `messageStack` ausgegeben. Es gibt keinen eigenen Admin-Fehlermechanismus. Beim Aendern eines vorhandenen Bestellsnapshots bleibt der bisherige vollstaendige Stand erhalten; Teilaktualisierungen finden nicht statt.
- Admin-Aktionen mit Redirect verwenden fuer eigene Meldungen `messageStack->add_session()`, Aktionen ohne Redirect `messageStack->add()`. Insbesondere meldet `install()` Erfolg und Fehler selbst per Session. `update()` ueberlaesst die Erfolgsmeldung seinem Framework-Aufrufer; bei Fehlern setzt es eine Session-Meldung und gibt `false` zurueck.

## Offene Entscheidungen vor der Umsetzung

Keine.
