<?php

namespace DeejayPoolBundle;

use DeejayPoolBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DeejayPoolBundle extends Bundle
{

    const PROVIDER_AVD  = 'av_district';
    const PROVIDER_FP   = 'franchise_pool';
    const BUNDLE_ALIAS  = 'deejay_pool';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ProviderCompilerPass());
    }
}
