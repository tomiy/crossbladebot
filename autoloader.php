<?php
/**
 * IRC only accepts \r\n (not \n) so on Windows systems you can't use PHP_EOL.
 * Use this constant instead to signify an EOL in messages. (handled by the socket)
 */
define('NL', "\r\n");
define('DS', DIRECTORY_SEPARATOR);
define('CLASS_DIR', relativePath(getcwd(), dirname(__DIR__)) . DS);

/**
 * Finds the relative path between 2 folders.
 *
 * @param string $source The source folder.
 * @param string $destin The destination folder.
 * @return string The relative path between the 2.
 */
function relativePath(string $source, string $destin): string
{
    $arFrom = explode(DS, rtrim($source, DS));
    $arTo = explode(DS, rtrim($destin, DS));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
        array_shift($arFrom);
        array_shift($arTo);
    }
    return rtrim(str_pad('', count($arFrom) * 3, '..' . DS) . implode(DS, $arTo), DS);
}

/**
 * Add the parent directory to the include path for the default autoloader to register it.
 * The classes will have .class.php as an extension, to differentiate them from normal PHP.
 * Register the built-in autoloader. (pure C, faster)
 */
set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_DIR);
spl_autoload_extensions('.class.php');
spl_autoload_register();
