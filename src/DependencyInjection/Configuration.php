<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 27-11-18
 * Time: 22:35
 */

namespace GabrielCorrea\WebpayBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gabriel_correa_webpay');
        $rootNode
            ->children()
            ->arrayNode('handler')
            ->children()
            ->scalarNode('save_transaction_handler')->isRequired()->end()
            ->end()
            ->end()
            ->end();
        return $treeBuilder;
    }

}