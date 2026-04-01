# Remove Image background

## Install

### rembg (optional)
Install the [rembg](https://github.com/danielgatis/rembg) Python CLI tool:
```bash
pip install rembg[cli]
```

Then install PHP dependencies:
```bash
composer install
```

### PHP Transformers (default)
Install PHP dependencies and download the model:
```bash
composer install

./vendor/bin/transformers download "briaai/RMBG-1.4"
```

Enable the `ffi` extension in your `php.ini` file:
```
ffi.enable = true
```

## Usage
You can remove the background of an image on the fly via url.
For example, your image is `https://example.com/image.jpg` you can remove the background by appending this app url to the image url:
```text
https://remove-bg.test/example.com/image.jpg
```

### Switching driver at runtime
By default, PHP Transformers is used. You can switch to `rembg` via the `driver` query string:
```text
https://remove-bg.test/example.com/image.jpg?driver=rembg
```

Enjoy!