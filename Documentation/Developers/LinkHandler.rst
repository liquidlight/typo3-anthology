.. include:: ../Includes.rst.txt

============
Link Handler
============

Link handlers in TYPO3 allow editors to create links to specific records directly from the link browser in the backend. This is particularly useful when you want to link to individual records that are displayed through the Anthology extension.

Overview
========

When you configure a link handler for your model, editors can:

- Select records directly from the link browser
- Create links that automatically route to the single view of a record
- Maintain links even if the record moves or changes

The link handler consists of two parts:

1. **Backend Configuration** (:file:`page.tsconfig`) - Enables the link browser tab
2. **Frontend Configuration** (:file:`setup.typoscript`) - Defines how links are rendered

Backend Configuration
=====================

Add this configuration to your :file:`page.tsconfig` to enable the link browser for your model:

.. code-block:: typoscript

	TCEMAIN.linkHandler {
		tx_myextension_domain_model_item {
			handler = TYPO3\CMS\Backend\LinkHandler\RecordLinkHandler
			label = Model name
			configuration {
				table = tx_myextension_domain_model_item
				storagePid = 123
				hidePageTree = 1
			}
			scanAfter = page
		}
	}

Configuration Options
----------------------

``handler``
	The PHP class handling the link browser functionality. **Required**

``label``
	Display name in the link browser tab. **Required**

``table``
	Database table name of your model. **Required**

``storagePid``
	Page ID where records are stored (0 for all pages).

``hidePageTree``
	Hide the page tree in link browser (1 = hide, 0 = show).

``scanAfter``
	Position of the tab in link browser.

Frontend Configuration
======================

Configure how the links are rendered in the frontend by adding this to your :file:`setup.typoscript`:

.. code-block:: typoscript

	config.recordLinks.tx_myextension_domain_model_item {
		forceLink = 0
		typolink {
			parameter = 123
			additionalParams.data = field:uid
			additionalParams.wrap = &tx_llanthology_anthologyview[record]=|&tx_llanthology_anthologyview[controller]=Anthology&tx_llanthology_anthologyview[action]=single
		}
	}

Configuration Options
----------------------

``forceLink``
	Force link generation even if target page doesn't exist. Default: ``0``

``parameter``
	Target page ID where Anthology plugin is configured. **Required**

``additionalParams.data``
	Data source for the record identifier. Default: ``field:uid``

``additionalParams.wrap``
	URL parameters to append. See example above.

Usage in Backend
================

Once configured, editors can:

1. Open the link browser in any RTE field
2. Click on the "Products" tab (or your configured label)
3. Browse and select records from the configured storage page
4. The link will automatically point to the single view

Troubleshooting
===============

Link Handler Tab Not Appearing
-------------------------------

- Check that :file:`page.tsconfig` is properly loaded
- Verify the table name matches your model exactly
- Ensure the storage PID exists and contains records

Links Not Working
------------------

- Verify the target page ID in :samp:`parameter` has the Anthology plugin
- Check that the repository is configured in :samp:`plugin.tx_llanthology.settings.repositories`
- Ensure routing is properly configured for clean URLs

No Records Showing
-------------------

- Check the :samp:`storagePid` configuration
- Verify records exist on the specified page
- Use :samp:`storagePid = 0` to search all pages

Advanced Configuration
======================

Multiple Storage Pages
----------------------

.. code-block:: typoscript

	TCEMAIN.linkHandler {
		tx_myextension_domain_model_item {
			handler = TYPO3\CMS\Backend\LinkHandler\RecordLinkHandler
			label = Items
			configuration {
				table = tx_myextension_domain_model_item
				storagePid = 10,20,30
				hidePageTree = 1
			}
			scanAfter = page
		}
	}
