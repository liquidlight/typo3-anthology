.. include:: ../Includes.rst.txt

=======
Sitemap
=======

To ensure that search engines can discover and index all the detail pages of your anthology records, it is important to include them in your website's XML sitemap.

This guide will show you how to configure a sitemap provider for the records displayed by the Anthology extension.

The Sitemap Provider
====================

A sitemap provider is responsible for collecting a list of URLs for a specific type of content and adding them to the XML sitemap. TYPO3 provides a generic :php:`RecordsXmlSitemapDataProvider` that can be configured to work with any database table.

TypoScript Configuration
=========================

.. code-block:: typoscript

	plugin.tx_seo.config.xmlSitemap.sitemaps {
		[model_identifier] {
			provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
			config {
				table = tx_myextension_domain_model_item
				sortField = crdate
				lastModifiedField = tstamp
				pid = 123
				url {
					pageId = 456
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

The :samp:`[model_identifier]` must be replaced with a unique value; this can be anything so long as it is unique. The :samp:`table`, :samp:`pid` and :samp:`pageId` values must be replaced with the relevant values.

Configuration Breakdown
------------------------

:samp:`[model_identifier]`
   A unique key for your sitemap provider. This can be any descriptive name (e.g., :samp:`my_items`, :samp:`news_articles`, :samp:`products`).

:samp:`provider`
   Uses the standard :php:`RecordsXmlSitemapDataProvider` from the TYPO3 core SEO extension.

:samp:`config`
   Contains the specific settings for the provider:

   - **table**: The name of the table your anthology is displaying (must match your repository configuration).
   - **sortField**: How the records should be sorted in the sitemap. :samp:`crdate` (creation date) is a sensible default.
   - **lastModifiedField**: Field used to determine when the record was last modified. Usually :samp:`tstamp`.
   - **pid**: The UID of the storage page where your records are stored. The provider will only select records from this page.
   - **url**: Defines how to construct the URL for each record:
      - **pageId**: The UID of the page that will handle the single view (your detail page).
      - **fieldToParameterMap**: Maps database fields to URL parameters. Here, the :samp:`uid` field is mapped to the :samp:`tx_llanthology_anthologyview[record]` parameter.
      - **additionalGetParameters**: Additional parameters required for the Anthology plugin to work correctly.

Final Steps
===========

1. Add the TypoScript configuration above to your site's :file:`setup.typoscript` file.
2. Adjust the following values to match your specific setup:
   - :samp:`[model_identifier]`: Choose a unique name for your sitemap
   - :samp:`table`: Your model's database table name
   - :samp:`pid`: The UID of the page where your records are stored
   - :samp:`pageId`: The UID of your single view page
3. Clear all caches in the TYPO3 backend.
4. If you're using route enhancers (recommended), ensure your routing configuration is set up correctly as described in the :doc:`Routing` guide.

.. note::

   If you're using route enhancers for clean URLs, you may need to adjust the :samp:`fieldToParameterMap` and :samp:`additionalGetParameters` to match your routing configuration. The example above works with the default Anthology plugin parameters.

After these steps, TYPO3 will automatically include the URLs for all your published anthology records in the XML sitemap. You can verify this by accessing your sitemap at :samp:`https://www.your-site.com/sitemap.xml`.
