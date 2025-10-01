# ll_anthology Extension

A TYPO3 extension that provides a generic content display system for listing and viewing records from any configured repository.

## Overview

The `ll_anthology` extension creates a flexible plugin system that can display content from any TYPO3 repository in both list and single view modes. It dynamically loads repositories based on configuration and provides pagination, template customization, and routing capabilities.

## Features

- **Dual Display Modes**: List view with pagination and single record view
- **Dynamic Repository Loading**: Configurable to work with any TYPO3 repository
- **Flexible Template System**: Custom template paths and template name overrides
- **Pagination Support**: Configurable items per page and pagination links
- **SEO-Friendly**: Automatic page title generation from record data
- **Route Enhancement**: Built-in route enhancers for clean URLs

## Installation

### Requirements

- TYPO3 CMS ^13.4
- PHP 8.1+
- Composer

### Installation Steps

1. Install via Composer:
   ```bash
   composer require liquidlight/typo3-anthology
   ```

2. Include the TypoScript configuration set in your site configuration or manually import the setup:
   ```typoscript
   @import 'EXT:ll_anthology/Configuration/TypoScript/setup'
   ```

3. Include the list view pagination configuration in the site's configuration YAML:
   ```yaml
   imports:
     -
       resource: 'EXT:ll_anthology/Configuration/Sites/AnthologyList.yaml'
   ```

### Implementation Scenarios

#### Custom Models

For projects requiring new domain models:

1. Create TCA configuration for your custom table
2. Implement corresponding Extbase model and repository classes
3. Configure repository mapping in TypoScript
4. Create templates in your extension's `Resources/Private/Templates/` directory

#### External Models

For projects using existing domain models from other extensions:

1. Ensure the source extension is installed and configured
2. Add repository mapping to TypoScript configuration
3. Create custom templates if the default templates are insufficient
4. Configure route enhancers for clean URLs if required

## Configuration

### TypoScript Setup

The extension requires TypoScript configuration to define available repositories:

```typoscript
plugin.tx_llanthology {
    settings {
        repositories {
            [table_name] = [Repository\Class\Name]
        }
    }
}
```

### Plugin Configuration

The plugin can be configured through FlexForms with the following options:

#### General Tab

- **Mode**: Choose between "list" or "single" view mode
- **TCA**: Select the repository/table to display content from
- **Single View Page**: Link to detail page (list mode only)
- **List View Page**: Link back to list page (single mode only)

#### Display Tab

- **Items Per Page**: Number of items to display per page (list mode)
- **Enable Pagination**: Toggle pagination on/off
- **Maximum Pagination Links**: Number of pagination links to show
- **Template Name**: Override default template name

## Usage

### List View Mode

Displays a paginated list of records from the selected repository. Supports:
- Configurable items per page
- Sliding window pagination
- Custom template overrides
- Filtering (TODO: implement)

### Single View Mode

Displays individual record details. Features:
- Automatic page title generation
- 404 handling for missing records
- Template path resolution from repository extension

## Architecture

### Core Classes

#### AnthologyController

Main controller handling view routing and data preparation:
- `viewAction()`: Routes to appropriate display mode
- `listAction()`: Handles paginated list display
- `singleAction()`: Handles single record display

#### RepositoryFactory

Factory class for instantiating repository instances:
- Validates repository classes
- Creates repository instances via GeneralUtility

#### PageTitleProvider

Generates page titles from record data:
- Uses TCA label configuration
- Supports alternative labels and label forcing
- Handles complex label combinations

#### PluginConfigurationHook

Populates FlexForm options:
- Reads TypoScript repository configuration
- Filters available TCA tables
- Provides repository selection options

### Template Resolution

The extension automatically resolves template paths from the repository's extension:
- Layouts: `[Extension]/Resources/Private/Layouts/`
- Templates: `[Extension]/Resources/Private/Templates/`
- Partials: `[Extension]/Resources/Private/Partials/`

## Routing

### List View Routes

The extension contains pagination configuration for list views. If included in the site configuration, it should not require further modification.

### Single View Routes

Route enhancer configuration for single record display with slug-based URLs:

```yaml
[EnhancerName]:
  type: Extbase
  limitToPages: [123]  # Page IDs where this enhancer applies
  extension: LlAnthology
  plugin: AnthologyView
  routes:
    - routePath: '/{record}'
      _controller: 'Anthology::single'
  defaultController: 'Anthology::view'
  aspects:
    record:
      type: PersistedAliasMapper
      tableName: '[table_name]'  # Your model's table name
      routeFieldName: 'slug'  # Field containing the URL slug
```

Include route enhancers in the site configuration:

```yaml
imports:
  -
    resource: 'EXT:[extension_key]/Configuration/Sites/[file_name].yaml'
```

**Configuration Notes:**
- `limitToPages`: Restrict enhancer to specific page UIDs
- `tableName`: Must match the TCA table name for your model
- `routeFieldName`: Database field used for URL generation (typically 'slug')
- Route generates URLs like `/my-record-slug` instead of `?tx_llanthology_anthologyview[record]=123`

## Development

### TODO

- [ ] Implement filtering functionality
- [ ] Add caching mechanisms
- [ ] Enhance error handling
- [ ] Add unit tests
- [ ] Add search functionality
- [ ] Add sitemap entries
