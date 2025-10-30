<?php

declare(strict_types=1);

namespace LiquidLight\Anthology\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

#[AsCommand(
	name: 'anthology:setup',
	description: 'Setup Anthology configuration',
)]
class SetupCommand extends Command
{
	private SymfonyStyle $io;

	private string $sitePackagePath;

	private string $tcaName;

	private string $repositoryClass;

	private int $modelPid;

	private int $listPageUid;

	private int $singlePageUid;

	private string $modelName;

	protected function configure(): void
	{
		$this->addArgument('sitePackagePath', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->io = new SymfonyStyle($input, $output);

		$this->io->title('Anthology setup');

		// 0. Get site package path
		$sitePackagePath = realpath($input->getArgument('sitePackagePath'));

		if (!$sitePackagePath) {
			$this->io->error('Invalid path supplied');
			return Command::FAILURE;
		}

		// 1. Select TCA
		$tcaName = $this->io->ask(
			'Enter the TCA/table name (e.g. `tx_myextension_domain_model_item`)',
			validator: fn ($answer) => !empty($answer) && array_key_exists($answer, $GLOBALS['TCA']) ? $answer : false
		);

		if (!$tcaName) {
			$this->io->error('Invalid TCA entered');
			return Command::FAILURE;
		}

		// 2. Select corresponding Repository
		$repositoryClass = $this->io->ask(
			'Enter the fully qualified class name for this TCA\'s Repository (e.g. `Vendor\MyExtension\Domain\Repository\ItemRepository`)',
			validator: $this->validateRepository(...)
		);

		if (!$repositoryClass) {
			$this->io->error('Invalid repository entered');
			return Command::FAILURE;
		}

		// 3. Enter model PID
		$modelPid = $this->io->ask(
			'Enter the model\'s PID',
			validator: $this->validatePageUid(...)
		);

		if (!$modelPid) {
			$this->io->error('Invalid PID entered');
			return Command::FAILURE;
		}

		// 4. Enter list page UID
		$listPageUid = $this->io->ask(
			'Enter the list view page UID',
			validator: $this->validatePageUid(...)
		);

		if (!$listPageUid) {
			$this->io->error('Invalid list view page UID entered');
			return Command::FAILURE;
		}

		// 5. Enter single page UID
		$singlePageUid = $this->io->ask(
			'Enter the single view page UID',
			validator: $this->validatePageUid(...)
		);

		if (!$singlePageUid) {
			$this->io->error('Invalid single view page UID entered');
			return Command::FAILURE;
		}

		// 6. Determine model name
		$modelName = $this->getModelName($tcaName);

		if (!$modelName) {
			$this->io->error('Could not determine model name');
			return Command::FAILURE;
		}

		// Assign options to properties
		$this->sitePackagePath = $sitePackagePath;
		$this->tcaName = $tcaName;
		$this->repositoryClass = $repositoryClass;
		$this->modelPid = $modelPid;
		$this->listPageUid = $listPageUid;
		$this->singlePageUid = $singlePageUid;
		$this->modelName = $modelName;

		// A. Add Anthology Typoscript
		$processTyposcript = $this->processTyposcript();

		// B. Create route enhancers
		$routeEnhancers = $this->processRouteEnhancers();

		// C. Add sitemap configuration
		$sitemapConfiguration = $this->processSitemapConfiguration();

		// D. Add link handler
		$linkHandler = $this->processLinkHandler();

		// Return success/failure
		return $processTyposcript && $routeEnhancers && $sitemapConfiguration && $linkHandler
			? Command::SUCCESS
			: Command::FAILURE;
	}

	private function validateTca(string $tcaName): string|false
	{
		return !empty($tcaName) && array_key_exists($tcaName, $GLOBALS['TCA'])
			? trim($tcaName)
			: false;
	}

	private function validateRepository(string $repositoryClass): string|false
	{
		return class_exists($repositoryClass)
			&& is_subclass_of($repositoryClass, Repository::class)
			? trim($repositoryClass, '\\\n\r\t\v\x00')
			: false;
	}

	private function validatePageUid(string $singlePageUid): int
	{
		return (int)$singlePageUid;
	}

	private function getModelName(string $tcaName): string|false
	{
		$tcaLabel = $GLOBALS['TCA'][$tcaName]['ctrl']['title'] ?? false;

		if (!$tcaLabel) {
			return false;
		}

		return GeneralUtility::underscoredToUpperCamelCase(
			str_replace(' ', '_', $this->getLanguageService()->sL($tcaLabel))
		);
	}

	private function processTyposcript(): bool
	{
		$typoScriptFilePath = $this->getTypoScriptPath('modules') . '/' . GeneralUtility::camelCaseToLowerCaseUnderscored($this->modelName) . '.typoscript';

		$typoScriptContents = <<<TYPOSCRIPT

			plugin.tx_llanthology {
				settings {
					repositories {
						{$this->tcaName} = {$this->repositoryClass}
					}
				}
			}

		TYPOSCRIPT;

		$success = !!file_put_contents($typoScriptFilePath, $typoScriptContents, FILE_APPEND);

		if ($success) {
			$this->io->success('TypoScript configuration created');
		} else {
			$this->io->error('There was a problem creating the TypoScript configuration');
		}

		return $success;
	}

	private function processRouteEnhancers(): bool
	{
		$routeEnhancerFilePath = $this->getYamlPath() . '/' . $this->modelName . '.yaml';

		$routeEnhancerConfiguration = [
			'routeEnhancers' => [
				"{$this->modelName}Single" => [
					'type' => 'Extbase',
					'limitToPages' => [
						$this->singlePageUid,
					],
					'extension' => 'LlAnthology',
					'plugin' => 'AnthologyView',
					'routes' => [
						[
							'routePath' => '/{record}',
							'_controller' => 'Anthology::single',
						],
					],
					'defaultController' => 'Anthology::view',
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
					'extension' => 'LlAnthology',
					'plugin' => 'AnthologyView',
					'routes' => [
						[
							'routePath' => '/',
							'_controller' => 'Anthology::list',
						],
						[
							'routePath' => '/page-{page}',
							'_controller' => 'Anthology::list',
							'_arguments' => [
								'page' => 'currentPage',
							],
						],
					],
					'defaultController' => 'Anthology::list',
					'defaults' => [
						'page' => 1,
					],
					'aspects' => [
						'page' => [
							'type' => 'StaticRangeMapper',
							'start' => 2,
							'end' => 1000,
						],
					],
				],
			],
		];

		$success = !!file_put_contents(
			$routeEnhancerFilePath,
			Yaml::dump(
				$routeEnhancerConfiguration,
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

	private function processSitemapConfiguration(): bool
	{
		$typoScriptFilePath = $this->getTypoScriptPath('modules') . '/sitemap.typoscript';

		$typoScriptContents = <<<TYPOSCRIPT

			plugin.tx_seo.config.xmlSitemap.sitemaps {
				vacancies {
					provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
					config {
						table = {$this->tcaName}
						sortField = crdate
						lastModifiedField = tstamp
						pid = {$this->modelPid}
						url {
							pageId = {$this->singlePageUid}
							fieldToParameterMap {
								uid = tx_llanthology_anthologyview[record]
							}
							additionalGetParameters {
								tx_llanthology_anthologyview.action = single
								tx_llanthology_anthologyview.controller = Anthology
							}
						}
					}
				}
			}

		TYPOSCRIPT;

		$success = !!file_put_contents($typoScriptFilePath, $typoScriptContents, FILE_APPEND);

		if ($success) {
			$this->io->success('Sitemap configuration created');
		} else {
			$this->io->error('There was a problem creating the Sitemap configuration');
		}

		return $success;
	}

	private function processLinkHandler(): bool
	{
		return $this->processLinkHandlerTsConfig() && $this->processLinkHandlerTypoScript();
	}

	private function processLinkHandlerTsConfig(): bool
	{
		$modelName = $this->getLanguageService()->sL($GLOBALS['TCA'][$this->tcaName]['ctrl']['title'] ?? '');
		$tsConfigFilePath = $this->getTypoScriptPath('..') . '/page.tsconfig';

		$tsConfigContents = <<<TSCONFIG

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

		TSCONFIG;

		$success = !!file_put_contents($tsConfigFilePath, $tsConfigContents, FILE_APPEND);

		if ($success) {
			$this->io->success('LinkHandler TSConfig created');
		} else {
			$this->io->error('There was a problem creating the LinkHandler TSConfig');
		}

		return $success;
	}

	private function processLinkHandlerTypoScript(): bool
	{
		$typoScriptFilePath = $this->getTypoScriptPath('modules') . '/' . GeneralUtility::camelCaseToLowerCaseUnderscored($this->modelName) . '.typoscript';

		$typoScriptContents = <<<TYPOSCRIPT

			config.recordLinks.{$this->tcaName} {
				forceLink = 0
				typolink {
					parameter = {$this->singlePageUid}
					additionalParams.data = field:uid
					additionalParams.wrap = &tx_llanthology_anthologyview[record]=|&tx_llanthology_anthologyview[controller]=Anthology&tx_llanthology_anthologyview[action]=single
				}
			}

		TYPOSCRIPT;

		$success = !!file_put_contents($typoScriptFilePath, $typoScriptContents, FILE_APPEND);

		if ($success) {
			$this->io->success('LinkHandler TypoScript created');
		} else {
			$this->io->error('There was a problem creating the LinkHandler TypoScript');
		}

		return $success;
	}

	private function getTypoScriptPath(string $subfolder): string
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

	private function getYamlPath(): string
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

	private function getLanguageService(): LanguageService
	{
		return $GLOBALS['LANG'];
	}
}
