<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
//--------------------
// Variables. Change these to meet your needs.
$file_types = ['php', 'css', 'js'];
$skip_directories = ['.git', 'cgi-bin', 'db', 'sqls', 'images'];
$starting_directory = __DIR__.'/';
// Initialize. No need to change anything below this line.
$stats = [];
$stats['gen'] = [];
$stats['gen']['commented_lines'] = 0;
$stats['gen']['character_count'] = 0;
$stats['gen']['blank_lines'] = 0;
$stats['gen']['bracket_lines'] = 0;
$stats['gen']['comment_blocks'] = 0;
$stats['gen']['classes'] = 0;
$stats['gen']['functions'] = 0;
$stats['included_files'] = [];
$stats['excluded_files'] = [];
$stats['skip'] = $skip_directories;
$stats['file_types'] = '('.implode('|', $file_types).')';
// Execute.
function countLines($dir, &$stats)
{
    $lineCounter = 0;
    $dirHandle = opendir($dir);
    $path = realpath($dir);
    $nextLineIsComment = false;
    if ($dirHandle) {
        while ($file = readdir($dirHandle)) {
            if (is_dir($path.'/'.$file) && ($file !== '.' && $file !== '..') && !in_array($file, $stats['skip'])) {
                $lineCounter += countLines($path.'/'.$file, $stats);
            } elseif (in_array($file, $stats['skip'])) {
                $stats['excluded_files'][] = $path.'/'.$file;
            } elseif ($file !== '.' && $file !== '..') {
                // Check if we have a valid file
                $ext = _findExtension($file);
                if (preg_match('/'.$stats['file_types'].'$/i', $ext)) {
                    $realFile = realpath($path).'/'.$file;
                    $fileArray = (array)file($realFile);
                    // Check content of file:
                        foreach ($fileArray as $iValue) {
                            if ($nextLineIsComment) {
                                ++$stats['gen']['commented_lines'];
                                // Look for the end of the comment block
                                if (strpos($iValue, '*/')) {
                                    $nextLineIsComment = false;
                                }
                            } else {
                                // Look for a function
                                if (strpos($iValue, 'function')) {
                                    ++$stats['gen']['functions'];
                                }
                                // Look for a commented line
                                if (strncmp(trim($iValue), '//', 2) === 0) {
                                    ++$stats['gen']['commented_lines'];
                                }
                                // Look for a class
                                if (strncmp(trim($iValue), 'class', 5) === 0) {
                                    ++$stats['gen']['classes'];
                                }
                                // Look for a comment block
                                if (strpos($iValue, '/*')) {
                                    $nextLineIsComment = true;
                                    ++$stats['gen']['commented_lines'];
                                    ++$stats['gen']['comment_blocks'];
                                }
                                //Look for a blank line
                                if (trim($iValue) == '') {
                                    ++$stats['gen']['blank_lines'];
                                }
                                // Look for lines that have an open or close bracket and nothing else.
                                if (trim($iValue) === '{' || trim($iValue) === '}') {
                                    ++$stats['gen']['bracket_lines'];
                                }
                            }
                    }
                    $lineCounter += count($fileArray);
                    // Mark as an included file.
                    $stats['included_files'][] = $path.'/'.$file;
                } else {
                    $stats['excluded_files'][] = $path.'/'.$file;
                }
            }
        }
    } else {
        echo 'Could not enter folder: '.$dir;
    }

    return $lineCounter;
}
function _findExtension($filename)
{
    $filename = strtolower($filename);
    $exts = (array)preg_split('[/\\.]', $filename);
    $n = count($exts) - 1;
    $exts = $exts[$n];

    return $exts;
}
if (!array_key_exists('lines_time', $_SESSION) || $_SESSION['lines_time'] > (time() + 300)) {
    $lines = countLines($starting_directory, $stats);
    $_SESSION['lines_count'] = $lines;
    $_SESSION['lines_time'] = time();
} else {
    $lines = $_SESSION['lines_count'];
}
?><tr>
    <th class="content-head">Stats</th>
</tr>
<tr>
    <td class="content">
        GRPG is currently made up of <?php echo format($lines); ?> lines of code.
    </td>
</tr>
