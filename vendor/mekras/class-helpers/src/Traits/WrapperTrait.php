<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Traits;

/**
 * Helper for creating wrapper classes.
 *
 * Usage:
 *
 * 1. create some class and add this trait;
 * 2. call setWrappedObject();
 * 3. define methods that you want to override.
 *
 * Access to all other methods and properties will be proxied to a wrapped object.
 *
 * @since 1.0
 */
trait WrapperTrait
{
    /**
     * Wrapped object.
     *
     * @var object|null
     *
     * @since 1.0
     */
    protected $wrappedObject;

    /**
     * Proxy property reading.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @since 1.0
     */
    public function __get($name)
    {
        return $this->wrappedObject->{$name};
    }

    /**
     * Proxy property writing.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     *
     * @since 1.0
     */
    public function __set($name, $value)
    {
        $this->wrappedObject->{$name} = $value;
    }

    /**
     * Proxy method calls.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     *
     * @since 1.0
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array([$this->wrappedObject, $name], $arguments);
    }

    /**
     * Set wrapped object.
     *
     * @param object $object
     *
     * @since 1.0
     */
    protected function setWrappedObject($object)
    {
        assert('is_object($object)');
        $this->wrappedObject = $object;
    }
}
