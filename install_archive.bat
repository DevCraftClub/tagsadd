@echo off
if exist temp rmdir /s /q temp
mkdir temp
xcopy /e /i /y upload\* temp\
cd temp
zip -0 -r -o tagsadd.zip *
cd ..
copy /y temp\tagsadd.zip tagsadd_install.zip
rmdir /s /q temp
