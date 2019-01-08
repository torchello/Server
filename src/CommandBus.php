<?php

namespace Rubix\Server;

use Rubix\Server\Commands\Command;
use Rubix\Server\Handlers\Handler;
use InvalidArgumentException;
use RuntimeException;

/**
 * Command Bus
 *
 * The command pattern is a behavioral design pattern in which a command
 * object is used to encapsulate all information needed to perform an
 * action. The command bus is responsible for dispatching the commands to
 * their appropriate handlers.
 *
 * @category    Machine Learning
 * @package     Rubix/Server
 * @author      Andrew DalPino
 */
class CommandBus
{
    /**
     * The mapping of commands to their handlers.
     * 
     * @var array
     */
    protected $mapping;

    /**
     * @param  array  $mapping
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct(array $mapping)
    {
        foreach ($mapping as $classname => $handler) {
            if (!class_exists($classname)) {
                throw new InvalidArgumentException("$classname does"
                    . ' not exist.');
            }

            if (!$handler instanceof Handler) {
                throw new InvalidArgumentException('Command must map'
                    . ' to a handler, ' . get_class($handler)
                    . ' found.');
            }
        }

        $this->mapping = $mapping;
    }

    /**
     * Dispatch the command to a handler.
     * 
     * @param  \Rubix\Server\Commands\Command  $command
     * @throws \RuntimeException
     * @return mixed
     */
    public function dispatch(Command $command)
    {
        $className = get_class($command);

        $handler = $this->mapping[$className] ?? null;

        if ($handler) {
            return $handler->handle($command);
        }

        throw new RuntimeException('An appropriate handler could'
            . " not be located for $className.");
    }
}