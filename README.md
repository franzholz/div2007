# TYPO3 extension div2007

## What is does

This library offers functions for TYPO3 extensions. It provides a modified t3lib_div of TYPO3 4.7.10 and backwards compatibility to TYPO3 6.2 and 4.5. It replaces most of the tslib_pibase methods.
You find the migration classes for TYPO3 6.x, 7.x and 8.x inside of the extension migration_core.

## Requirements

1.7.20 has been the last version which supports TYPO3 4.5 - 6.1 and PHP < 5.5 .

Starting with version 1.8.0 TYPO3 6.2 and PHP 5.5.0 are the minimum requirements to use the extension div2007.

If you run TYPO3 7 oder 8 then you might consider to install also the extension migration_core, if older TYPO3 methods are still used in extensions which did rely on div2007.

## Setup

Some texts which are used in multiple extensions are added to the local language files of div2007. You can simply reuse them in your own extensions.

Use this setup to overwrite the privacy policy conditions:

### example:

```
lib.div2007 {
  _LOCAL_LANG.default {
    privacy_policy.acknowledged = I agree and confirm to have read the privacy policy.
    privacy_policy.hint_1 = A telephone call or an email sent to us is enough to be deleted from our database. You can do this at any time.
  }
}
```

