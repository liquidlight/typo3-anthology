# ll_anthology Extension

A TYPO3 extension that provides a generic content display system for listing and viewing records from any configured repository with advanced filtering capabilities.

## Overview

The `ll_anthology` extension creates a flexible plugin system that can display content from any TYPO3 repository in both list and single view modes. It dynamically loads repositories based on configuration and provides pagination, advanced filtering, template customization, and routing capabilities.

## Current Status

✅ **Fully Implemented:**
- List and single view modes with pagination
- Repository factory and dynamic loading
- Search filtering with multiple field support and text input template
- Category filtering with multiple display modes (select, link, radio) and complete template
- Template path resolution and custom template support
- Page title generation
- FlexForm configuration with all options

⚠️ **Partially Implemented:**
- Date filtering (TCA configuration and template exist, but constraint throws `NotImplementedMethodException` and template is empty)

🔄 **In Development:**
- Complete date filter implementation
- Filter combination logic (AND/OR switching)
- Enhanced error handling

## Features

- **Dual Display Modes**: List view with pagination and single record view
- **Dynamic Repository Loading**: Configurable to work with any TYPO3 repository
- **Advanced Filtering System**: Search and category-based filtering with multiple display modes (date filtering planned)
- **Flexible Template System**: Custom template paths and template name overrides
- **Pagination Support**: Configurable items per page and pagination links
- **SEO-Friendly**: Automatic page title generation from record data
- **Route Enhancement**: Built-in route enhancers for clean URLs

## Installation

### Requirements

- TYPO3 CMS ^13.4
- PHP 8.1+
- Composer
- FluidTYPO3 VHS ^7.1 (for enhanced Fluid ViewHelpers)

### Installation Steps

1. Install via Composer:
   ```bash
   composer require liquidlight/typo3-anthology
   ```

2. Include the TypoScript configuration set in your site configuration or manually import the setup:
   ```typoscript
   @import 'EXT:ll_anthology/Configuration/TypoScript/setup'
   ```

3. Configure the extension using TypoScript and FlexForm settings as described below.
   ```yaml
   imports:
     -

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

The extension requires TypoScript configuration to define available repositories and filter implementations:

**Note**: The current TypoScript setup in `Configuration/TypoScript/setup.typoscript` includes the complete configuration with filter implementations. You may need to add your specific repository mappings.

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
- **No Results Text**: Custom message when no records are found
- **Template**: Override default template name

#### Filters Tab
- **Filters**: Configure inline filter records for search, category, and date filtering (list mode only)

## Usage

### List View Mode

Displays a paginated list of records from the selected repository. Supports:
- Configurable items per page
- Sliding window pagination
- Custom template overrides
- Advanced filtering system with search, category, and date filters

### Single View Mode

Displays individual record details. Features:
- Automatic page title generation
- 404 handling for missing records
- Template path resolution from repository extension

## Filtering System
The extension provides a comprehensive filtering system that allows users to filter records in list view mode.

### Filter Types

#### Search Filter
- **Purpose**: Full-text search across multiple fields
- **Configuration**: Select which database fields to search in
- **Display**: Text input field with optional placeholder
- **Implementation**: Uses LIKE queries with OR logic across selected fields

#### Category Filter
- **Purpose**: Filter records by category relationships
- **Configuration**: Select a parent category to show its children as filter options
- **Display Modes**:
  - **Select**: Dropdown selection
  - **Link**: Clickable category links
  - **Check**: Checkbox selection (multiple categories)
- **Implementation**: Uses category UID matching via `categories.uid` field

#### Date Filter
- **Purpose**: Filter records by date ranges
- **Status**: ⚠️ **Not yet implemented** - placeholder exists but throws `NotImplementedMethodException`
- **Configuration**:
  - Select which date field to filter on
  - Choose date span (months or years)
- **Display Modes**:
  - **Select**: Dropdown with predefined date ranges
  - **Link**: Clickable date range links
- **Implementation**: Date range queries on specified datetime fields

### Filter Configuration
Filters are configured as inline records in the plugin's Filters tab:

1. **Filter Type**: Choose between search, category, or date
2. **Title**: Display label for the filter
3. **Display Mode**: How the filter appears to users (select, link, check)
4. **Field Configuration**: Specific settings based on filter type:
   - Search: Select searchable fields and set placeholder text
   - Category: Choose parent category for filter options
   - Date: Select date field and time span

### Filter Implementation
The filtering system uses a constraint-based architecture:

- **ConstraintBuilder**: Processes active filters and builds query constraints
- **FilterConstraintInterface**: Interface for filter implementations
- **SearchConstraint**: Handles search filter logic
- **CategoryConstraint**: Handles category filter logic
- **FilterRepository**: Manages filter record retrieval
- **FilterConfigurationHook**: Populates field options based on TCA configuration

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

#### FilterConfigurationHook
Populates filter field options:
- Analyzes TCA configuration for target repository
- Provides searchable fields for search filters
- Provides date fields for date filters

#### ConstraintBuilder
Builds query constraints from active filters:
- Validates filter implementations
- Creates constraint objects for query modification
- Supports extensible filter types

#### Filter Model & Repository
Manages filter configuration:
- **Filter**: Domain model for filter records
- **FilterRepository**: Repository for filter data access
- Supports search, category, and date filter types

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

- [x] ~~Implement filtering functionality~~ ✅ **Completed**
- [ ] Add caching mechanisms
- [ ] Enhance error handling
- [ ] Add unit tests
- [x] ~~Add search functionality~~ ✅ **Completed**
- [ ] Add date filter implementation ⚠️ **High Priority** - Currently throws NotImplementedMethodException
- [ ] Add sitemap entries
- [ ] Add filter combination logic (AND/OR switching)
- [ ] Add custom filter constraint implementations
- [ ] Move TypoScript to TSConfig
- [ ] Convert filter `<f:section>`s to partials
- [ ] Move partials prefixed with `List` into a `List` folder
- [ ] Check and amend rootpath ordering
- [ ] Add LinkHandler
- [ ] Set single and list page UIDs somewhere in YAML for use in link handler
