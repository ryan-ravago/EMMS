// Long rows fold into a "Show N more" toggle in the mobile card layout.
// resources/css/mobile-tables.css hides the folded fields; this only adds the toggle.

const VISIBLE_FIELDS = 5; // title + 4 key fields (keep in sync with the CSS)
const FIELD = ':scope > .fi-ta-cell:not(.fi-ta-selection-cell, .emms-more, :has(> .fi-ta-actions), [class*=":fi-visible"])';
const ROW = '.fi-ta-table-stacked-on-mobile > tbody > tr.fi-ta-row:not(.fi-ta-group-header-row, .fi-ta-summary-row)';

// Livewire re-renders rows (and drops the toggle), so remember what was open.
const expanded = new Set();

function enhance() {
    document.querySelectorAll(ROW).forEach((row) => {
        if (row.querySelector(':scope > .emms-more')) return;

        const folded = row.querySelectorAll(FIELD).length - VISIBLE_FIELDS;
        if (folded < 2) return; // not worth a toggle for a single field

        const key = row.getAttribute('wire:key');
        const button = document.createElement('button');
        const cell = document.createElement('td');

        const render = (open) => {
            button.setAttribute('aria-expanded', open);
            button.textContent = open ? 'Show less' : `Show ${folded} more`;
        };

        button.type = 'button';
        button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') !== 'true';
            render(open);
            if (key) open ? expanded.add(key) : expanded.delete(key);
        });
        render(expanded.has(key));

        cell.className = 'fi-ta-cell emms-more';
        cell.append(button);
        row.append(cell);
    });
}

new MutationObserver(enhance).observe(document.documentElement, { childList: true, subtree: true });
enhance();
