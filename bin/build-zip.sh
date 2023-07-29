#!/bin/sh

PLUGIN_SLUG="wc-cart-pdf"
PROJECT_PATH="."
BUILD_PATH="./build"
DEST_PATH="$BUILD_PATH/$PLUGIN_SLUG"

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

echo "Installing PHP and JS dependencies..."
npm ci --no-optional
composer install --no-dev || exit "$?"
echo "Running JS Build..."
npm run build || exit "$?"
echo "Generating translations..."
npm run i18n || exit "$?"

echo "Syncing files..."
rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DEST_PATH/" --delete --delete-excluded

echo "Generating zip file..."
cd "$BUILD_PATH" || exit
zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"

cd "$PROJECT_PATH" || exit
echo "${PLUGIN_SLUG}.zip file generated!"

echo "Build done!"
