.. include:: ../Includes.rst.txt

=========
Templates
=========

The Anthology extension uses the Fluid templating engine to render its output. This allows for full control over the HTML markup. You can customise every aspect of the plugin's appearance by overriding the default templates.

Default Template Structure
==========================

The extension's templates are located in its :file:`Resources/Private/` directory:

**Templates/**
   Contains the main template files for each action (e.g., :file:`List.html`, :file:`View.html`).

**Partials/**
   Contains reusable snippets of code that are used in the main templates. This is where the most common customisations are made.

   - :file:`List/Record.html`: Renders a single record in the list view.
   - :file:`List/Pagination.html`: Renders the pagination widget.
   - :file:`List/Filters.html`: Renders the container for the filters.
   - :file:`Filter/...`: Contains the templates for the different filter types (e.g., :file:`Date.html`, :file:`Search.html`).

**Layouts/**
   Defines the overall HTML structure of the templates.

Overriding Templates
====================

There are two main ways to override the default templates.

Automatic Overrides (Recommended)
----------------------------------

A powerful feature of the Anthology extension is its ability to automatically detect and use templates from the extension that provides the data.

As described in the ":doc:`Models`" guide, you configure the Anthology plugin to use a repository from one of your own extensions. When you do this, the Anthology plugin will automatically add your extension's :file:`Resources/Private/` directories to its template paths.

**This means you can simply copy a template file from the Anthology extension to your own extension, modify it, and it will be used automatically.**

For example, to customise the display of a single record in the list view:

1. Copy the file from :file:`ll_anthology/Resources/Private/Partials/List/Record.html`.
2. Paste it into the corresponding directory in your own extension: :file:`my_extension/Resources/Private/Partials/List/Record.html`.
3. Clear the cache.
4. Modify the copied file to your liking.

This is the recommended approach as it keeps the templates for your model logically grouped with the model itself.

Manual TypoScript Overrides
---------------------------

You can also explicitly tell the Anthology plugin where to find your templates using TypoScript:

.. code-block:: typoscript

	plugin.tx_llanthology {
		settings {
			view {
				templateRootPaths.123456789 = EXT:my_site_package/Resources/Private/Templates/
				partialRootPaths.123456789 = EXT:my_site_package/Resources/Private/Partials/
				layoutRootPaths.123456789 = EXT:my_site_package/Resources/Private/Layouts/
			}
		}
	}

Available Variables
===================

Inside the templates, you have access to several variables:

**paginator**
   A paginator object that contains the records for the current page (:samp:`paginator.paginatedItems`).

**pagination**
   The pagination object for building the page links.

**filters**
   A list of the configured filter objects.

**record**
   In the :samp:`singleAction` view and the :file:`List/Record.html` partial, this variable holds the current record being displayed.

**settings**
   The settings array from the plugin's FlexForm.
