<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Extension;

use MonorepoBuilder20211223\Symfony\Component\Config\Definition\ConfigurationInterface;
use MonorepoBuilder20211223\Symfony\Component\Config\Definition\Processor;
use MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Container;
use MonorepoBuilder20211223\Symfony\Component\DependencyInjection\ContainerBuilder;
use MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\LogicException;
/**
 * Provides useful features shared by many extensions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Extension implements \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Extension\ExtensionInterface, \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface
{
    /**
     * @var mixed[]
     */
    private $processedConfigs = [];
    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://example.org/schema/dic/' . $this->getAlias();
    }
    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * This convention is to remove the "Extension" postfix from the class
     * name and then lowercase and underscore the result. So:
     *
     *     AcmeHelloExtension
     *
     * becomes
     *
     *     acme_hello
     *
     * This can be overridden in a sub-class to specify the alias manually.
     *
     * @throws BadMethodCallException When the extension name does not follow conventions
     */
    public function getAlias() : string
    {
        $className = static::class;
        if (\substr_compare($className, 'Extension', -\strlen('Extension')) !== 0) {
            throw new \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\BadMethodCallException('This extension does not follow the naming convention; you must overwrite the getAlias() method.');
        }
        $classBaseName = \substr(\strrchr($className, '\\'), 1, -9);
        return \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Container::underscore($classBaseName);
    }
    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $class = static::class;
        if (\strpos($class, "\0") !== \false) {
            return null;
            // ignore anonymous classes
        }
        $class = \substr_replace($class, '\\Configuration', \strrpos($class, '\\'));
        $class = $container->getReflectionClass($class);
        if (!$class) {
            return null;
        }
        if (!$class->implementsInterface(\MonorepoBuilder20211223\Symfony\Component\Config\Definition\ConfigurationInterface::class)) {
            throw new \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\LogicException(\sprintf('The extension configuration class "%s" must implement "%s".', $class->getName(), \MonorepoBuilder20211223\Symfony\Component\Config\Definition\ConfigurationInterface::class));
        }
        if (!($constructor = $class->getConstructor()) || !$constructor->getNumberOfRequiredParameters()) {
            return $class->newInstance();
        }
        return null;
    }
    protected final function processConfiguration(\MonorepoBuilder20211223\Symfony\Component\Config\Definition\ConfigurationInterface $configuration, array $configs) : array
    {
        $processor = new \MonorepoBuilder20211223\Symfony\Component\Config\Definition\Processor();
        return $this->processedConfigs[] = $processor->processConfiguration($configuration, $configs);
    }
    /**
     * @internal
     */
    public final function getProcessedConfigs() : array
    {
        try {
            return $this->processedConfigs;
        } finally {
            $this->processedConfigs = [];
        }
    }
    /**
     * @throws InvalidArgumentException When the config is not enableable
     */
    protected function isConfigEnabled(\MonorepoBuilder20211223\Symfony\Component\DependencyInjection\ContainerBuilder $container, array $config) : bool
    {
        if (!\array_key_exists('enabled', $config)) {
            throw new \MonorepoBuilder20211223\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException("The config array has no 'enabled' key.");
        }
        return (bool) $container->getParameterBag()->resolveValue($config['enabled']);
    }
}