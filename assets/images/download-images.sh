#!/bin/sh
set -eu
cd "$(dirname "$0")"
while IFS= read -r url; do
  [ -z "$url" ] && continue
  file="$(basename "${url%%\?*}")"
  echo "Downloading $file..."
  curl -L --fail --retry 3 "$url" -o "$file"
done < IMAGE_SOURCES.txt
echo "Done."
