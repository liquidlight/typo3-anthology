<?php

declare(strict_types=1);

namespace LiquidLight\Anthology;

use LiquidLight\Anthology\DependencyInjection\AnthologyAttributeCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (
	ContainerConfigurator $container,
	ContainerBuilder $containerBuilder
) {
	$containerBuilder->addCompilerPass(new AnthologyAttributeCompiler());
};
