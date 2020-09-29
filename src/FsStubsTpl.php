<?php
/**
 * For every class-method in Symfony Filesystem, make a function in CCL namespace.
 * Write the result to a PHP file.
 */
namespace CCL\FsStubsTpl;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

$namespace = 'CCL';
$baseClass = Filesystem::class;
$useClasses = [IOException::class, FileNotFoundException::class];
$skipMethods = ['handleError'];
$outFile = 'compile-lib-fs.php';

$filterSignature = [];
$filterSignature['copy'] = function ($sig) {
    return preg_replace(';\$overwriteNewerFiles = FALSE;i', 'bool $overwriteNewerFiles = TRUE', $sig);
};

####################################################################################
## Utilities

/**
 * Export the value of $v as PHP code.
 *
 * @param mixed $v
 * @return string
 */
$export = function ($v) {
  if ($v === []) {
    return '[]';
  }
  return var_export($v, 1);
};

/**
 * Create a parameter-signature.
 *
 * @param \ReflectionParameter[] $params
 * @return string
 *   Ex: '$a, $b, $c = 100, $d = true'
 */
$formatSignature = function ($name, $params) use ($export, $filterSignature) {
  $sigs = [];
  foreach ($params as $param) {
    /**
     * @var \ReflectionParameter $param
     */

    // Note: we don't formally constrain parameter types in here, because that
    // yields a more stable signature across diff versions of Symfony Filesystem.
    $sig = '';
    $sig .= '$' . $param->getName();
    try {
        $sig .= ' = ' . $export($param->getDefaultValue(), 1);
    } catch (\ReflectionException $e) {
    }

    $sigs[] = $sig;
  }
  $sig = implode(', ', $sigs);

  if ($filterSignature[$name]) {
      $sig = call_user_func($filterSignature[$name], $sig);
  }
  return $sig;
};

/**
 * @param \ReflectionParameter[] $params
 * @return string
 */
$formatPassthru = function ($params) {
  $passthrus = [];
  foreach ($params as $param) {
    /**
     * @var \ReflectionParameter $param
     */
    $passthrus[] = '$' . $param->getName();
  }
  return implode(', ', $passthrus);
};

/**
 * @param int $spaces
 *   Number of leading spaces to add (positive) or remove (negative).
 * @param string $text
 * @return string
 */
$indent = function ($spaces, $text) {
  $lines = explode("\n", $text);
  $prefix = str_repeat(' ', abs($spaces));
  $remove = ($spaces < 0);
  $spaces = abs($spaces);
  foreach ($lines as &$line) {
    if ($remove) {
      if (substr($line, 0, $spaces) === $prefix) {
        $line = substr($line, $spaces);
      }
    } else {
      $line = $prefix . $line;
    }
  }
  return implode("\n", $lines);
};

$formatDocBlock = function ($text) {
  $prefix = function($line) {
    return " * $line";
  };

  return "/" . "**\n" .
    implode("\n", array_map($prefix, explode("\n", rtrim($text)))) . "\n" .
    " *" . "/\n";
};

####################################################################################
## Main

ob_start();

printf("<" . "?php\n");
printf("// AUTO-GENERATED VIA %s\n", __FILE__);
printf("namespace %s;\n", $namespace);
printf("\n");

foreach ($useClasses as $useClass) {
    printf("use %s;\n", $useClass);
}
printf("\n");
printf("%s", $formatDocBlock("@return $baseClass"));
printf("function fs() {\n");
printf("  static \$singleton = NULL;\n");
printf("  \$singleton = \$singleton ?: new \\%s();\n", $baseClass);
printf("  return \$singleton;\n");
printf("}\n");

$c = new \ReflectionClass($baseClass);
foreach ($c->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
  /**
   * @var \ReflectionMethod $method
   */

  if (in_array($method->getName(), $skipMethods)) {
    continue;
  }

  printf("\n");
  printf("%s\n", $indent(-4, $method->getDocComment()));
  printf("function %s(%s) {\n", $method->getName(), $formatSignature($method->getName(), $method->getParameters()));
  if (preg_match(';@return;', $method->getDocComment())) {
    printf("  return fs()->%s(%s);\n", $method->getName(), $formatPassthru($method->getParameters()));
  } else {
    printf("  fs()->%s(%s);\n", $method->getName(), $formatPassthru($method->getParameters()));
  }
  printf("}\n");
}

$code = ob_get_contents();
ob_end_clean();
file_put_contents($outFile, $code);
