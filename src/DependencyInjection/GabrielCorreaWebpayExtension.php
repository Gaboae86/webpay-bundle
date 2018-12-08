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

        $container->setParameter('payment_form_view', $config['views']['payment_form_view']);

        $container->setParameter('webpay_final_url', $config['webpay_params']['webpay_final_url']);
        $container->setParameter('webpay_path_key', $config['webpay_params']['webpay_path_key']);
        $container->setParameter('webpay_path_crt', $config['webpay_params']['webpay_path_crt']);
        $container->setParameter('webpay_is_dev_end', $config['webpay_params']['webpay_is_dev_end']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

}