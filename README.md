# CiviCRM Compilation Library

This is a quick-and-dirty library with some example tasks and helpers for [composer-compile-plugin](https://github.com/civicrm/composer-compile-plugin).

Some general values for this repo:

* Use basic functions and static methods to allow easy operation in pre-boot environments.
* Every task/helper must throw an exception if it doesn't work.
* If a task is outputting to a folder, and if the folder doesn't exist, then it should auto-create the folder.
* Feel free to copy/hack/etc. If you re-publish, please use a different name+namespace to allow coexistence.

## Require the library

All the examples below require the `civicrm/composer-compile-lib` package.

```javascript
  "require": {
    "civicrm/composer-compile-lib": "~1.0"
  }
```

## Task: SCSS

```javascript
{
  "extra": {
    "compile": [
      {
        "title": "Whizbang CSS (<comment>dist/whizbang.css</comment>)",
        "run": "@php-method \\CCL\\Task::scss",
        "watch-files": ["scss"],
        "scss-files": {"scss/whizbang.scss": "dist/whizbang.css"},
        "scss-imports": ["scss"]
        "scss-import-prefixes": {"LOGICAL_PREFIX/": "physical/folder"}
      }
    ]
  }
}
```

## Task: Meta-PHP

```javascript
{
  "extra": {
    "compile": [
      {
        "title": "Sandwich (<comment>src/Sandwich.php</comment>)",
        "run": "@php-method \\CCL\\Task::metaphp",
        "watch-files": ["src/tpl"],
        "metaphp-tpl": "src/tpl/class.php",
        "metaphp-data": "src/tpl/Sandwich.json",
        "metaphp-out": "src/Sandwich.php"
      }
    ]
  }
}


## Functions

PHP's standard library has a lot of functions that would work for basic file manipulation (`copy()`, `rename()`, `chdir()`, etc).  The
problem is error-signaling -- you have to explicitly check error-output, and this grows cumbersome with improvised glue code.  It's more
convenient to have a default 'stop-on-error' behavior, e.g.  throwing exceptions.

[symfony/filesystem](https://symfony.com/doc/current/components/filesystem.html) provides wrappers which throw exceptions.
But it puts them into a class `Filesystem` which, which requires more boilerplate.

For the most part, `CCL` simply mirrors `symfony/filesystem` using standalone functions in the `CCL` namespace. Compare:

```php
// PHP Standard Library
if (!copy('old', 'new')) {
  throw new \Exception("...");
}

// Symfony Filesystem
$fs = new \Symfony\Component\Filesystem\Filesystem();
$fs->copy('old', 'new');

// Quick and dirty
\CCL\copy('old', 'new');
```

This is more convenient for scripting one-liners - for example, these tasks do simple file operations. If anything
goes wrong, they raise an exception and stop the compilation process.

```javascript
{
  "extra": {
    "compile": [
      {
        "title": "Smorgasboard of random helpers",
        "run": [
          // Create files and folders
          "@php-eval \\CCL\\dumpFile('dist/timestamp.txt', date('Y-m-d H:i:s'));",
          "@php-eval \\CCL\\mkdir('some/other/place');",

          // Concatenate a few files
          "@php-eval \\CCL\\dumpFile('dist/bundle.js', \\CCL\\cat(glob('js/*.js'));",
          "@php-eval \\CCL\\chdir('css'); \\CCL\\dumpFile('all.css', ['colors.css', 'layouts.css']);",

          // If you need reference material from another package...
          "@export TWBS={{pkg:twbs/bootstrap}}",
          "@php-eval \\CCL\\copy(getenv('TWBS') .'/dist/bootstrap.css', 'web/main.css')"
        ]
      }
    ]
  }
}
```

The full function list:

```php
namespace CCL;

// CCL wrappers

function chdir(string $dir);
function glob($pat, $flags = null);
function cat($files);

// Symfony wrappers

function appendToFile($filename, $content);
function dumpFile($filename, $content);
function mkdir($dirs, $mode = 511);
function touch($files, $time = null, $atime = null);

function copy($originFile, $targetFile, $overwriteNewerFiles = true);
function mirror($originDir, $targetDir, $iterator = null, $options = []);
function remove($files);
function rename($origin, $target, $overwrite = false);

function chgrp($files, $group, $recursive = false);
function chmod($files, $mode, $umask = 0, $recursive = false);
function chown($files, $user, $recursive = false);

function hardlink($originFile, $targetFiles);
function readlink($path, $canonicalize = false);
function symlink($originDir, $targetDir, $copyOnWindows = false);

function exists($files);

function tempnam($dir, $prefix);
```
