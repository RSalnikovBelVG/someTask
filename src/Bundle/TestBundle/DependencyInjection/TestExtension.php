<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use function dirname;

class TestExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $yamlLoader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $yamlLoader->load('services.yaml');

        $definition = $container->getDefinition('calculate');
        $definition->setArgument(0, $mergedConfig);
    }
}