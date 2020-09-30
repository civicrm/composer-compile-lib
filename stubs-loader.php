<?php
namespace CCL;

// Define the stubs in most page-loads, but not during our own/internal/CCL compilation.
if (empty(getenv('COMPOSER_COMPILE_TASK')) ||
  (!empty($GLOBALS['COMPOSER_COMPILE_TASK']) && empty($GLOBALS['COMPOSER_COMPILE_TASK']['-ccl-task']))
) {
  include_once __DIR__ . '/stubs-dynamic.php';
}
