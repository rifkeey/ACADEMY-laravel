/*
 * Interface messages for scripts. The layout embeds the current language's messages as JSON
 * (<script type="application/json" id="i18n">), so scripts never hard-code English. Unknown
 * keys fall back to the key itself.
 */
let messages = null;

export function t(key) {
    if (messages === null) {
        try {
            messages = JSON.parse(document.getElementById('i18n')?.textContent ?? '{}');
        } catch {
            messages = {};
        }
    }
    return messages[key] ?? key;
}
