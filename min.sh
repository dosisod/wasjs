# install minify with "npm install minify -g"
echo "Minifying was.js"
minify src/was.js > /tmp/was.min.js
cat /tmp/was.min.js src/sha512.js > build/was.min.js
rm /tmp/was.min.js

echo "Minifying styles.css"
minify src/styles.css > build/was.min.css