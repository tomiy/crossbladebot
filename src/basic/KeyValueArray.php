<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace crossbladebot\basic;

/**
 * A workaround to use more oop friendly get/set functions for arrays.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class KeyValueArray
{
    private array $_array;
    
    public function __construct(array $array)
    {
        $this->_array = $array;
    }
    
    public function getArray(): array
    {
        return $this->_array;
    }
    
    public function setArray(array $array): void
    {
        $this->_array = $array;
    }
    
    public function get($key)
    {
        if(isset($this->_array[$key])) {
            return $this->_array[$key];
        }
        
        return null;
    }
    
    public function set($key, $value): void
    {
        $this->_array[$key] = $value;
    }
}