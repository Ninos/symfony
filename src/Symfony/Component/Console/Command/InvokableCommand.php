<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Represents an invokable command.
 *
 * @author Yonel Ceruto <open@yceruto.dev>
 *
 * @internal
 */
class InvokableCommand
{
    private readonly \ReflectionFunction $reflection;

    public function __construct(
        private readonly Command $command,
        private readonly \Closure $code,
    ) {
        $this->reflection = new \ReflectionFunction($code);
    }

    /**
     * Invokes a callable with parameters generated from the input interface.
     */
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $statusCode = ($this->code)(...$this->getParameters($input, $output));

        if (null !== $statusCode && !\is_int($statusCode)) {
            // throw new LogicException(\sprintf('The command "%s" must return either void or an integer value in the "%s" method, but "%s" was returned.', $this->command->getName(), $this->reflection->getName(), get_debug_type($statusCode)));
            trigger_deprecation('symfony/console', '7.3', \sprintf('Returning a non-integer value from the command "%s" is deprecated and will throw an exception in PHP 8.0.', $this->command->getName()));

            return 0;
        }

        return $statusCode ?? 0;
    }

    /**
     * Configures the input definition from an invokable-defined function.
     *
     * Processes the parameters of the reflection function to extract and
     * add arguments or options to the provided input definition.
     */
    public function configure(InputDefinition $definition): void
    {
        foreach ($this->reflection->getParameters() as $parameter) {
            if ($argument = Argument::tryFrom($parameter)) {
                $definition->addArgument($argument->toInputArgument());
            } elseif ($option = Option::tryFrom($parameter)) {
                $definition->addOption($option->toInputOption());
            }
        }
    }

    private function getParameters(InputInterface $input, OutputInterface $output): array
    {
        $parameters = [];
        foreach ($this->reflection->getParameters() as $parameter) {
            if ($argument = Argument::tryFrom($parameter)) {
                $parameters[] = $argument->resolveValue($input);

                continue;
            }

            if ($option = Option::tryFrom($parameter)) {
                $parameters[] = $option->resolveValue($input);

                continue;
            }

            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType) {
                // throw new LogicException(\sprintf('The parameter "$%s" must have a named type. Untyped, Union or Intersection types are not supported.', $parameter->getName()));
                trigger_deprecation('symfony/console', '7.3', \sprintf('Omitting the type declaration for the parameter "$%s" is deprecated and will throw an exception in PHP 8.0.', $parameter->getName()));

                continue;
            }

            $parameters[] = match ($type->getName()) {
                InputInterface::class => $input,
                OutputInterface::class => $output,
                SymfonyStyle::class => new SymfonyStyle($input, $output),
                Application::class => $this->command->getApplication(),
                default => throw new RuntimeException(\sprintf('Unsupported type "%s" for parameter "$%s".', $type->getName(), $parameter->getName())),
            };
        }

        return $parameters ?: [$input, $output];
    }
}