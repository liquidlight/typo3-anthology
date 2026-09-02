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

Model specific overrides
------------------------

To provide templates to the Anthology plugin for specific models, use the following TypoScript:

.. code-block:: typoscript

	plugin.tx_llanthology {
		settings {
			view {
				modelname {
    				templateRootPaths.123456789 = EXT:my_site_package/Resources/Private/Templates/ModelName/
    				partialRootPaths.123456789 = EXT:my_site_package/Resources/Private/Partials/ModelName/
    				layoutRootPaths.123456789 = EXT:my_site_package/Resources/Private/Layouts/ModelName/
				}
			}
		}
	}

.. note::

   The `plugin.tx_llanthology.settings.view.modelname` key must be lowercase and match the model's name, i.e. `Domain\\Model\\BlogPost` -> `plugin.tx_llanthology.settings.view.blogpost`

Extension-wide overrides
------------------------

Alternatively, you can provide template overrides for Anthology as a whole:

.. code-block:: typoscript

	plugin.tx_llanthology {
		settings {
			view {
  				templateRootPaths.123456789 = EXT:my_site_package/Resources/Private/Templates/ModelName/
  				partialRootPaths.123456789 = EXT:my_site_package/Resources/Private/Partials/ModelName/
  				layoutRootPaths.123456789 = EXT:my_site_package/Resources/Private/Layouts/ModelName/
			}
		}
	}

Ensure that the key for each of the entries for model specific, or extension-wide, overrides are unique to avoid clashes.

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
