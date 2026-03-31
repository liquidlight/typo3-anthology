# 1.2.4 **31st March 2026**

#### Feature

- Add `BeforeGetAllRecordsEvent` and `BeforeGetRecordsWithConstraintsEvent` PSR-14 events

#### Fix

- Remove unexpected exclamation mark from keyword filter partial
- Replace direct property access in `PageTitleProvider` with `ObjectAccess::getProperty()`


# 1.2.3 **10th March 2026**

#### Feature

- Add preview link configuration to `SetupCommand`
- Dispatch PSR-14 events from `AnthologyController`


# 1.2.2 **10th March 2026**

#### Feature

- Add site configuration to templates and partials

#### Fix

- Replace hardcoded sitemap key with model name in setup command
- Display record image in list view (if set)


# 1.2.1 **4th March 2026**

#### Feature

- Output warning on default single view when in development mode
- Add <label> elements to filter inputs
- Migrate documentation to reStructuredText format

#### Fix

- Remove <f:debug> from list view
- Hide pagination from list view when pagination is not required
- Fix filter reset link Fluid element


# 1.2.0 **17th February 2026**

#### Feature

- Replace cached repository and filter storage with list generated at compilation


# 1.1.1 **22nd January 2026**

#### Fix

- Fix bug when loading hardcoded filter onfiguration


# 1.1.0 **21st January 2026**

#### Feature

- Provide option to hardcode repository and filter classes


# 1.0.1 **27th November 2025**

#### Fix

- Fixes bug in PageTitleService when page title is not set


# 1.0.0 **25th November 2025**

#### Feature

- Initial release
