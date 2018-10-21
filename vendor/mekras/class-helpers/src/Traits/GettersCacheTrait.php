<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Traits;

/**
 * Cache for getters.
 *
 * Allow to cache values returned by getters during the object lifetime.
 *
 * Example:
 *
 * ```php
 * class MyClass
 * {
 *     use GettersCacheTrait;
 *
 *     public function getFoo()
 *     {
 *         return $this->getCachedProperty(
 *             'foo',
 *             function () { return new FooClass($this->foo); }
 *         );
 *     }
 * ```
 *
 * Method `getCachedProperty` checks if key `foo` exists in the object's internal cache, and if it
 * exists, returns cached value. Otherwise factory function will be called to get value.
 *
 * @since 1.0
 */
trait GettersCacheTrait
{
    /**
     * Internal getters cache.
     *
     * @var array
     */
    private $gettersCache = [];

    /**
     * Return cached value for $name or call $factory to get new value.
     *
     * @param string   $name    Cache entry key.
     * @param \Closure $factory Value factory function.
     *
     * @return mixed
     *
     * @since 1.0
     */
    protected function getCachedProperty($name, \Closure $factory)
    {
        $name = (string) $name;
        if (!array_key_exists($name, $this->gettersCache)) {
            $this->gettersCache[$name] = call_user_func($factory);
        }

        return $this->gettersCache[$name];
    }

    /**
     * Change cached value.
     *
     * Useful for setters.
     *
     * @param string $name  Cache entry key.
     * @param mixed  $value New value.
     *
     * @since 1.0
     */
    protected function setCachedProperty($name, $value)
    {
        $this->gettersCache[$name] = $value;
    }

    /**
     * Drop cached value.
     *
     * @param string $name Cache entry key.
     *
     * @since 1.3
     */
    protected function dropCachedProperty($name)
    {
        unset($this->gettersCache[$name]);
    }

    /**
     * Drop ALL cached values.
     *
     * @since 1.4
     */
    protected function dropCachedProperties()
    {
        $this->gettersCache = [];
    }
}
