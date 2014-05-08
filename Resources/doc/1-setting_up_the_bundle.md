Step 1: Setting up the bundle
=============================

### A) Install DevtrwParseBundle

Simply run assuming you have installed composer.phar or composer binary:

``` bash
$ php composer.phar require devtrw/parse-bundle dev-master
```

### B) Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Devtrw\ParseBundle\DevtrwParseBundle(),
    );
}
```

## That was it!

Check out the docs for information on how to use the bundle!
[Return to the index.](index.md)
