#!/usr/bin/env bash

set -x

AUTOVERSION=$(git describe --tags)
VERSION=${1:-$AUTOVERSION}
echo "pack version: $VERSION"

mkdir -p dist

echo "build version: $VERSION"

composer i
yarn install
yarn build

find /var/www/resources/lang -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;

composer archive --format=tar --file dist/prepack
echo "creating release archive"
mkdir -p dist/engelsystem-$VERSION
cd dist/engelsystem-$VERSION
tar xf ../prepack.tar
cd ..
tar cjf engelsystem-${VERSION}.tar.bz2 engelsystem-${VERSION}
rm -rf prepack.tar engelsystem-${VERSION}
