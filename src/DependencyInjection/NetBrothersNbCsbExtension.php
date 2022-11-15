<?php
/**
 * NetBrothersCreateBundle
 *
 * @author Stefan Wessel, NetBrothers GmbH
 * @date 16.12.20
 *
 */

namespace NetBrothers\NbCsbBundle\DependencyInjection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class NbCreateSymfonyBundleExtension
 * @package NetBrothers\NbCreateSymfonyBundle\DependencyInjection
 */
class NetBrothersNbCsbExtension extends Extension
{

    /** Setting config to service
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $configuration = new Configuration();
        $loader->load('services.xml');
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('netbrothers_nbcsb', $config);
        $createBundleCommand = $container->getDefinition('netbrothers_nbcsb.command.create_bundle_command');
        $createBundleCommand->setArgument(0, $container->getParameter('netbrothers_nbcsb'));
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'netbrothers_nbcsb';
    }
}
