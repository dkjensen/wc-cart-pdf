#!/bin/sh

# We are going to find all instances of "../data/" in /src/dependencies and replace it with "../../data/"
find src/dependencies/Mpdf -type f -print0 | while IFS= read -r -d '' file; do
    # Replace all instances of "../data" with "../../data"
    sed -i 's/\.\.\/data/\.\.\/\.\.\/data/g' "$file"

    # Replace all instances of "../ttfonts" with "../../ttfonts"
    sed -i 's/\.\.\/ttfonts/\.\.\/\.\.\/ttfonts/g' "$file"
done