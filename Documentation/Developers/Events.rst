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

- `ViewInterface $view`
- `RequestInterface $request`

Called immediately before the HTML response is returned from `AnthologyController` when in list view.

.. _BeforeAnthologySingleViewRenderEvent:
`BeforeAnthologySingleViewRenderEvent`
--------------------------------------

Arguments
^^^^^^^^^

- `AbstractEntity $record`
- `ViewInterface $view`
- `RequestInterface $request`

Called immediately before the HTML response is returned from `AnthologyController` when in single view, and contains the record which will be displayed.

.. _BeforeAnthologyViewRenderEventInterface:
`BeforeAnthologyViewRenderEventInterface`
--------------------------------------

Both :ref:`BeforeAnthologyListViewRenderEvent` and  :ref:`BeforeAnthologySingleViewRenderEvent` implement the `BeforeAnthologyViewRenderEventInterface`, which can also be listened for as an event in itself. This will ensure that a listener will be triggered before the rendering of both list and single views.
