<?php

namespace SmartCore\Bundle\SettingsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SmartSettingsExtension extends Extension
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

        //$container->setParameter('smart_core.settings.table_prefix', $config['table_prefix']);
        $container->setParameter('smart_core.settings.setting_manager', $config['setting_manager']);
        $container->setParameter('smart_core.settings.doctrine_cache_provider', $config['doctrine_cache_provider']);
        $container->setParameter('smart_core.settings.show_bundle_column', $config['show_bundle_column']);

        $alias = new Alias($config['setting_manager']);
        $alias->setPublic(true);

        $container->setAlias('settings', $alias);
    }
}
