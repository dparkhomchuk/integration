<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Traits;

/**
 * Property container.
 *
 * Example:
 *
 * ```php
 * class MyClass
 * {
 *     use PropertyContainerTrait;
 *
 *     public function getFoo()
 *     {
 *         return $this->getPropertyValue('foo', 'default value);
 *     }
 *
 *     public function setFoo($value)
 *     {
 *         return $this->setPropertyValue('foo', $value);
 *     }
 * }
 * ```
 *
 * @since 1.5
 */
trait PropertyContainerTrait
{
    /**
     * Property container.
     *
     * @var array
     */
    private $properties = [];

    /**
     * Return property value.
     *
     * @param string $property Property name.
     * @param mixed  $default  Default value if property not set.
     *
     * @return mixed
     *
     * @since 1.5
     */
    protected function getPropertyValue($property, $default = null)
    {
        if (array_key_exists($property, $this->properties)) {
            return $this->properties[$property];
        }

        return $default;
    }

    /**
     * Set property value.
     *
     * @param string $property Property name.
     * @param mixed  $value    Property value.
     *
     * @since 1.5
     */
    protected function setPropertyValue($property, $value)
    {
        $this->properties[$property] = $value;
    }

    /**
     * Get all properties as array.
     *
     * @return array
     *
     * @since 1.5
     */
    protected function getProperties()
    {
        return $this->properties;
    }
}
