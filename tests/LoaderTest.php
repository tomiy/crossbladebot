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

namespace CrossbladeBotTests;

use PHPUnit\Framework\TestCase;

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Component\Loader;

/**
 * Test case for the Loader class.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class LoaderTest extends TestCase
{
    /**
     * Assert that you can create a Loader object.
     *
     * @return void
     */
    public function testCanInstantiate(): void
    {
        $this->assertInstanceOf(Loader::class, new Loader(new Logger()));
    }
}
