<?php
/**
 * NetBrothersCreateBundle
 *
 * @author Stefan Wessel, NetBrothers GmbH
 * @date 16.12.20
 *
 */

namespace NetBrothers\NbCsbBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package NetBrothers\NbCreateSymfonyBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('netbrothers_nbcsb');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('template_dir')
                    ->info('path to templates (default to directory `installation/templates` in bundle)')
                    ->defaultValue('default')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}