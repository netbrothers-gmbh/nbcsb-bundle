<?php
/**
 * NetBrothersCreateBundle
 *
 * @author Stefan Wessel, NetBrothers GmbH
 * @date 16.12.20
 *
 */

namespace NetBrothers\NbCsbBundle;
use NetBrothers\NbCsbBundle\DependencyInjection\NetBrothersNbCsbExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetBrothersNbCsbBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new NetBrothersNbCsbExtension();
        }
        return $this->extension;
    }
}
