.. include:: ../Includes.rst.txt

=====================
External Repositories
=====================

If you wish to display records from a repository which is not part of your project, or is in a dependency which does not natively support Anthology, you can do so by creating a custom model and repository which extend the original.

The following examples assume you wish to display records created by :composer:`friendsoftypo3/tt-address`, but the same principles apply to any repository.

Model
=====

Create a new model which extends the original. Unless required, you don't need to add any additional properties or methods to this model.

**Classes/Domain/Model/Address.php**

.. code-block:: php

	<?php

	declare(strict_types=1);

	namespace Vendor\MyExtension\Domain\Model;

	use FriendsOfTYPO3\TtAddress\Domain\Model\Address as AddressOriginal;

	class Address extends AddressOriginal
	{
	}

Repostory
=========

As a pair to the model created above, you also need to create a repository which extends the original, and add the :php:`AsAnthologyRepository` attribute:

**Classes/Domain/Repository/AddressRepository.php**

.. code-block:: php

	<?php

	declare(strict_types=1);

	namespace Vendor\MyExtension\Domain\Repository;

	use FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository as AddressRepositoryOriginal;
	use LiquidLight\Anthology\Attribute\AsAnthologyRepository;

	#[AsAnthologyRepository('tt_address')]
	class AddressRepository extends AddressRepositoryOriginal
	{
	}

Configuration
=============

Finally, you must configure TYPO3 to use your newly created model and repository with the original table.

**Configuration/Extbase/Persistence/Classes.php**

.. code-block:: php

	<?php

	use Vendor\MyExtension\Domain\Model\Address;

	return [
		Address::class => [
			'tableName' => 'tt_address',
		],
	];

After completing the steps above and clearing the cache, your new model will be available for selection in the Anthology plugin.
