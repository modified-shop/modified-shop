#!/bin/sh
#  -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2026 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------
#  Released under the GNU General Public License
#  -----------------------------------------------------------------------------------------
#
#  Runs the tests of the EU guarantee labelling module.
#
#    tests/guarantee_labels/run.sh              all tests
#    tests/guarantee_labels/run.sh mail render  only the named ones
#
#  Needs php on the path with gd, mbstring and the freetype support of imagettfbbox().
#  Nothing is written outside the temporary working directory this script creates, and that one
#  is removed again when the run ends. Parallel runs do not share it.

DIR=$(cd "$(dirname "$0")" && pwd)
PHP=${PHP:-php}

if ! command -v "$PHP" > /dev/null 2>&1; then
  echo "php not found, set PHP=/path/to/php" >&2
  exit 2
fi

# A working directory of its own, created here and never reused. Two runs at the same time would
# otherwise build and delete the same tree and fail each other, and a leftover from an earlier run
# would decide what a test sees.
BASE=${GARAN_TEST_BASE:-${TMPDIR:-/tmp}}
BASE=${BASE%/}

# handed down so a test started from here reads the same base this script checked
GARAN_TEST_BASE="$BASE"
export GARAN_TEST_BASE

if [ ! -d "$BASE" ] || [ ! -w "$BASE" ]; then
  echo "GARAN_TEST_BASE is not a writable directory" >&2
  exit 2
fi

# the same bases the bootstrap refuses, so a wrong one is one message and not 25 failing tests
for forbidden in / "$HOME" "$(cd "$DIR/../.." && pwd)"; do
  if [ -n "$forbidden" ] && [ "$(cd "$BASE" && pwd -P)" = "$(cd "$forbidden" && pwd -P)" ]; then
    echo "GARAN_TEST_BASE must not be the root, the home directory or the repository" >&2
    exit 2
  fi
done

WORK=$(mktemp -d "$BASE/garan-tests-XXXXXXXX") || exit 2

# the marker the bootstrap looks for before it follows a handed down path
: > "$WORK/.garan-tests" || exit 2

# only this directory is ever removed, and only while it still is the one that was created here
cleanup() {
  case "$(basename "$WORK")" in
    garan-tests-*)
      if [ -d "$WORK" ] && [ ! -L "$WORK" ]; then
        rm -rf "$WORK"
      fi
      ;;
  esac
}

# EXIT does the cleaning, the signal handlers exist so a signal really ends the run: without
# them the shell finished the loop and reported success after a Ctrl-C
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

GARAN_TEST_WORK_DIR="$WORK"
export GARAN_TEST_WORK_DIR

if [ $# -gt 0 ]; then
  FILES=""
  for name in "$@"; do
    match=$(ls "$DIR"/*"$name"*_test.php 2>/dev/null)
    if [ -z "$match" ]; then
      echo "no test matches '$name'" >&2
      exit 2
    fi
    FILES="$FILES $match"
  done
else
  FILES=$(ls "$DIR"/*_test.php)
fi

total_ok=0
total_failed=0
failed_files=""

for file in $FILES; do
  name=$(basename "$file")
  output=$("$PHP" "$file" 2>&1)
  status=$?
  summary=$(printf '%s' "$output" | grep -E '^bestanden: [0-9]+' | tail -1)
  ok=$(printf '%s' "$summary" | sed -n 's/^bestanden: *\([0-9]*\).*/\1/p')
  failed=$(printf '%s' "$summary" | sed -n 's/.*fehlgeschlagen: *\([0-9]*\).*/\1/p')

  [ -z "$ok" ] && ok=0
  [ -z "$failed" ] && failed=0

  # A notice or a warning is a defect as well: it means the code read something that was not
  # there. The exit code is checked too, so a test that dies before its summary cannot pass.
  noise=$(printf '%s\n' "$output" | grep -cE 'PHP (Warning|Notice|Deprecated|Fatal error|Parse error)')

  if [ "$status" -ne 0 ] || [ "$failed" -gt 0 ] || [ "$noise" -gt 0 ] || [ -z "$summary" ]; then
    echo "FAIL  $name"

    if [ -z "$summary" ]; then
      # without a summary there is nothing to pick out, so the whole output is shown: it holds
      # the reason the test never got that far
      echo "      no summary, the test did not run to its end"
      printf '%s\n' "$output" | head -20 | sed 's/^/      /'
    else
      printf '%s\n' "$output" | grep -E '  FAIL  |PHP (Warning|Notice|Deprecated|Fatal error|Parse error)' | head -20 | sed 's/^/      /'
    fi
    failed_files="$failed_files $name"
    total_failed=$((total_failed + failed))

    if [ "$failed" -eq 0 ]; then
      total_failed=$((total_failed + 1))
    fi
  else
    echo "ok    $name  ($ok)"
  fi

  total_ok=$((total_ok + ok))
done

echo "----------------------------------------"

if [ -n "$failed_files" ]; then
  echo "failed:$failed_files"
  echo "$total_ok passed, $total_failed failed"
  exit 1
fi

echo "$total_ok passed, nothing failed"
exit 0
