<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Command;

use LiquidLight\Anthology\Exception\InvalidSetupValueException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(
	name: 'anthology:setup',
	description: 'Setup Anthology configuration',
)]
class SetupCommand extends Command
{
	protected const COMMAND_TITLE = 'Anthology setup';

	protected const EXTENSION_KEY = 'LlAnthology';

	protected const PLUGIN_NAME = 'AnthologyView';

	protected const LIST_CONTROLLER = 'Anthology::list';

	protected const SINGLE_CONTROLLER = 'Anthology::single';

	protected SymfonyStyle $io;

	protected string $sitePackagePath;

	protected string $tcaName;

	protected int $modelPid;

	protected int $listPageUid;

	protected int $singlePageUid;

	protected string $modelName;

	protected function configure(): void
	{
		$this->addArgument('sitePackagePath', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->io = new SymfonyStyle($input, $output);

		$this->io->title(static::COMMAND_TITLE);

		// Assign options to properties
		try {
			$this->sitePackagePath = $this->getSitePackagePath($input);
			$this->tcaName = $this->getTcaName();
			$this->modelPid = $this->getModelPid();
			$this->listPageUid = $this->getListPageUid();
			$this->singlePageUid = $this->getSinglePageUid();
			$this->modelName = $this->getModelName($this->tcaName);
		} catch (InvalidSetupValueException $e) {
			$this->io->error($e->getMessage());
			return Command::FAILURE;
		}

		// A. Create route enhancers
		$routeEnhancers = $this->processRouteEnhancers();

		// B. Add sitemap configuration
		$sitemapConfiguration = $this->processSitemapConfiguration();

		// C. Add link handler
		$linkHandler = $this->processLinkHandler();

		// Return success/failure
		return $routeEnhancers && $sitemapConfiguration && $linkHandler
			? Command::SUCCESS
			: Command::FAILURE;
	}

	protected function getSitePackagePath(InputInterface $input): string
	{
		$sitePackagePath = realpath($input->getArgument('sitePackagePath'));

		if (!$sitePackagePath) {
			throw new InvalidSetupValueException('Invalid path supplied');
		}

		return $sitePackagePath;
	}

	protected function getTcaName(): string
	{
		$tcaName = $this->io->ask(
			'Enter the TCA/table name (e.g. `tx_myextension_domain_model_item`)',
			validator: $this->validateTca(...)
		);

		if (!$tcaName) {
			throw new InvalidSetupValueException('Invalid TCA entered');
		}

		return $tcaName;
	}

	protected function getModelPid(): int
	{
		$modelPid = $this->io->ask(
			'Enter the model\'s PID',
			validator: $this->validatePageUid(...)
		);

		if (!$modelPid) {
			throw new InvalidSetupValueException('Invalid PID entered');
		}

		return $modelPid;
	}

	protected function getListPageUid(): int
	{
		$listPageUid = $this->io->ask(
			'Enter the list view page UID',
			validator: $this->validatePageUid(...)
		);

		if (!$listPageUid) {
			throw new InvalidSetupValueException('Invalid list view page UID entered');
		}

		return $listPageUid;
	}

	protected function getSinglePageUid(): int
	{
		$singlePageUid = $this->io->ask(
			'Enter the single view page UID',
			validator: $this->validatePageUid(...)
		);

		if (!$singlePageUid) {
			throw new InvalidSetupValueException('Invalid single view page UID entered');
		}

		return $singlePageUid;
	}

	protected function getModelName(string $tcaName): string
	{
		$tcaLabel = $GLOBALS['TCA'][$tcaName]['ctrl']['title'] ?? false;

		if (!$tcaLabel) {
			throw new InvalidSetupValueException('Could not determine model name');
		}

		return GeneralUtility::underscoredToUpperCamelCase(
			str_replace(' ', '_', $this->getLanguageService()->sL($tcaLabel))
		);
	}

	protected function validateTca(string $tcaName): string|false
	{
		return !empty($tcaName) && array_key_exists($tcaName, $GLOBALS['TCA'])
			? trim($tcaName)
			: false;
	}

	protected function validatePageUid(string $singlePageUid): int
	{
		return (int)$singlePageUid;
	}

	protected function processRouteEnhancers(): bool
	{
		$routeEnhancerFilePath = $this->getYamlPath() . '/' . $this->modelName . '.yaml';

		$success = !!file_put_contents(
			$routeEnhancerFilePath,
			Yaml::dump(
				$this->getRouteEnhancerConfiguration(),
				99,
				2,
				Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_OBJECT_AS_MAP
			),
			FILE_APPEND
		);

		if ($success) {
			$this->io->success('RouteEnhancer configuration created');
		} else {
			$this->io->error('There was a problem creating the RouteEnhancer configuration');
		}

		return $success;
	}

	protected function getRouteEnhancerConfiguration(): array
	{
		return [
			'routeEnhancers' => [
				"{$this->modelName}Single" => [
					'type' => 'Extbase',
					'limitToPages' => [
						$this->singlePageUid,
					],
					'extension' => static::EXTENSION_KEY,
					'plugin' => static::PLUGIN_NAME,
					'routes' => [
						[
							'routePath' => '/{record}',
							'_controller' => static::SINGLE_CONTROLLER,
						],
					],
					'defaultController' => static::SINGLE_CONTROLLER,
					'aspects' => [
						'record' => [
							'type' => 'PersistedAliasMapper',
							'tableName' => $this->tcaName,
							'routeFieldName' => 'slug',
						],
					],
				],
				"{$this->modelName}List" => [
					'type' => 'Extbase',
					'limitToPages' => [
						$this->listPageUid,
					],
					'extension' => static::EXTENSION_KEY,
					'plugin' => static::PLUGIN_NAME,
					'routes' => [
						[
							'routePath' => '/',
							'_controller' => static::LIST_CONTROLLER,
						],
						[
							'routePath' => '/page-{page}',
							'_controller' => static::LIST_CONTROLLER,
							'_arguments' => [
								'page' => 'currentPage',
							],
						],
					],
					'defaultController' => static::LIST_CONTROLLER,
					'defaults' => [
						'page' => 1,
					],
					'aspects' => [
						'page' => [
							'type' => 'StaticRangeMapper',
							'start' => '2',
							'end' => '1000',
						],
					],
				],
			],
		];
	}

	protected function processSitemapConfiguration(): bool
	{
		$typoScriptFilePath = $this->getTypoScriptPath('modules') . '/sitemap.typoscript';

		$success = !!file_put_contents($typoScriptFilePath, $this->getSitemapConfiguration(), FILE_APPEND);

		if ($success) {
			$this->io->success('Sitemap configuration created');
		} else {
			$this->io->error('There was a problem creating the Sitemap configuration');
		}

		return $success;
	}

	protected function getSitemapConfiguration(): string
	{
		$extensionKey = strtolower(static::EXTENSION_KEY);
		$pluginName = strtolower(static::PLUGIN_NAME);

		$controller = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[0] ?? '';
		$action = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[1] ?? '';

		return <<<TYPOSCRIPT

			plugin.tx_seo.config.xmlSitemap.sitemaps {
				{$this->modelName} {
					provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
					config {
						table = {$this->tcaName}
						sortField = crdate
						lastModifiedField = tstamp
						pid = {$this->modelPid}
						url {
							pageId = {$this->singlePageUid}
							fieldToParameterMap {
								uid = tx_{$extensionKey}_{$pluginName}[record]
							}
							additionalGetParameters {
								tx_{$extensionKey}_{$pluginName}.action = {$action}
								tx_{$extensionKey}_{$pluginName}.controller = {$controller}
							}
						}
					}
				}
			}

		TYPOSCRIPT;
	}

	protected function processLinkHandler(): bool
	{
		return $this->processLinkHandlerTsConfig() && $this->processLinkHandlerTypoScript();
	}

	protected function processLinkHandlerTsConfig(): bool
	{
		$modelName = $this->getLanguageService()->sL($GLOBALS['TCA'][$this->tcaName]['ctrl']['title'] ?? '');
		$tsConfigFilePath = $this->getTypoScriptPath('..') . '/page.tsconfig';

		$success = !!file_put_contents($tsConfigFilePath, $this->getLinkHandlerTsConfig($modelName), FILE_APPEND);

		if ($success) {
			$this->io->success('LinkHandler TSConfig created');
		} else {
			$this->io->error('There was a problem creating the LinkHandler TSConfig');
		}

		return $success;
	}

	protected function getLinkHandlerTsConfig(string $modelName): string
	{
		$extensionKey = strtolower(static::EXTENSION_KEY);
		$pluginName = strtolower(static::PLUGIN_NAME);

		$controller = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[0] ?? '';
		$action = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[1] ?? '';

		return <<<TSCONFIG

			TCEMAIN.linkHandler {
				{$this->tcaName} {
					handler = TYPO3\CMS\Backend\LinkHandler\RecordLinkHandler
					label = {$modelName}
					configuration {
						table = {$this->tcaName}
						storagePid = {$this->modelPid}
						hidePageTree = 1
					}
					scanAfter = page
				}
			}

			TCEMAIN.preview {
				{$this->tcaName} {
					previewPageId = {$this->singlePageUid}
					fieldToParameterMap {
						uid = tx_{$extensionKey}_{$pluginName}[record]
					}
					additionalGetParameters {
						tx_{$extensionKey}_{$pluginName}.controller = {$controller}
						tx_{$extensionKey}_{$pluginName}.action = {$action}
					}
				}
			}

			[traverse(page, "uid") == {$this->modelPid}]
				mod.web_list {
					allowedNewTables = {$this->tcaName}
					hideTables := addToList(tt_content)
				}
			[global]

		TSCONFIG;
	}

	protected function processLinkHandlerTypoScript(): bool
	{
		$typoScriptFilePath = $this->getTypoScriptPath('modules') . '/' . GeneralUtility::camelCaseToLowerCaseUnderscored($this->modelName) . '.typoscript';

		$success = !!file_put_contents($typoScriptFilePath, $this->getLinkHandlerTypoScript(), FILE_APPEND);

		if ($success) {
			$this->io->success('LinkHandler TypoScript created');
		} else {
			$this->io->error('There was a problem creating the LinkHandler TypoScript');
		}

		return $success;
	}

	protected function getLinkHandlerTypoScript(): string
	{
		$extensionKey = strtolower(static::EXTENSION_KEY);
		$pluginName = strtolower(static::PLUGIN_NAME);

		$controller = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[0] ?? '';
		$action = GeneralUtility::trimExplode('::', static::SINGLE_CONTROLLER)[1] ?? '';

		return <<<TYPOSCRIPT

			config.recordLinks.{$this->tcaName} {
				forceLink = 0
				typolink {
					parameter = {$this->singlePageUid}
					additionalParams.data = field:uid
					additionalParams.wrap = &tx_{$extensionKey}_{$pluginName}[record]=|&tx_{$extensionKey}_{$pluginName}[controller]={$controller}&tx_{$extensionKey}_{$pluginName}[action]={$action}
				}
			}

		TYPOSCRIPT;
		;
	}

	protected function getTypoScriptPath(string $subfolder): string
	{
		if (is_dir($this->sitePackagePath . '/Configuration/Sets')) {
			// TypoScript is in a site set
			$extensionSetFolder = GeneralUtility::underscoredToUpperCamelCase(basename($this->sitePackagePath));
			$typoScriptPath = $this->sitePackagePath . '/Configuration/Sets/' . $extensionSetFolder . '/TypoScript/' . $subfolder;
		} else {
			// TypoScript is in extension's Configuration folder
			$typoScriptPath = $this->sitePackagePath . '/Configuration/TypoScript/' . $subfolder;
		}

		if (!is_dir($typoScriptPath)) {
			mkdir(
				$typoScriptPath,
				recursive: true
			);
		}

		return realpath(rtrim($typoScriptPath, '/'));
	}

	protected function getYamlPath(): string
	{
		$yamlPath = $this->sitePackagePath . '/Configuration/Sites';

		if (!is_dir($yamlPath)) {
			mkdir(
				$yamlPath,
				recursive: true
			);
		}

		return realpath(rtrim($yamlPath, '/'));
	}

	protected function getLanguageService(): LanguageService
	{
		return $GLOBALS['LANG'];
	}
}
