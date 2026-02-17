<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\DependencyInjection;

use LiquidLight\Anthology\Attribute\AsAnthologyFilter;
use LiquidLight\Anthology\Attribute\AsAnthologyRepository;
use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;
use LiquidLight\Anthology\Registry\AnthologyRepositoryRegistry;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AnthologyAttributeCompiler implements CompilerPassInterface
{
	public function process(ContainerBuilder $container): void
	{
		$filterRegistryDefinition = $container->findDefinition(AnthologyFilterRegistry::class);
		$repositoryRegistryDefinition = $container->findDefinition(AnthologyRepositoryRegistry::class);

		foreach ($container->getDefinitions() as $definition) {
			$className = $definition->getClass();

			if (!$className || !class_exists($className, false)) {
				continue;
			}

			$reflection = new ReflectionClass($className);

			if ($reflection->isAbstract()) {
				continue;
			}

			// Filters
			foreach ($reflection->getAttributes(AsAnthologyFilter::class) as $attribute) {
				$filterRegistryDefinition->addMethodCall(
					'add',
					[$className]
				);
			}

			// Repositories
			foreach ($reflection->getAttributes(AsAnthologyRepository::class) as $attribute) {
				$repositoryRegistryDefinition->addMethodCall(
					'add',
					[$className]
				);
			}
		}
	}
}
