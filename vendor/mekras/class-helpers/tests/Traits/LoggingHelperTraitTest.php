<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Tests\Traits;

use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Tests for Mekras\ClassHelpers\Traits\LoggingHelperTrait
 *
 * @covers Mekras\ClassHelpers\Traits\LoggingHelperTrait
 */
class LoggingHelperTraitTest extends TestCase
{
    /**
     * Prepare test case environment
     */
    public static function setUpBeforeClass()
    {
        eval(
        '
        namespace Mekras\ClassHelpers\Tests\Traits {
        class LoggingHelperTraitTestFoo
            {
                use \Mekras\ClassHelpers\Traits\LoggingHelperTrait;

                public function get()
                {
                    return $this->getLogger();
                }

                public function set($value)
                {
                    $this->setLogger($value);
                }

                public function log()
                {
                    call_user_func_array([$this, "logException"], func_get_args());
                }
            }}');
    }

    /**
     *
     */
    public function testGetSetLogger()
    {
        $helper = new LoggingHelperTraitTestFoo();
        $logger = new NullLogger();
        $helper->set($logger);
        static::assertSame($logger, $helper->get());
    }

    /**
     *
     */
    public function testGetNullLogger()
    {
        $helper = new LoggingHelperTraitTestFoo();
        static::assertInstanceOf('Psr\Log\NullLogger', $helper->get());
    }

    /**
     *
     */
    public function testLogException()
    {
        $helper = new LoggingHelperTraitTestFoo();
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger->expects(static::once())->method('log')->with(LogLevel::ERROR, 'Exception: Foo');
        $helper->set($logger);
        $e = new \Exception('Foo');
        $helper->log($e);
    }
}
