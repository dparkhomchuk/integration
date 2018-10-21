<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Tests\Traits;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * Tests for Mekras\ClassHelpers\Traits\PropertyContainerTrait.
 *
 * @covers Mekras\ClassHelpers\Traits\PropertyContainerTrait
 */
class PropertyContainerTest extends TestCase
{
    /**
     * Prepare test case environment
     */
    public static function setUpBeforeClass()
    {
        eval(
        '
        namespace Mekras\ClassHelpers\Tests\Traits {
        class PropertyContainerTraitTestFoo
            {
                use \Mekras\ClassHelpers\Traits\PropertyContainerTrait;

                public function getFoo()
                {
                    return $this->getPropertyValue(
                        "foo",
                        "Foo"
                    );
                }

                public function setFoo($value)
                {
                    $this->setPropertyValue("foo", $value);
                }

                public function export()
                {
                    return $this->getProperties();
                }
            }}');
    }

    /**
     * Basics checks.
     */
    public function testBasics()
    {
        $object = new PropertyContainerTraitTestFoo();
        static::assertEquals('Foo', $object->getFoo());
        $object->setFoo('Bar');
        static::assertEquals('Bar', $object->getFoo());
        static::assertEquals(['foo' => 'Bar'], $object->export());
    }
}
