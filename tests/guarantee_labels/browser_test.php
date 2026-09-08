<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Das Verhalten von guarantee_labels.js an einer kleinen Attrappe des Dom. Zwei Fehler, die
// hier auffallen wuerden, sind der Ausgabe entgangen: eine fehlende Id fuer die Lightbox und
// ein zweiter Klick, der eine leere Box oeffnet, waehrend die Grafik noch laedt.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$repo = $guarantee_labels_paths['repo'];
$work = $guarantee_labels_paths['work'];

$node = trim((string)shell_exec('command -v node 2>/dev/null'));

if ($node === '') {
  echo "  --    kein node, das Browserverhalten wird nicht ausgefuehrt\n";
  echo "\n----------------------------------------\n";
  echo "bestanden: 0   fehlgeschlagen: 0\n";
  exit(0);
}

$script = <<<'JS'
'use strict';
const fs = require('fs');
const source = fs.readFileSync(process.argv[2], 'utf8');

let pass = 0, fail = 0;
function ok(name, cond, extra) {
  if (cond) { pass++; console.log('  ok    ' + name); }
  else { fail++; console.log('  FAIL  ' + name + (extra ? '  (' + extra + ')' : '')); }
}

// die kleinste Menge Dom, die guarantee_labels.js anfasst
function el(classes, attrs) {
  const node = {
    classes: classes.split('.').filter(Boolean),
    attrs: Object.assign({}, attrs || {}),
    children: [], parent: null, style: {}, innerHTML: '', textContent: '', className: '',
    getAttribute(n) { return Object.prototype.hasOwnProperty.call(this.attrs, n) ? this.attrs[n] : null; },
    setAttribute(n, v) { this.attrs[n] = String(v); },
    removeAttribute(n) { delete this.attrs[n]; },
    appendChild(child) { child.parent = this; this.children.push(child); return child; },
    removeChild(child) { const i = this.children.indexOf(child); if (i > -1) this.children.splice(i, 1); },
    matches(sel) {
      if (sel.charAt(0) === '#') { return this.id === sel.slice(1); }
      return this.classes.indexOf(sel.slice(1)) !== -1;
    },
    closest(sel) { let n = this; while (n) { if (n.matches(sel)) { return n; } n = n.parent; } return null; },
    querySelector(sel) {
      for (const child of this.children) {
        if (child.matches(sel)) { return child; }
        const deep = child.querySelector(sel);
        if (deep) { return deep; }
      }
      return null;
    }
  };
  // im Browser sind Eigenschaft und Attribut dieselbe Id
  Object.defineProperty(node, 'id', {
    get() { return this.attrs.id || ''; },
    set(value) { this.attrs.id = String(value); }
  });

  return node;
}

// Eine Seite mit einem oder mehreren Labeln, so wie guarantee_labels_markup() sie schreibt.
// Mehrere Vorkommen desselben Artikels tragen dieselbe Id: product::buildDataArray() liefert
// jedem Platz dasselbe gecachte Markup.
function page(options) {
  const settings = options || {};
  const id = 'guarantee-label-content-1';
  const body = el('.body');
  const blocks = [];

  for (let i = 0; i < (settings.copies || 1); i++) {
    const label = body.appendChild(el('.guarantee-label'));
    const dialog = label.appendChild(el('.guarantee-label__dialog'));
    const content = dialog.appendChild(el('.guarantee-label__content', {id: id}));
    const full = content.appendChild(el('.guarantee-label__full', {'data-guarantee-label-src': 'cache/x/colour.svg'}));
    const graphic = full.appendChild(el('.guarantee-label__graphic'));
    const compact = label.appendChild(el('.guarantee-label__compact', {'data-guarantee-label-content': id}));

    dialog.showModal = function () { dialog.opened = (dialog.opened || 0) + 1; };
    dialog.close = function () { dialog.opened = 0; };

    blocks.push({label: label, dialog: dialog, content: content, full: full, graphic: graphic, compact: compact});
  }

  const first = blocks[0];
  const state = {id: id, blocks: blocks, label: first.label, dialog: first.dialog, content: first.content,
                 full: first.full, graphic: first.graphic, compact: first.compact,
                 opened: [], fetches: 0, waiting: []};

  // wie im Browser: das erste Element des Dokuments mit dieser Id gewinnt
  function byId(node, name) {
    for (const child of node.children) {
      if (child.id === name) { return child; }
      const deep = byId(child, name);
      if (deep) { return deep; }
    }
    return null;
  }

  const document = {
    body: body,
    addEventListener(type, handler) { if (type === 'click') { state.click = handler; } },
    createElement(tag) { const node = el(''); node.tag = tag; return node; },
    getElementById(name) { return byId(body, name); }
  };

  state.byId = function (name) { return byId(body, name); };

  const window = {
    innerWidth: 1000, innerHeight: 800,
    getComputedStyle() { return {getPropertyValue() { return 'sans-serif'; }}; }
  };

  if (settings.colorbox) {
    window.jQuery = function () {};
    window.jQuery.colorbox = function (config) { state.opened.push(config.href); };
  }

  if (settings.thickbox) {
    window.tb_show = function (title, href) { state.opened.push(href); };
  }

  function fetch() {
    state.fetches++;
    return new Promise(function (resolve) {
      state.waiting.push(function () {
        resolve({ok: true, text: function () { return Promise.resolve('<svg id="a"></svg>'); }});
      });
    });
  }

  new Function('document', 'window', 'fetch', source)(document, window, fetch);

  state.clickCompact = function (index) {
    state.click({target: blocks[index || 0].compact, preventDefault: function () {}});
  };
  state.settle = function () { const waiting = state.waiting.slice(); state.waiting = []; waiting.forEach(function (fn) { fn(); }); };

  return state;
}

// laesst die Promise-Kette durchlaufen
function tick() { return new Promise(function (resolve) { setTimeout(resolve, 0); }); }

(async function () {
  console.log('== Die Lightbox bekommt die Id des Inhalts ==');

  let box = page({colorbox: true});
  box.clickCompact();
  box.settle();
  await tick();
  ok('colorbox oeffnet die Id und nicht undefined', box.opened.length === 1 && box.opened[0] === '#' + box.id, box.opened.join(','));

  box = page({thickbox: true});
  box.clickCompact();
  box.settle();
  await tick();
  ok('thickbox bekommt inlineId', box.opened.length === 1 && box.opened[0].indexOf('inlineId=' + box.id + '&') !== -1, box.opened.join(','));

  box = page();
  box.clickCompact();
  box.settle();
  await tick();
  ok('ohne Lightbox oeffnet der Dialog', box.dialog.opened === 1);

  console.log('');
  console.log('== Ein zweiter Klick waehrend des Ladens ==');

  box = page({colorbox: true});
  box.clickCompact();
  box.clickCompact();
  ok('nichts oeffnet, solange die Grafik laeuft', box.opened.length === 0, box.opened.join(','));
  ok('nur eine Anfrage fuer beide Klicks', box.fetches === 1, String(box.fetches));
  box.settle();
  await tick();
  ok('nach dem Laden oeffnen beide', box.opened.length === 2, box.opened.join(','));
  ok('die Grafik steht im Dokument', box.graphic.innerHTML.indexOf('<svg') === 0, box.graphic.innerHTML);

  box.clickCompact();
  await tick();
  ok('ein spaeterer Klick oeffnet sofort', box.opened.length === 3 && box.fetches === 1, box.opened.length + '/' + box.fetches);

  console.log('');
  console.log('== Derselbe Artikel zweimal auf der Seite ==');

  // product::buildDataArray() liefert beiden Plaetzen dasselbe Markup samt Id. Die Lightbox
  // nimmt bei doppelter Id den ersten Treffer im Dokument: Ohne eigene Id laedt der geklickte
  // Block seine Grafik und der erste, noch leere geht auf.
  box = page({colorbox: true, copies: 2});
  ok('zwei Bloecke tragen dieselbe Id', box.blocks[0].content.id === box.blocks[1].content.id);
  box.clickCompact(1);
  box.settle();
  await tick();
  ok('geoeffnet wird der geklickte Block', box.opened.length === 1
     && box.byId(box.opened[0].slice(1)) === box.blocks[1].content, box.opened.join(','));
  ok('die Grafik steht im geklickten Block', box.blocks[1].graphic.innerHTML.indexOf('<svg') === 0
     && box.blocks[0].graphic.innerHTML === '');

  box = page({colorbox: true, copies: 2});
  box.clickCompact(0);
  box.settle();
  await tick();
  ok('der erste Block behaelt seine Id', box.opened.length === 1 && box.opened[0] === '#' + box.id, box.opened.join(','));

  console.log('');
  console.log('----------------------------------------');
  console.log('bestanden: ' + pass + '   fehlgeschlagen: ' + fail);
  process.exit(fail > 0 ? 1 : 0);
}());
JS;

$file = $work.'/browser_check.js';
file_put_contents($file, $script);

$output = array();
$status = 0;
exec(escapeshellcmd($node).' '.escapeshellarg($file).' '.escapeshellarg($repo.'/images/guarantee_labels/guarantee_labels.js').' 2>&1', $output, $status);
@unlink($file);

foreach ($output as $line) {
  echo $line."\n";
}

exit($status);
