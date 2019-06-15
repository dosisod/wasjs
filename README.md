# wasjs
Wait a Second JS: Proof-of-work based timeout for web downloads

![wasjs in action](img/using.gif)

## Setup

Using the `index.html` as an example, include `was.js` and `sha512.js` into your site

Then, add the following HTML to your site as well:

```html
<div class="wasjs"><img src="wasjs.png" onload="new was(this, 'file_from_path')"></div>
```

Here, `'file_from_path'` will be sanatized and appended to the docroot of the PHP path

To change the path, replace `$path` inside of `was.php` with the directory of your files you want to host

The path can be inside or outside of the docroot, though it is recommended to be outside (where the PHP files cannot be reached)

## License

Make sure to include the license for `wasjs` from the `LICENSE` file, and the license included in `sha512.js` for its author