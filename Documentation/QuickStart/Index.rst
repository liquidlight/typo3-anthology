.. include:: ../Includes.rst.txt

===========
Quick Start
===========

This guide provides a comprehensive overview for getting the Anthology extension running, assuming you have an existing TYPO3 model you wish to display.

Installation
============

Install the extension via Composer:

.. code-block:: bash

	composer require liquidlight/typo3-anthology

Add Anthology to your site set as a dependency by either selecting it in the Sites module or adding the following to your site config YAML:

.. code-block:: yaml

	dependencies:
	  - liquidlight/anthology

If you're not using site sets, you can include the TypoScript in your :file:`setup.typoscript` file:

.. code-block:: typoscript

	@import 'EXT:ll_anthology/Configuration/TypoScript/setup'

Automated Setup
===============

The quickest and easiest way to get started with Anthology is using the :command:`anthology:setup` command:

Run the Command
---------------

.. code-block:: bash

	./vendor/bin/typo3 anthology:setup [site_package_path]

Replace :samp:`[site_package_path]` with the path to either your site package, or the extension you are modifying.

Once the command has completed, it is recommended you manually review the generated configuration for accuracy before proceeding.

Add the Generated Configuration
--------------------------------

If it is not already configured, you must include the generated :file:`sitemap.typoscript` and :file:`[model_name].typoscript` files in your site package or extension's TypoScript file:

.. code-block:: typoscript

	@import './TypoScript/modules'

And also add the generated YAML configuration to your site configuration YAML:

.. code-block:: yaml

	imports:
	  - resource: 'EXT:site_package/Configuration/Sites/[ModelName].yaml'

Manual Setup
============

Repository Configuration
--------------------------

For the extension to function, you must add the :php:`AsAnthologyRepository` attribute to your model's repository.

.. code-block:: php

	use LiquidLight\Anthology\Attribute\AsAnthologyRepository;
	use TYPO3\CMS\Extbase\Persistence\Repository;

	#[AsAnthologyRepository('tx_myextension_domain_model_item')]
	class ItemRepository extends Repository {
		...
	}

-  :php:`AsAnthologyRepository`'s :php:`tableName` argument **must** be the name of your database table and TCA.

Add and Configure the Plugin
-----------------------------

1. Navigate to the **Page** module and add the **Anthology** content element from the **Plugins** tab.
2. Configure the plugin settings:

	**General Tab**
		- **Mode**: Choose :samp:`List` for the main view or :samp:`Single` for a detail page.
		- **Model name**: Select your model (e.g., "My Extension Items"). This list is populated based on your TypoScript configuration.
		- **Single View Page**: For a :samp:`List` plugin, link to the page where the :samp:`Single` view is located.

	**Display Tab**
		- **Items Per Page**: Set the number of items for pagination (e.g., :samp:`10`).
		- **Enable Pagination**: Activate or deactivate the pagination.

	**Filters Tab**
		- Assign pre-configured filter records to the plugin.

Routing (for Detail Pages)
---------------------------

To create user-friendly URLs for your detail pages, add a route enhancer to your site's :file:`config.yaml`:

.. code-block:: yaml

	routeEnhancers:
	  # Single view with slug-based URLs
	  ItemSingle:
	  type: Extbase
	  limitToPages:
	    - 123  # Your single view page UID
	  extension: LlAnthology
	  plugin: AnthologyView
	  routes:
	    # Single record by slug
	    - routePath: '/{record}'
	    _controller: 'Anthology::single'
	  defaultController: 'Anthology::view'
	  aspects:
	    record:
	    type: PersistedAliasMapper
	    tableName: tx_myextension_domain_model_item
	    routeFieldName: slug

This requires a :samp:`slug` field in your model's table.

For further information, and to add pagination route enhancers to the list view, refer to the :doc:`../Developers/Routing` documentation.

Sitemap
-------

To include your detail pages in the XML sitemap, add a sitemap provider to your TypoScript:

.. code-block:: typoscript

	plugin.tx_seo.config.xmlSitemap.sitemaps {
		my_items {
			provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
			config {
				table = tx_myextension_domain_model_item
				sortField = crdate
				lastModifiedField = tstamp
				pid = 123  # Storage page UID
				url {
					pageId = 456  # Single view page UID
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

See the :doc:`../Developers/Sitemap` guide for more details.

Templates
---------

The extension will automatically use templates from your model's extension directory. To override a template, copy it from :file:`ll_anthology/Resources/Private/` to the corresponding path in your own extension (e.g., :file:`my_extension/Resources/Private/Partials/List/Record.html`) and modify it.

.. seealso::
	For more detailed information on template customization, see the :doc:`../Developers/Templates` guide.
