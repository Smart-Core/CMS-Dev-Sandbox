<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $resources = $container->getParameter('twig.form.resources');

        $resources[] = '@CMS/Form/fields.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
