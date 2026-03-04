.. include:: ../Includes.rst.txt

============
Introduction
============

ll_anthology Extension
======================

A TYPO3 extension that provides a generic content display system for listing and viewing records from any configured repository with advanced filtering capabilities.

Overview
--------

The :php:`ll_anthology` extension creates a flexible plugin system that can display content from any TYPO3 repository in both list and single view modes. It dynamically loads repositories based on configuration and provides pagination, advanced filtering, template customisation, and routing capabilities.

Features
--------

- **Dual Display Modes**: List view with pagination and single record view
- **Dynamic Repository Loading**: Configurable to work with any TYPO3 repository
- **Advanced Filtering System**: Search, category, and date filtering with multiple display modes
- **Flexible Template System**: Custom template paths and template name overrides
- **Pagination Support**: Configurable items per page and pagination links
- **SEO-Friendly**: Automatic page title generation from record data
- **Route Enhancement**: Built-in route enhancers for clean URLs

Requirements
------------

- TYPO3 CMS ^13.4
- PHP 8.2+
- Composer
- FluidTYPO3 VHS ^7.1
