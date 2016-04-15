<?php

namespace FlexModel\FlexModelElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * FlexModelElasticsearch loads and manages the bundle configuration.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FlexModelElasticsearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('flex_model.elasticsearch.index', strtolower($config['index']));
        $container->setParameter('flex_model.elasticsearch.hosts', $config['hosts']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
