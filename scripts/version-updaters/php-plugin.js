/**
 * commit-and-tag-version updater for emailchef-add-on-for-pmp.php:
 * keeps the plugin header "Version:" and the
 * EMAILCHEF_ADD_ON_FOR_PMP_VERSION constant in sync.
 */

const HEADER_RE = /(\* Version:\s+)([0-9.]+)/;
const CONSTANT_RE = /(define\(\s*['"]EMAILCHEF_ADD_ON_FOR_PMP_VERSION['"],\s*['"])([0-9.]+)(['"]\s*\))/;

module.exports.readVersion = function (contents) {
    const match = contents.match(HEADER_RE);
    if (!match) {
        throw new Error('Version header not found in emailchef-add-on-for-pmp.php');
    }
    return match[2];
};

module.exports.writeVersion = function (contents, version) {
    if (!CONSTANT_RE.test(contents)) {
        throw new Error('EMAILCHEF_ADD_ON_FOR_PMP_VERSION constant not found in emailchef-add-on-for-pmp.php');
    }
    return contents
        .replace(HEADER_RE, `$1${version}`)
        .replace(CONSTANT_RE, `$1${version}$3`);
};
