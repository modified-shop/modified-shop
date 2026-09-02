<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/**
 * Builds the working directories the guarantee label tests run in and returns their paths.
 *
 * The tests never touch the shop they belong to. They run against a small tree of symlinks into
 * the repository plus a few writable directories, so a test may create a cache entry or archive
 * a file without leaving anything behind in the working copy.
 *
 * The label templates are stubs, not the official files: they carry the same tokens and are a few
 * hundred bytes, which keeps the render tests fast and independent of the licensed originals.
 *
 * @return array repo, shop and ext paths
 */

// The tests live on their own branch, so they may sit inside a working copy of the shop or in a
// worktree of their own next to it. GARAN_SHOP_ROOT names the shop in the second case.
$guarantee_labels_repo = trim((string)getenv('GARAN_SHOP_ROOT'));

if ($guarantee_labels_repo === '') {
  $guarantee_labels_repo = dirname(dirname(__DIR__));
}

$guarantee_labels_repo = rtrim($guarantee_labels_repo, '/');

if (!is_file($guarantee_labels_repo.'/inc/html_encoding.php')) {
  fwrite(STDERR, "no shop at ".$guarantee_labels_repo.", set GARAN_SHOP_ROOT\n");
  exit(2);
}

// the file that tells a working directory of these tests from any other directory
defined('GUARANTEE_LABELS_TEST_MARKER') or define('GUARANTEE_LABELS_TEST_MARKER', '.garan-tests');

// The name is fixed and carries the repository in its hash, so two working copies do not share
// one directory. Only the base may be chosen, never the directory itself: the runner deletes
// this path, and a caller must not be able to point that at a directory of their own.
$guarantee_labels_name = 'garan-tests-'.substr(md5($guarantee_labels_repo), 0, 12);
// an empty value counts as unset, the same way the shell reads it
$guarantee_labels_base = trim((string)getenv('GARAN_TEST_BASE'));

if ($guarantee_labels_base === '') {
  $guarantee_labels_base = sys_get_temp_dir();
}

$guarantee_labels_base = rtrim($guarantee_labels_base, '/');

if ($guarantee_labels_base === '' || !is_dir($guarantee_labels_base) || !is_writable($guarantee_labels_base)) {
  fwrite(STDERR, "GARAN_TEST_BASE is not a writable directory\n");
  exit(2);
}

// a base that holds the repository, the home directory or the root would put the deletion right
// next to files nobody meant to lose
$guarantee_labels_forbidden = array('/', $guarantee_labels_repo, rtrim((string)getenv('HOME'), '/'));

foreach ($guarantee_labels_forbidden as $guarantee_labels_path) {
  if ($guarantee_labels_path !== '' && realpath($guarantee_labels_base) === realpath($guarantee_labels_path)) {
    fwrite(STDERR, "GARAN_TEST_BASE must not be the root, the home directory or the repository\n");
    exit(2);
  }
}

// One directory per run, never a shared one: two runs at the same time would build and delete
// the same tree and fail each other. The runner creates it and hands it down, a test started on
// its own creates one and passes it to the processes it spawns.
$guarantee_labels_work = getenv('GARAN_TEST_WORK_DIR');
$guarantee_labels_owned = false;

if ($guarantee_labels_work === false || $guarantee_labels_work === '') {
  $guarantee_labels_work = $guarantee_labels_base.'/'.$guarantee_labels_name.'-'.getmypid().'-'.bin2hex(random_bytes(4));

  if (!@mkdir($guarantee_labels_work, 0700)) {
    fwrite(STDERR, "cannot create the working directory: ".$guarantee_labels_work."\n");
    exit(2);
  }

  if (@file_put_contents($guarantee_labels_work.'/'.GUARANTEE_LABELS_TEST_MARKER, 'guarantee label tests') === false) {
    @rmdir($guarantee_labels_work);
    fwrite(STDERR, "cannot mark the working directory: ".$guarantee_labels_work."\n");
    exit(2);
  }

  $guarantee_labels_owned = true;
  putenv('GARAN_TEST_WORK_DIR='.$guarantee_labels_work);
} else {
  // A handed down path is only followed when it really is one of ours. Tests write into it and
  // remove subdirectories of it, so an arbitrary directory must never end up here, whatever the
  // environment says.
  $guarantee_labels_given = rtrim($guarantee_labels_work, '/');
  $guarantee_labels_real = is_link($guarantee_labels_given) ? false : @realpath($guarantee_labels_given);
  $guarantee_labels_home = @realpath($guarantee_labels_base);

  if ($guarantee_labels_real === false
      || $guarantee_labels_home === false
      || !is_dir($guarantee_labels_real)
      || dirname($guarantee_labels_real) !== $guarantee_labels_home
      || strpos(basename($guarantee_labels_real), 'garan-tests-') !== 0
      || !is_file($guarantee_labels_real.'/'.GUARANTEE_LABELS_TEST_MARKER)
      )
  {
    fwrite(STDERR, "GARAN_TEST_WORK_DIR is not a working directory of these tests: ".$guarantee_labels_given."\n");
    exit(2);
  }

  $guarantee_labels_work = $guarantee_labels_real;
}

// whoever created it clears it away again, the processes it spawns leave it alone
if ($guarantee_labels_owned === true) {
  register_shutdown_function(function () use ($guarantee_labels_work) {
    if (strpos(basename($guarantee_labels_work), 'garan-tests-') !== 0 || is_link($guarantee_labels_work)) {
      return;
    }

    $items = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($guarantee_labels_work, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
      // a symlink into the repository is unlinked, never walked into
      if ($item->isLink() || !$item->isDir()) {
        @unlink($item->getPathname());
      } else {
        @rmdir($item->getPathname());
      }
    }

    @rmdir($guarantee_labels_work);
  });
}

$guarantee_labels_shop = $guarantee_labels_work.'/shop';
$guarantee_labels_ext = $guarantee_labels_work.'/ext';


/**
 * Creates a symlink unless it is already the one we want.
 */
function guarantee_labels_test_link($target, $link) {
  if (is_link($link)) {
    if (readlink($link) === $target) {
      return;
    }

    unlink($link);
  }

  if (!is_dir(dirname($link))) {
    mkdir(dirname($link), 0777, true);
  }

  symlink($target, $link);
}

// the storefront and administration tree: everything readable comes from the repository
foreach (array('inc', 'includes', 'lang') as $guarantee_labels_part) {
  guarantee_labels_test_link($guarantee_labels_repo.'/'.$guarantee_labels_part, $guarantee_labels_shop.'/'.$guarantee_labels_part);
}

guarantee_labels_test_link($guarantee_labels_repo.'/images/guarantee_labels/fonts',
                           $guarantee_labels_shop.'/images/guarantee_labels/fonts');

// the stub templates, copied so a test may replace one to provoke a failure
if (!is_dir($guarantee_labels_shop.'/images/guarantee_labels/assets')) {
  mkdir($guarantee_labels_shop.'/images/guarantee_labels/assets', 0777, true);
}

foreach (glob(__DIR__.'/fixtures/assets/*.svg') as $guarantee_labels_asset) {
  $guarantee_labels_target = $guarantee_labels_shop.'/images/guarantee_labels/assets/'.basename($guarantee_labels_asset);

  if (!is_file($guarantee_labels_target)) {
    copy($guarantee_labels_asset, $guarantee_labels_target);
  }
}

foreach (array('cache/guarantee_labels', 'log', 'media/products', 'media/guarantee_labels/archive') as $guarantee_labels_part) {
  if (!is_dir($guarantee_labels_shop.'/'.$guarantee_labels_part)) {
    mkdir($guarantee_labels_shop.'/'.$guarantee_labels_part, 0777, true);
  }
}

// The module installation tests need the administration next to the catalogue and the official
// templates, because they check that the module refuses to switch on without them.
foreach (array('inc', 'images') as $guarantee_labels_part) {
  guarantee_labels_test_link($guarantee_labels_repo.'/'.$guarantee_labels_part, $guarantee_labels_ext.'/'.$guarantee_labels_part);
}

foreach (array('classes', 'extra', 'functions', 'external') as $guarantee_labels_part) {
  guarantee_labels_test_link($guarantee_labels_repo.'/includes/'.$guarantee_labels_part,
                             $guarantee_labels_ext.'/includes/'.$guarantee_labels_part);
}

// Only the class extensions of this module, never a whole modules directory: registering one
// makes the framework read the directory, and every other module there expects its own language
// file, which this fixture does not have.
$guarantee_labels_extensions = array(
  'admin/includes/modules/categories/guarantee_labels_product.php',
  'admin/includes/modules/system/guarantee_labels.php',
  'includes/modules/product/guarantee_labels_listing.php',
  'includes/modules/order/guarantee_labels_order.php',
);

foreach ($guarantee_labels_extensions as $guarantee_labels_part) {
  guarantee_labels_test_link($guarantee_labels_repo.'/'.$guarantee_labels_part,
                             $guarantee_labels_ext.'/'.$guarantee_labels_part);
}

// a second extension that does not belong to this module, so a test can show that installing or
// removing the module leaves the extensions of a shop alone
guarantee_labels_test_link(__DIR__.'/fixtures/other_extension.php',
                           $guarantee_labels_ext.'/admin/includes/modules/categories/other_extension.php');

return array(
  'repo' => $guarantee_labels_repo,
  'shop' => $guarantee_labels_shop,
  'ext' => $guarantee_labels_ext,
  'work' => $guarantee_labels_work,
);
