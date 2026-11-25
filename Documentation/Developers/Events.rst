.. include:: ../Includes.rst.txt

=============
PSR-14 Events
=============

Several events are dispatched in order to allow for modifications to the behaviour of the Anthology extension, records, filters, returned data, and views. These can be listened for by creating a `Typo3 Event Listener class <https://docs.typo3.org/permalink/t3coreapi:extension-development-event-listener>`_.

Available Events
================

All events are in the namespace `LiquidLight\\Anthology\\Events`:

- :ref:`BeforeGetRecordsEvent`
- :ref:`BeforeAnthologyListViewRenderEvent`
- :ref:`BeforeAnthologySingleViewRenderEvent`
- :ref:`BeforeAnthologyViewRenderEventInterface`
- :ref:`GetAnthologyRegistryEvent`

.. _BeforeGetRecordsEvent:
`BeforeGetRecordsEvent`
-----------------------

Arguments
^^^^^^^^^

- `RepositoryInterface $repository`
- `QueryInterface $query`
- `array &$constraints`
- `readonly string $constraintModeMethod`
- `ViewInterface $view`
- `RequestInterface $request`
- `readonly array $settings`

This event is the first to be dispatched by the list view, and is the most powerful of those provided. It is dispatched before any queries are made to the database, and can be used to override almost all of the subsequent behaviours of the extension, including modifying the query itself.

.. _BeforeAnthologyListViewRenderEvent:
`BeforeAnthologyListViewRenderEvent`
------------------------------------

Arguments
^^^^^^^^^

- `array $settings`
- `ViewInterface $view`
- `RequestInterface $request`

Called immediately before the HTML response is returned from `AnthologyController` when in list view.

.. _BeforeAnthologySingleViewRenderEvent:
`BeforeAnthologySingleViewRenderEvent`
--------------------------------------

Arguments
^^^^^^^^^

- `AbstractEntity $record`
- `array $settings`
- `ViewInterface $view`
- `RequestInterface $request`

Called immediately before the HTML response is returned from `AnthologyController` when in single view, and contains the record which will be displayed.

.. _BeforeAnthologyViewRenderEventInterface:
`BeforeAnthologyViewRenderEventInterface`
--------------------------------------

Both :ref:`BeforeAnthologyListViewRenderEvent` and  :ref:`BeforeAnthologySingleViewRenderEvent` implement the `BeforeAnthologyViewRenderEventInterface`, which can also be listened for as an event in itself. This will ensure that a listener will be triggered before the rendering of both list and single views.

.. _GetAnthologyRegistryEvent:
`GetAnthologyRegistryEvent`
---------------------------

Arguments
^^^^^^^^^

- `array &$registry`
- `readonly string $registryName`

Dispatched each time a registry's contents are read via `get()`, after duplicates and empty values have been removed. `$registry` is passed by reference and holds the class names collected from the `AsAnthologyFilter`, `AsAnthologyPreFilter` and `AsAnthologyRepository` attributes, so listeners can remove or reorder entries, for example to drop a filter provided by another extension or to control the order in which filters are built. Whatever the listeners leave in `$registry` is returned as-is (no further de-duplication).

`$registryName` is the fully qualified class name of the registry being read (`AnthologyFilterRegistry`, `AnthologyPreFilterRegistry` or `AnthologyRepositoryRegistry`), allowing a listener to target a single registry.

.. code-block:: php

	use LiquidLight\Anthology\Events\GetAnthologyRegistryEvent;
	use LiquidLight\Anthology\Registry\AnthologyFilterRegistry;
	use TYPO3\CMS\Core\Attribute\AsEventListener;

	#[AsEventListener]
	final class TidyFilterRegistryListener
	{
		public function __invoke(GetAnthologyRegistryEvent $event): void
		{
			if ($event->registryName !== AnthologyFilterRegistry::class) {
				return;
			}

			// Remove a filter that is not wanted on this site
			$event->registry = array_filter(
				$event->registry,
				static fn (string $class): bool => $class !== UnwantedFilter::class
			);

			// Move a filter to the front (usort also re-indexes the array)
			usort(
				$event->registry,
				static fn (string $a, string $b): int => ($b === PriorityFilter::class) <=> ($a === PriorityFilter::class)
			);
		}
	}
