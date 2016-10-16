# UglifyJS.php

Using UglifyJS wrap in PHP

- You have to install command line uglifyjs ``npm install uglify-js -g``
- Versions follow the versions of the UglifyJS package


## Usage

```php
$compiler = new UglifyJS();

$compiler->add("js/my-app.js")
		->add("js/popup.js")
		->cacheDir("/tmp/js-cache/")
		->write();
```

## API

These are the main methods to execute:

### cacheDir( $path )

Setting the temp dir for the cached files.

### add( $script )

Add a script in the queue to be compressed.

### write( $output );

Parsing queue and compressing files. Optionally outputting the result if ```$output=true``` (default: false)


## Credits

Created by Makis Tracend ( [@tracend](http://github.com/tracend) )

### Trivia

* Originally created to be part of [KISSCMS](https://github.com/makesites/kisscms/issues/99)
* Based on [PHP Closure](http://code.google.com/p/php-closure/) by Daniel Pupius

### License

Released under the [Apache License v2.0](http://www.apache.org/licenses/LICENSE-2.0)
