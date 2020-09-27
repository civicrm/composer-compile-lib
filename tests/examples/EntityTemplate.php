<?php
/**
 * This template expects the following inputs:
 *
 * namespace: string
 * class: string
 * fields: array (string $fieldName => string $type)
 */
namespace Qnd\Tests\Examples;

$funcName = function ($action, $fieldName) {
    return $action . ucfirst($fieldName);
};

global $metaphp;

echo "<" . "?php\n";
printf("namespace %s;\n\n", $metaphp->namespace);
printf("class %s\n{\n", $metaphp->class);

//list ($firstField) = array_shift(array_keys($metaphp->fields));
$first = true;
foreach ($metaphp->fields as $fieldName => $fieldType) {
    if ($first) {
        $first = false;
    } else {
        echo "\n";
    }
    printf("    /**\n");
    printf("     * @var %s\n", $fieldType);
    printf("     */\n");
    printf("    protected \$%s;\n", $fieldName);
}

foreach ($metaphp->fields as $fieldName => $fieldType) {
    $literalType = strpos($fieldType, '[]') === false ? $fieldType : 'array';

    printf("\n");
    printf("    /**\n");
    printf("     * @return %s\n", $fieldType);
    printf("     */\n");
    printf("    public function %s(): %s\n", $funcName('get', $fieldName), $literalType);
    printf("    {\n");
    printf("        return \$this->%s;\n", $fieldName);
    printf("    }\n");
}

foreach ($metaphp->fields as $fieldName => $fieldType) {
    $literalType = strpos($fieldType, '[]') === false ? $fieldType : 'array';

    printf("\n");
    printf("    /**\n");
    printf("     * @return %s\n", $fieldType);
    printf("     */\n");
    printf("    public function %s(%s \$%s)\n", $funcName('set', $fieldName), $literalType, $fieldName);
    printf("    {\n");
    printf("        \$this->%s = \$%s;\n", $fieldName, $fieldName);
    printf("    }\n");
}

printf("}\n", $metaphp->class);
