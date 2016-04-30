<?php

namespace Jum4\DoctrineLoggerBundle\DependencyInjection;

use Jum4\DoctrineLoggerBundle\Config\EntityConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class Jum4DoctrineLoggerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('jum4_doctrine_logger.level', $config['level']);

        if (isset($config['channel'])) {
            $definition = $container->findDefinition('jum4_doctrine_logger.doctrine_logger');
            $definition->addTag('monolog.logger', ['channel' => $config['channel']]);
            $container->setDefinition('jum4_doctrine_logger.doctrine_logger', $definition);
        }
    }
}
