# ll_anthology Extension

A TYPO3 extension that provides a generic content display system for listing and viewing records from any configured repository with advanced filtering capabilities.

## Overview

The `ll_anthology` extension creates a flexible plugin system that can display content from any TYPO3 repository in both list and single view modes. It dynamically loads repositories based on configuration and provides pagination, advanced filtering, template customisation, and routing capabilities.

## Features

- **Dual Display Modes**: List view with pagination and single record view
- **Dynamic Repository Loading**: Configurable to work with any TYPO3 repository
- **Advanced Filtering System**: Search, category, and date filtering with multiple display modes
- **Flexible Template System**: Custom template paths and template name overrides
- **Pagination Support**: Configurable items per page and pagination links
- **SEO-Friendly**: Automatic page title generation from record data
- **Route Enhancement**: Built-in route enhancers for clean URLs

## Requirements

- TYPO3 CMS ^13.4
- PHP 8.2+
- Composer
- FluidTYPO3 VHS ^7.1

## Documentation

For detailed setup and configuration instructions, see the documentation:

- [Quick Start Guide](Documentation/00.%20Quick%20start.md) - Complete setup walkthrough
- [Enable a Model](Documentation/10.%20Enable%20a%20model.md) - Connecting your data models
- [Routing](Documentation/20.%20Routing.md) - URL configuration for clean URLs
- [Adding Sitemap Items](Documentation/30.%20Adding%20sitemap%20items.md) - SEO integration
- [Templates](Documentation/40.%20Templates.md) - Customising the output
- [Custom Filters](Documentation/50.%20Custom%20filters.md) - Creating custom filtering options
