<?php
define('NL', "\r\n");
define('DS', DIRECTORY_SEPARATOR);
define('CLASS_DIR', relativePath(getcwd(), dirname(__DIR__)) . DS);

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

set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_DIR);
spl_autoload_extensions('.class.php');
spl_autoload_register();
