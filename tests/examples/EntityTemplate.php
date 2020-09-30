<?php
/**
 * This template expects the input `$tplData`, which should be the name of the JSON data-file.
 * The file specifies:
 *
 * - namespace: string
 * - class: string
 * - fields: array (string $fieldName => string $type)
 */
namespace CCL\Tests\Examples;

$json = json_decode(\CCL\cat($GLOBALS['tplData']));

$funcName = function ($action, $fieldName) {
    return $action . ucfirst($fieldName);
};

echo "<" . "?php\n";
printf("namespace %s;\n\n", $json->namespace);
printf("class %s\n{\n", $json->class);

//list ($firstField) = array_shift(array_keys($json->fields));
$first = true;
foreach ($json->fields as $fieldName => $fieldType) {
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

foreach ($json->fields as $fieldName => $fieldType) {
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

foreach ($json->fields as $fieldName => $fieldType) {
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

printf("}\n", $json->class);
