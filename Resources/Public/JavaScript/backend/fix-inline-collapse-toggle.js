/**
 * Works around https://github.com/twbs/bootstrap/issues/26516: Bootstrap's Collapse
 * data-api resolves `data-bs-target` with an unescaped `document.querySelector()`
 * call, so it silently does nothing whenever the target id contains a literal ".",
 * such as the ids TYPO3 generates for records inline (IRRE) inside a FlexForm field
 * named "settings.filters" or "settings.preFilters".
 *
 * Only steps in when the native (unescaped) selector fails to resolve, so already
 * working collapse toggles elsewhere are left untouched.
 */
import { Collapse } from 'bootstrap';
import RegularEvent from '@typo3/core/event/regular-event.js';

new RegularEvent('click', (event, target) => {
	const rawSelector = target.getAttribute('data-bs-target');
	if (!rawSelector || rawSelector === '#' || document.querySelector(rawSelector) !== null) {
		return;
	}

	const escapedSelector = '#' + CSS.escape(rawSelector.slice(1));
	const collapseElement = document.querySelector(escapedSelector);
	if (collapseElement === null) {
		return;
	}

	event.preventDefault();

	const willShow = !collapseElement.classList.contains('show');

	// `toggle: false` matches Bootstrap's own data-api handler: without it, creating the
	// instance for the first time triggers an implicit initial `show()`, and the explicit
	// `.toggle()` below would then fire a second, near-simultaneous `show.bs.collapse` -
	// which made TYPO3's not-yet-loaded IRRE record start, then immediately abort, its
	// AJAX content fetch (its "click again to cancel a pending load" behaviour).
	Collapse.getOrCreateInstance(collapseElement, { toggle: false }).toggle();

	// Bootstrap also can't find this button to update its own chevron/aria-expanded state:
	// its Collapse constructor discovers trigger buttons the same broken (unescaped) way,
	// so `target` never ends up in its internal trigger list. Keep them in sync ourselves.
	// Done from the known toggle direction, not a "shown.bs.collapse" listener, because a
	// not-yet-loaded record's first `show()` is deferred (preventDefault'd) until its AJAX
	// content fetch completes - that event wouldn't fire until then, leaving the chevron
	// stale for the whole round trip (or forever, if the fetch fails).
	target.classList.toggle('collapsed', !willShow);
	target.setAttribute('aria-expanded', willShow ? 'true' : 'false');
}).delegateTo(document, '[data-bs-toggle="collapse"]');
