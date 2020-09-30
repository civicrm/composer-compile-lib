<?php
/**
 * For every class-method in Symfony `Filesystem` and CCL `Functions`, make a function in CCL namespace.
 * Write the result to a PHP file.
 *
 * NOTE: It's a bit annoying that PHP supports auto-loading of classes but not of functions; this means that
 * namespaced functions have to be parsed fully (even if they're not going to be used). But if it's
 * any consolation, we only load the 1-line wrappers.
 */
namespace CCL\FsStubsTpl;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

$namespace = 'CCL';
$baseClasses = [Filesystem::class => '_sym', \CCL\Functions::class => '_ccl'];
$useClasses = [IOException::class, FileNotFoundException::class];
$skipMethods = ['handleError'];
$outFile = 'stubs-dynamic.php';

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
  if ($v === TRUE || $v === FALSE || $v === NULL) {
    return strtoupper(var_export($v, 1));
  }
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

foreach ($baseClasses as $baseClass => $singletonFunc) {
  printf("\n");
  printf("%s", $formatDocBlock("@return $baseClass"));
  printf("function %s() {\n", $singletonFunc);
  printf("  static \$singleton = NULL;\n");
  printf("  \$singleton = \$singleton ?: new \\%s();\n", $baseClass);
  printf("  return \$singleton;\n");
  printf("}\n");
}

foreach ($baseClasses as $baseClass => $singletonFunc) {
  $c = new \ReflectionClass($baseClass);
  foreach ($c->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
    /**
     * @var \ReflectionMethod $method
     */

    if (in_array($method->getName(), $skipMethods)) {
      continue;
    }

    preg_match(';\n( +);m', $method->getDocComment(), $oldDocSpaces);

    printf("\n");
    printf("%s\n", $indent(1 - strlen($oldDocSpaces[1] ?? ''), $method->getDocComment()));
    printf("function %s(%s) {\n", $method->getName(), $formatSignature($method->getName(), $method->getParameters()));
    if (preg_match(';@return;', $method->getDocComment())) {
      printf("  return %s()->%s(%s);\n", $singletonFunc, $method->getName(), $formatPassthru($method->getParameters()));
    } else {
      printf("  %s()->%s(%s);\n", $singletonFunc, $method->getName(), $formatPassthru($method->getParameters()));
    }
    printf("}\n");
  }
}

$code = ob_get_contents();
ob_end_clean();
file_put_contents($outFile, $code);