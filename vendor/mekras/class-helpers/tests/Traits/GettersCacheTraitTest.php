<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Tests\Traits;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * Tests for Mekras\ClassHelpers\Traits\GettersCacheTrait
 *
 * @covers Mekras\ClassHelpers\Traits\GettersCacheTrait
 */
class GettersCacheTraitTest extends TestCase
{
    /**
     * Prepare test case environment
     */
    public static function setUpBeforeClass()
    {
        eval(
        '
        namespace Mekras\ClassHelpers\Tests\Traits {
        class GettersCacheTraitTestFoo
            {
                use \Mekras\ClassHelpers\Traits\GettersCacheTrait;

                public $foo = 123;

                public function getFoo()
                {
                    return $this->getCachedProperty(
                        "foo",
                        function () {
                            $value = new \StdClass;
                            $value->id = $this->foo;
                            return $value;
                        }
                    );
                }

                public function setFoo($value)
                {
                    if (is_object($value)) {
                        $this->setCachedProperty("foo", $value);
                        $value = $value->id;
                    }
                    $this->foo = $value;
                }

                public function unsetFoo()
                {
                    $this->dropCachedProperty("foo");
                }
            }}');
    }

    /**
     * Check getter
     */
    public function testGetCachedValue()
    {
        $object = new GettersCacheTraitTestFoo();
        $foo1 = $object->getFoo();
        $foo2 = $object->getFoo();
        static::assertSame($foo1, $foo2);
        static::assertEquals(123, $foo2->id);
    }

    /**
     * Check setter
     */
    public function testSetCachedValue()
    {
        $object = new GettersCacheTraitTestFoo();
        $foo = new \stdClass();
        $foo->id = 456;
        $object->setFoo($foo);
        static::assertEquals(456, $object->getFoo()->id);
        static::assertEquals(456, $object->foo);
    }

    /**
     * Check dropping
     */
    public function testDropCachedValue()
    {
        $object = new GettersCacheTraitTestFoo();
        $foo1 = $object->getFoo();
        $foo2 = $object->getFoo();
        static::assertSame($foo1, $foo2);
        $object->unsetFoo();
        $foo2 = $object->getFoo();
        static::assertNotSame($foo1, $foo2);
    }
}
