<?php
/**
 * For every class-method in Symfony Filesystem, make a function in Qnd namespace.
 * Write the result to 'qnd-dynamic.php'.
 */
namespace Qnd\FsStubsTpl;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

$namespace = 'Qnd';
$baseClass = Filesystem::class;
$useClasses = [IOException::class, FileNotFoundException::class];
$outFile = 'qnd-dynamic.php';

####################################################################################
## Utilities

/**
 * Export the value of $v as PHP code.
 *
 * @param mixed $v
 * @return string
 */
$export = function ($v) {
    switch ($v) {
        case null:
        case true:
        case false:
            return strtolower(var_export($v, 1));
        case []:
            return '[]';
        default:
            return var_export($v, 1);
    }
};

/**
 * Create a parameter-signature.
 *
 * @param \ReflectionParameter[] $params
 * @return string
 *   Ex: '$a, $b, $c = 100, $d = true'
 */
$formatSignature = function ($params) use ($export) {
    $sigs = [];
    foreach ($params as $param) {
        /** @var \ReflectionParameter $param */
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
    return implode(', ', $sigs);
};

/**
 * @param \ReflectionParameter[] $params
 * @return string
 */
$formatPassthru = function ($params) {
    $passthrus = [];
    foreach ($params as $param) {
        /** @var \ReflectionParameter $param */
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

####################################################################################
## Main

ob_start();

printf("<" . "?php\n");
printf("namespace %s;\n", $namespace);
printf("// AUTO-GENERATED VIA %s\n", __FILE__);
printf("\n");

foreach ($useClasses as $useClass) {
    printf("use \\%s;\n", $useClass);
}
printf("\n");
printf("function fs(): \\%s\n", $baseClass);
printf("{\n");
printf("    static \$singleton = null;\n");
printf("    \$singleton = \$singleton ?: new \\%s();\n", $baseClass);
printf("    return \$singleton;\n");
printf("}\n");

$c = new \ReflectionClass($baseClass);
foreach ($c->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
    /** @var \ReflectionMethod $method */

    printf("\n");
    printf("%s\n", $indent(-4, $method->getDocComment()));
    printf("function %s(%s)\n", $method->getName(), $formatSignature($method->getParameters()));
    printf("{\n");
    if (preg_match(';@return;', $method->getDocComment())) {
        printf("    return fs()->%s(%s);\n", $method->getName(), $formatPassthru($method->getParameters()));
    } else {
        printf("    fs()->%s(%s);\n", $method->getName(), $formatPassthru($method->getParameters()));
    }
    printf("}\n");
}

$code = ob_get_contents();
ob_end_clean();
file_put_contents($outFile, $code);
