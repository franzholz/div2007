|TYPO3| |Monthly Downloads| |Latest Stable Version|

=======================
TYPO3 extension div2007
=======================

:Repository:  https://github.com/franzholz/div2007
:TER:         https://extensions.typo3.org/extension/div2007/

What is does
------------

This library offers functions for TYPO3 extensions. tx_div2007_div
provides a modified t3lib_div of TYPO3 4.7.10 and backwards
compatibility to TYPO3 6.2 and 4.5. It replaces most of the tslib_pibase
methods. You find the migration classes for TYPO3 6.x, 7.x and 8.x
inside of the extension migration_core. Since version 1.10.30 a
middleware to store the request is provided for Ajax calls. This is
required for Ajax calls since TYPO3 9.5. With this you can use the
method FrontendUtility::getPageId() to get the current page id out of
the speaking url of the routing enhancer.

All class.tx_div2007_xxx.php files have been removed in versions 2.x.
Most code of the former extensions div and lib is not present any more.
This means that the MVC design pattern of it has been given up.
Parts of the former methods have been integrated into the namespace classes.

Requirements
------------

• 2.2.0 and 2.0.5 contain the class JambageCom\Div2007\Compatibility\AbstractPlugin as replacement for
  TYPO3\CMS\Frontend\Plugin\AbstractPlugin needed for TYPO3 13
• 2.1.0 and later require PHP 8 and TYPO3 12.x or TYPO3 13
• 1.16 - 2.0.99 require PHP 7.4 - 8 and TYPO3 10.4 - 12.x
• 1.13 - 11.15 require PHP 7.4 - 8 and TYPO3 10.4 - 11.5
• 1.12.x are the last versions which support PHP 7.2 - 7.4 .
• 1.11.8 has been the last version which supports PHP < 7.2 .
• 1.11.6 has been the last version which supports TYPO3 6.2 .
• 1.7.20 has been the last version which supports TYPO3 4.5 - 6.1 and PHP < 5.5 .

Starting with version 1.12.0 TYPO3 7.6 and PHP 7.2.0 are the minimum
requirements to use the extension div2007.

If you run TYPO3 7 oder 8 and older versions of extensions like
tt_products, then you might consider to install also the extension
migration_core, if older TYPO3 class names are still used in extensions
which did rely on div2007.

Setup
-----

Some texts which are used in multiple extensions are added to the local
language files of div2007. You can simply reuse them in your own
extensions.

Use this setup to overwrite the privacy policy conditions:

example:
~~~~~~~~

::

   lib.div2007 {
     _LOCAL_LANG.default {
       privacy_policy.acknowledged = I agree and confirm to have read the privacy policy.
       privacy_policy.hint_1 = A telephone call or an email sent to us is enough to be deleted from our database. You can do this at any time.
     }
   }


.. |TYPO3| image:: https://img.shields.io/badge/TYPO3-Extension-orange?logo=TYPO3
   :target: https://extensions.typo3.org/extension/div2007
.. |Monthly Downloads| image:: https://poser.pugx.org/jambagecom/div2007/d/monthly
   :target: https://packagist.org/packages/jambagecom/div2007
.. |Latest Stable Version| image:: http://poser.pugx.org/jambagecom/div2007/v
   :target: https://packagist.org/packages/jambagecom/div2007
