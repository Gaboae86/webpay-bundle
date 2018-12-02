<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 18-11-18
 * Time: 22:25
 */

namespace GabrielCorrea\WebpayBundle\DependencyInjection;


use GabrielCorrea\WebpayBundle\Interfaces\SaveTransactionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GabrielCorreaWebpayExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias(SaveTransactionInterface::class, $config['handler']['save_transaction_handler']);
        $container->setAlias('gabriel_correa_webpay_save_transaction_handler',
            $config['handler']['save_transaction_handler']);


        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

}