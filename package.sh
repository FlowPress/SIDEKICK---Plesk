cp -R htdocs dist/
cp -R _meta dist/
cp -R plib dist/
cp -R CHANGES.md dist/
cp -R DESCRIPTION.md dist/
cp -R README.md dist/
cp -R meta.xml dist/

cd dist
rm -fr sidekick_plesk.zip

zip -r -X "sidekick_plesk.zip" *
