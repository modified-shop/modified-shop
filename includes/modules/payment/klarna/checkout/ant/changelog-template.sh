#!/bin/sh

if [ -z "$1" ]; then
	echo "$0: need a version" >&2
	exit 1
fi
version="$1"

if [ -z "$2" ]; then
	echo "$0: need a build date" >&2
	exit 1
fi
bdate="$2"

if grep "^$version" CHANGELOG > /dev/null; then
	echo "$0: $version already in changelog" >&2
	exit 2
fi

desc=$(git describe --long 2>/dev/null)
if [ "$?" -eq "0" ] ; then
	since=$(git rev-parse $(echo "$desc" | cut -d - -f 1))
	range="$since..HEAD"
else
	echo "no tags found, including *all* commits" >&2
	range="HEAD"
fi
template="$(mktemp)"

printf "$version\n----------------\nDate: $bdate\nEDIT ME\n\n" > "$template"
git log "$range" --format="* %s%n    - %b%n" | sed '/^    - $/d' >> "$template"
sed -i CHANGELOG -e '3 r'"$template"

rm "$template"
