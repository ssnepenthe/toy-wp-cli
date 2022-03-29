<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Invoker\Invoker;
use Invoker\InvokerInterface;

class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var InvokerInterface
     */
    protected $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker();
    }

    /**
     * @return mixed
     */
    public function call($callback)
    {
        return $this->invoker->call($callback);
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command, array $args, array $assocArgs)
    {
        $parameters = [
            'args' => $args,
            'arguments' => $args,
            'assocArgs' => $assocArgs,
            'assoc_args' => $assocArgs,
            'opts' => $assocArgs,
            'options' => $assocArgs,
        ];

        $registeredArgs = $command->getArguments();

        while (count($args)) {
            $current = array_shift($registeredArgs);

            $name = $current->getName();
            $snakeName = $this->snakeCase($name);
            $camelName = $this->camelCase($name);

            if ($current->getRepeating()) {
                $parameters[$snakeName] = $args;
                $parameters[$camelName] = $args;

                $args = [];
            } else {
                $arg = array_shift($args);

                $parameters[$snakeName] = $arg;
                $parameters[$camelName] = $arg;
            }
        }

        foreach ($command->getOptions() as $option) {
            $name = $option->getName();
            $snakeName = $this->snakeCase($name);
            $camelName = $this->camelCase($name);

            if (array_key_exists($name, $assocArgs)) {
                $parameters[$snakeName] = $assocArgs[$name];
                $parameters[$camelName] = $assocArgs[$name];

                unset($assocArgs[$name]);
            } elseif ($option instanceof Flag) {
                $parameters[$snakeName] = false;
                $parameters[$camelName] = false;
            }
        }

        if ($command->getAcceptArbitraryOptions() && ! empty($assocArgs)) {
            $parameters['arbitraryOptions'] = $assocArgs;
            $parameters['arbitrary_options'] = $assocArgs;
        }

        return $this->invoker->call($command->getHandler(), $parameters);
    }

    protected function camelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }

    protected function snakeCase(string $string): string
    {
        return strtolower(str_replace('-', '_', $string));
    }
}
