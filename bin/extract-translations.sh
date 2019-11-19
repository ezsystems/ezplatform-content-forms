#!/usr/bin/env sh
echo 'Translation extraction';
cd ../../..;
# Extract string for default locale
echo '# Extract ContentForms';
./app/console translation:extract en -v \
  --dir=./vendor/ezsystems/ezplatform-content-forms/src/bundle \
  --dir=./vendor/ezsystems/ezplatform-content-forms/src/lib \
  --exclude-dir=vendor \
  --output-dir=./vendor/ezsystems/ezplatform-content-forms/src/bundle/Resources/translations \
  --keep
  "$@"

echo '# Clean file references';
sed -i "s|>.*/vendor/ezsystems/ezplatform-content-forms/|>|g" ./vendor/ezsystems/ezplatform-content-forms/src/bundle/Resources/translations/*.xlf

cd vendor/ezsystems/ezplatform-content-forms;
echo 'Translation extraction done';
