#!/bin/bash
mkdir -p temp
cp -r upload/* temp/
cd temp || exit 1
zip -0 -r -o tagsadd.zip *
cd ..
cp -f temp/tagsadd.zip tagsadd_install.zip
rm -rf temp
exit 0
