.. include:: ../Includes.rst.txt

======
Models
======

The Anthology extension is designed to be a generic tool for displaying records from any database table. To achieve this, you must explicitly tell the extension which model you want it to work with. The :doc:`../QuickStart/Index` guide provides a brief overview, whilst this guide explains the concepts in more detail.

Making a model available involves three key parts: the TCA, the Extbase Repository, and the Extbase Model.

The TCA File
============

At a minimum, you need a :samp:`ctrl` section in your `TCA file <https://docs.typo3.org/permalink/t3tca:start>`_ with a :samp:`title`. This title is what will appear in the plugin's "Model name" selection box.

Here is a minimal example for a hypothetical table named :samp:`tx_myextension_domain_model_item`:

**Configuration/TCA/tx_myextension_domain_model_item.php**

.. code-block:: php

	<?php

	return [
		'ctrl' => [
			'title' => 'My Extension Items',
			'label' => 'title',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			// ... other necessary ctrl properties
			'iconfile' => 'EXT:my_extension/Resources/Public/Icons/Item.svg',
		],
		// ... rest of your TCA ...
	];

The Repository
==============

To fetch the data from your table, the Anthology extension needs a corresponding `Extbase repository <https://docs.typo3.org/permalink/t3coreapi:extbase-repository-api>`_. This repository **must** have the :php:`LiquidLight\\Anthology\\Attribute\\AsAnthologyRepository` attribute with the corresponding TCA/table name argument.

**Classes/Domain/Repository/ItemRepository.php**

.. code-block:: php

	<?php

	declare(strict_types=1);

	namespace Vendor\MyExtension\Domain\Repository;

	use LiquidLight\Anthology\Attribute\AsAnthologyRepository;
	use TYPO3\CMS\Extbase\Persistence\Repository;

	#[AsAnthologyRepository('tx_myextension_domain_model_item')]
	class ItemRepository extends Repository
	{
		...
	}

The Model
=========

The model should be a standard `Extbase domain model <https://docs.typo3.org/permalink/t3coreapi:extbase-model>`_, no additional configuration is required to use a model in Anthology

**Classes/Domain/Model/Item.php**

.. code-block:: php

	<?php

	declare(strict_types=1);

	namespace Vendor\MyExtension\Domain\Model;

	use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

	class Item extends AbstractEntity
	{
		...
	}

Select the Model in the Plugin
==============================

After completing the steps above and clearing the cache, your new model will be available for selection in the Anthology plugin.

1. Edit the Anthology content element on your page.
2. Navigate to the **General** tab.
3. Click on the **Model name** dropdown menu.
4. You should now see "My Extension Items" (the :samp:`title` from your TCA file) as an option.

Once you select it and save the content element, the plugin will start to query and display the records from your :samp:`tx_myextension_domain_model_item` table.
