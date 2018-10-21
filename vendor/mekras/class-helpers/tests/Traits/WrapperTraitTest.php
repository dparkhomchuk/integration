<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Tests\Traits;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * Tests for Mekras\ClassHelpers\Traits\WrapperTrait
 *
 * @covers Mekras\ClassHelpers\Traits\WrapperTrait
 */
class WrapperTraitTest extends TestCase
{
    /**
     * Prepare test case environment
     */
    public static function setUpBeforeClass()
    {
        eval(
            '
            namespace Mekras\ClassHelpers\Tests\Traits {
                class WrapperTraitTest_Real
                {
                    public $foo;
                    public function bar($baz)
                    {
                        return $baz;
                    }
                }
                class WrapperTraitTest_Wrap
                {
                    use \Mekras\ClassHelpers\Traits\WrapperTrait;
                    public function __construct($object)
                    {
                        $this->setWrappedObject($object);
                    }
                }
            }'
        );
    }

    /**
     * Basic checks
     */
    public function testOverall()
    {
        $real = new WrapperTraitTest_Real();
        $proxy = new WrapperTraitTest_Wrap($real);

        $proxy->foo = 'FOO';
        static::assertEquals('FOO', $proxy->foo);
        static::assertEquals('FOO', $real->foo);

        static::assertEquals('BAZ', $proxy->bar('BAZ'));
    }
}
