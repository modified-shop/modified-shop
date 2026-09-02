# Tests der modified eCommerce Shopsoftware

Dieser Branch enthält ausschließlich Tests. Er ist bewusst kein Teil von `main` und keines
Feature-Branches: Die Dateien sollen nicht mit einem Shop ausgeliefert werden, im Wurzelverzeichnis
eines installierten Shops wären sie sonst über HTTP erreichbar.

## Verwenden

Die Tests laufen gegen eine Arbeitskopie des Shops und erwarten, dass sie darin unter `tests/`
liegen. In einer Arbeitskopie holst du sie so hinein:

```sh
git archive test/guarantee-labels | tar -x
```

`tests/` steht in der `.gitignore` des Shops, die Dateien werden also nicht versehentlich in einen
Feature-Branch committet.

Willst du sie stattdessen an einem anderen Ort auschecken, sag dem Bootstrap, wo der Shop liegt:

```sh
git worktree add ../shop-tests test/guarantee-labels
GARAN_SHOP_ROOT=/pfad/zum/shop ../shop-tests/tests/guarantee_labels/run.sh
```

## Änderungen zurückschreiben

```sh
git add -f tests/
git stash push -- tests/
git checkout test/guarantee-labels
git stash pop
git commit -m "..."
```

## Inhalt

| Verzeichnis | Modul |
| --- | --- |
| `guarantee_labels/` | EU-Haltbarkeitsgarantie und GARAN-Label, Issue #3050 |
