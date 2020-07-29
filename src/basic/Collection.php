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
 * An object to work with arrays in a more oop friendly way.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Collection implements \Iterator
{
    private array $_container;
    private array $_keys;
    private int $_position;
    
    public function __construct(array $array = [])
    {
        $this->setContainer($array);
    }
    
    public function getContainer(): array
    {
        return $this->_container;
    }
    
    public function setContainer(array $array): void
    {
        $this->_container = $array;
        $this->_keys = array_keys($this->_container);
        $this->rewind();
    }
    
    public function get($key)
    {
        if(isset($this->_container[$key])) {
            return $this->_container[$key];
        }
        
        return null;
    }
    
    public function set($key, $value): void
    {
        $this->_container[$key] = $value;
        $this->_keys[] = $key;
    }
    
    public function unset($key): void
    {
        unset($this->_container[$key]);
        unset($this->_keys[array_search($key, $this->_keys)]);
    }
    
    public function count(): int
    {
        return count($this->_keys);
    }
    
    public function rewind(): void
    {
        $this->_position = 0;
    }
    
    public function current()
    {
        return $this->_container[$this->_keys[$this->_position]];
    }
    
    public function key()
    {
        return $this->_keys[$this->_position];
    }
    
    public function next(): void
    {
        ++$this->_position;
    }
    
    public function valid(): bool
    {
        return isset($this->_keys[$this->_position]);
    }
}