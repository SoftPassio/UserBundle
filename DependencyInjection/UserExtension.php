<?php

namespace SoftPassio\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('soft_passio_app_user.default_role', $config['default_role']);
        $container->setParameter('soft_passio_app_user.acl.enabled', $config['acl']['enabled']);
        $container->setParameter(
            'soft_passio_app_user.acl.access_resolver_class',
            $config['acl']['access_resolver_class']
        );
        $container->setParameter('soft_passio_app_user.acl.access_denied_path', $config['acl']['access_denied_path']);
        $container->setParameter(
            'soft_passio_app_user.acl.access_denied_show_flash_message',
            $config['acl']['access_denied_show_flash_message']
        );

        foreach ($config['entities'] as $key => $entity) {
            $container->setParameter(sprintf('soft_passio_app_user.entities.%s', $key), $entity);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('doctrine.yml');
    }
}
