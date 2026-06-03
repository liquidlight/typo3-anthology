# 2.0.0

**3rd June 2026**

#### Feature

- Remove deprecated `BeforeGetRecordsWithConstraintsEvent` and `BeforeGetAllRecordsEvent` events
- [BREAKING] Migrate plugin from list_type to CType (#27)

# 1.3.0

**3rd June 2026**

#### Feature

- Make AnthologyController `private` visibility `protected` to allow extending controller
- Add full path to language strings in Fluid templates to allow extension
- Decompose `SetupCommand` to enable extending commands
- Add content type restrictions for storage folder to `page.tsconfig` during setup
- Consolidate `BeforeGetAllRecordsEvent` and `BeforeGetRecordsWithConstraintsEvent` into `BeforeGetAllRecordsEvent`

#### Fix

- Fix issue with cached page titles displayed on subsequent loads
- Fix issue where no fields were available for filters in Anthology extensions

#### Refactor

- Add `@extensionScannerIgnoreLine` to any false positives thrown up from the extension file scanner (#26)
- Replace `StandaloneView` with `ViewFactoryInterface` (#26)
- Remove deprecated constants (#26)

# 1.2.4 

**31st March 2026**

#### Feature

- Add `BeforeGetAllRecordsEvent` and `BeforeGetRecordsWithConstraintsEvent` PSR-14 events

#### Fix

- Remove unexpected exclamation mark from keyword filter partial
- Replace direct property access in `PageTitleProvider` with `ObjectAccess::getProperty()`


# 1.2.3 

**10th March 2026**

#### Feature

- Add preview link configuration to `SetupCommand`
- Dispatch PSR-14 events from `AnthologyController`


# 1.2.2 

**10th March 2026**

#### Feature

- Add site configuration to templates and partials

#### Fix

- Replace hardcoded sitemap key with model name in setup command
- Display record image in list view (if set)


# 1.2.1 

**4th March 2026**

#### Feature

- Output warning on default single view when in development mode
- Add <label> elements to filter inputs
- Migrate documentation to reStructuredText format

#### Fix

- Remove <f:debug> from list view
- Hide pagination from list view when pagination is not required
- Fix filter reset link Fluid element


# 1.2.0

**17th February 2026**

#### Feature

- Replace cached repository and filter storage with list generated at compilation


# 1.1.1 

**22nd January 2026**

#### Fix

- Fix bug when loading hardcoded filter onfiguration


# 1.1.0 

**21st January 2026**

#### Feature

- Provide option to hardcode repository and filter classes


# 1.0.1 

**27th November 2025**

#### Fix

- Fixes bug in PageTitleService when page title is not set


# 1.0.0 

**25th November 2025**

#### Feature

- Initial release
