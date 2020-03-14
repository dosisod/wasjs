# install minify with "npm install minify -g"
echo "Minifying was.js"
minify was.js > /tmp/was.min.js
cat /tmp/was.min.js sha512.js > build/was.min.js
rm /tmp/was.min.js

echo "Minifying styles.css"
minify styles.css > build/was.min.css