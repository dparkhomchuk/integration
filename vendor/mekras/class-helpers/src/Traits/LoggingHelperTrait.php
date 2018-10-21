<?php
/**
 * Class helpers.
 */
namespace Mekras\ClassHelpers\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Logging helper.
 *
 * - Logger setter/getter.
 * - {@see getLogger()} return NullLogger when no logger was set — you can omit logger checks.
 * - Shortcut method to log exceptions.
 *
 * @since 1.0
 */
trait LoggingHelperTrait
{
    /**
     * Logger.
     *
     * @var LoggerInterface|null
     */
    private $logger = null;

    /**
     * Set logger.
     *
     * @param LoggerInterface $logger
     *
     * @since 1.1 Method made public.
     * @since 1.0
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return logger.
     *
     * @return LoggerInterface
     *
     * @since 1.0
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * Log exception.
     *
     * @param \Exception  $e      Exception.
     * @param string|null $action Failed action short description.
     * @param mixed       $level  Log level (default to LogLevel::ERROR).
     *
     * @since 1.1 Default log level is LogLevel::ERROR
     * @since 1.0
     */
    protected function logException(\Exception $e, $action = null, $level = LogLevel::ERROR)
    {
        $message = $action
            ? sprintf('%s failed: %s', $action, $e->getMessage())
            : sprintf('%s: %s', get_class($e), $e->getMessage());
        $this->getLogger()->log($level, $message, ['backtrace' => $e->getTraceAsString()]);
    }
}
