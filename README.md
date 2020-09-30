# CiviCRM Compilation Library

This package provides a handful of small tasks and helpers for use with [composer-compile-plugin](https://github.com/civicrm/composer-compile-plugin).

Design guidelines:

* Use basic functions and static methods to allow easy operation in pre-boot environments.
* Every task/function must throw an exception if it doesn't work.
* If a task is outputting to a folder, and if the folder doesn't exist, then it should auto-create the folder.

It aims to support CiviCRM-related packages, but it is loosely coupled, so:

* CiviCRM-related packages can have compilation tasks which don't use any of these helpers.
* Other projects can use these helpers without CiviCRM.

## Require the library

All the examples below require the `civicrm/composer-compile-lib` package.

```javascript
  "require": {
    "civicrm/composer-compile-lib": "~1.0"
  }
```

## Task: SCSS

In this example, we generate a file `dist/sandwich.css` by reading `scss/sandwich.scss`.  The file may `@import` mixins and
variables from the `./scss/` folder.

```javascript
{
  "extra": {
    "compile": [
      {
        "title": "Whizbang CSS (<comment>dist/whizbang.css</comment>)",
        "run": "@php-method \\CCL\\Task::scss",
        "watch-files": ["scss"],
        "scss-files": {
          "dist/sandwich.css": "scss/sandwich.scss",
          "dist/salad.css": "scss/salad.scss"
        },
        "scss-imports": ["scss"]
        "scss-import-prefixes": {"LOGICAL_PREFIX/": "physical/folder"}
      }
    ]
  }
}
```

The task is simply a PHP method, so it can be invoked from a PHP script.  In this PHP script, we get a list of SCSS
files (`globMap(...)`) and feed that into `scss()`.

```php
$files = \CCL\globMap('scss/*.scss', 'dist/#1.css', 1);
\CCL\Task::scss([
  'scss-files' => $files,
  'scss-imports' => ['scss']
  'scss-import-prefixes' => ['LOGICAL_PREFIX/' => 'physical/folder']
]);
```

## Task: PHP Template

In this example, we use a PHP template (eg [`src/Entity/EntityTemplate.php`](tests/examples/EntityTemplate.php)) to generate a PHP class.
Specifically, the `Sandwich.php` entity will be generated from the [`Sandwich.json`](tests/examples/Sandwich.json) specification.

```javascript
{
  "extra": {
    "compile": [
      {
        "title": "Sandwich (<comment>src/Sandwich.php</comment>)",
        "run": "@php-method \\CCL\\Task::template",
        "watch-files": ["src/Entity"],
        "tpl-file": "src/Entity/EntityTemplate.php",
        "tpl-items": [
          "src/Entity/Sandwich.php": "src/Entity/Sandwich.json",
          "src/Entity/Salad.php": "src/Entity/Salad.json"
        ],
      }
    ]
  }
}
```

Again, the task is simply a PHP method, so it can be used from a PHP script.  This example maps all JSON files to PHP files:

```php
$files = \CCL\globMap('src/Entity/*.json', 'src/Entity/#1.php', 1);
\CCL\Task::template([
  "tpl-file" => "src/Entity/EntityTemplate.php",
  "tpl-items" => $files,
]);
```

In either case, it loads the `tpl-file` (`EntityTemplate.php`).  The template receives one input (e.g. `$tplData ==
"src/Entity/Sandwich.json"`).

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

// CCL wrapper functions

function chdir(string $dir);
function glob($pat, $flags = null);
function cat($files);

// CCL distinct functions

function mapkv($array, $func);
funciton globMap($globPat, $mapPat, $flip = false);

// Symfony wrapper functions

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
