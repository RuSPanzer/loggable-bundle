<?php

namespace Ruspanzer\LoggableBundle\DependencyInjection;

use Ruspanzer\LoggableBundle\EventListener\TablePrefixListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RuspanzerLoggableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $definition = $container->getDefinition(TablePrefixListener::class);
        $definition->replaceArgument(0, $config['table_prefix']);
    }
}
