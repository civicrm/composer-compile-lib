<?php
namespace CCL;

use Symfony\Component\Filesystem\Exception\IOException;

if (!isset($GLOBALS['COMPOSER_COMPILE_TASK']) || empty($GLOBALS['COMPOSER_COMPILE_TASK']['-ccl-task'])) {
    include_once __DIR__ . '/compile-lib-fs.php';
}

function chdir($directory)
{
    if (!\chdir($directory)) {
        throw new IOException("Failed to change directory ($directory)");
    }
}

/**
 * @param string|string[] $pat
 *   List of glob patterns.
 * @param null|int $flags
 * @return array
 *   List of matching files.
 */
function glob($pats, $flags = null) {
    $r = [];
    $pats = (array) $pats;
    foreach ($pats as $pat) {
        $r = array_unique(array_merge($r, (array) \glob($pat, $flags)));
    }
    sort($r);
    return $r;
}

/**
 * Read a set of files and concatenate the results
 *
 * @param string|string[] $srcs
 *   Files to read. These may be globs.
 * @param string $newLine
 *   Whether to ensure that joined files have a newline separator.
 *   Ex: 'raw' (as-is), 'auto' (add if missing)
 * @return string
 *   The result of joining the files.
 */
function cat($srcs, $newLine = 'auto') {
    $buf = '';
    foreach (glob($srcs) as $file) {
        if (!is_readable($file)) {
            throw new \RuntimeException("Cannot read $file");
        }
        $buf .= file_get_contents($file);
        switch ($newLine) {
            case 'auto':
                if (substr($buf, -1) !== "\n") {
                    $buf .= "\n";
                }
                break;
            case 'raw':
                // Don't
                break;
        }
    }
    return $buf;
}

///**
// * Atomically dumps content into a file.
// *
// * @param string $filename The file to be written to
// * @param string $content  The data to write into the file
// *
// * @throws IOException if the file cannot be written to
// */
//function write($file, $content) {
//    \CCL\dumpFile($file, $content);
//}

///**
// * Copy file(s) to a destination.
// *
// * This does work with files or directories. However, if you wish to reference a directory, then
// * it *must* end with a trailing slash. Ex:
// *
// * Copy "infile.txt" to "outfile.txt"
// *   cp("infile.txt", "outfile.txt");
// *
// * Copy "myfile.txt" to "out-dir/myfile.txt"
// *   cp("myfile.txt", "out-dir/");
// *
// * Recursively copy "in-dir/*" into "out-dir/"
// *   cp("in-dir/*", "out-dir/");
// *
// * Recursively copy the whole "in-dir/" into "out-dir/deriv/"
// *   cp("in-dir/", "out-dir/deriv/");
// *
// * @param string $srcs
// * @param string $dest
// */
//function cp($srcs, $dest) {
//    $destType = substr($dest, -1) === '/' ? 'D' : 'F';
//
//    foreach (glob($srcs, MARK) as $src) {
//        $srcType = substr($src, -1) === '/' ? 'D' : 'F';
//        switch ($srcType . $destType) {
//        }
//    }
//
//}