<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * email functions
 *
 * $Id$
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_products
 *
 *
 */



class tx_div2007_email {

	/**
	 * Extended mail function
	 */
	public function send_mail (
		$toEMail,
		$subject,
		$message,
		$html,
		$fromEMail,
		$fromName,
		$attachment = '',
		$bcc = '',
		$returnPath = ''
	) {
		global $TYPO3_CONF_VARS;

		if (
			isset($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/utility/class.t3lib_utility_mail.php']) &&
			is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/utility/class.t3lib_utility_mail.php']) &&
			isset($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/utility/class.t3lib_utility_mail.php']['substituteMailDelivery']) &&
			is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/utility/class.t3lib_utility_mail.php']['substituteMailDelivery']) &&
			array_search('t3lib_mail_SwiftMailerAdapter', $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/utility/class.t3lib_utility_mail.php']['substituteMailDelivery']) !== FALSE
		) {
			if (!is_array($toEMail)) {
				$emailArray = t3lib_div::trimExplode(',', $toEMail);
				$toEMail = array();
				foreach ($emailArray as $email) {
					$toEMail[] = $email;
				}
			}

			/** @var $mail t3lib_mail_Message */
			$mailMessage = t3lib_div::makeInstance('t3lib_mail_Message');
			$mailMessage->setTo($toEMail)
				->setFrom(array($fromEMail => $fromName))
				->setReturnPath($returnPath)
				->setSubject($subject)
				->setBody($html, 'text/html', $GLOBALS['TSFE']->renderCharset)
				->addPart($message, 'text/plain', $GLOBALS['TSFE']->renderCharset);

			if (isset($attachment)) {
				if (is_array($attachment)) {
					$attachmentArray = $attachment;
				} else {
					$attachmentArray = array($attachment);
				}
				foreach ($attachmentArray as $theAttachment) {
					if (file_exists($theAttachment)) {
						$mailMessage->attach(Swift_Attachment::fromPath($theAttachment));
					}
				}
			}
			if ($bcc != '') {
				$mailMessage->addBcc($bcc);
			}
			$mailMessage->send();
		} else {
			$fromName = tx_div2007_alpha5::slashName($fromName);
			t3lib_div::requireOnce(PATH_t3lib . 'class.t3lib_htmlmail.php');
			$cls = t3lib_div::makeInstanceClassName('t3lib_htmlmail');
			if (is_array($toEMail)) {
				list($email, $name) = each($toEMail);
				$toEMail = tx_div2007_alpha5::slashName($name) . ' <' . $email . '>';
			}

			if (class_exists($cls)) {
				$Typo3_htmlmail = t3lib_div::makeInstance('t3lib_htmlmail');
				$Typo3_htmlmail->start();
				$Typo3_htmlmail->mailer = 'TYPO3 HTMLMail';
				// $Typo3_htmlmail->useBase64(); TODO
				$message = html_entity_decode($message);
				if ($Typo3_htmlmail->linebreak == chr(10)) {
					$message = str_replace(chr(13) . chr(10), $Typo3_htmlmail->linebreak, $message);
				}

				$Typo3_htmlmail->subject = $subject;
				$Typo3_htmlmail->from_email = $fromEMail;
				$Typo3_htmlmail->returnPath = $fromEMail;
				$Typo3_htmlmail->from_name = $fromName;
				$Typo3_htmlmail->replyto_email = $Typo3_htmlmail->from_email;
				$Typo3_htmlmail->replyto_name = $Typo3_htmlmail->from_name;
				$Typo3_htmlmail->organisation = '';

				if (isset($attachment)) {
					if (is_array($attachment)) {
						$attachmentArray = $attachment;
					} else {
						$attachmentArray = array($attachment);
					}
					foreach ($attachmentArray as $theAttachment) {
						if (file_exists($theAttachment)) {
							$Typo3_htmlmail->addAttachment($theAttachment);
						}
					}
				}

				if ($html) {
					$Typo3_htmlmail->theParts['html']['content'] = $html;
					$Typo3_htmlmail->theParts['html']['path'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
					$Typo3_htmlmail->extractMediaLinks();
					$Typo3_htmlmail->extractHyperLinks();
					$Typo3_htmlmail->fetchHTMLMedia();
					$Typo3_htmlmail->substMediaNamesInHTML(0);	// 0 = relative
					$Typo3_htmlmail->substHREFsInHTML();
					$Typo3_htmlmail->setHTML($Typo3_htmlmail->encodeMsg($Typo3_htmlmail->theParts['html']['content']));
				}
				if ($message) {
					$Typo3_htmlmail->addPlain($message);
				}
				$Typo3_htmlmail->setHeaders();
				if ($bcc != '') {
					$Typo3_htmlmail->add_header('Bcc: '.$bcc);
				}

				if (isset($attachment) && is_array($attachment) && count($attachment)) {
					if (isset($Typo3_htmlmail->theParts) && is_array($Typo3_htmlmail->theParts) && isset($Typo3_htmlmail->theParts['attach']) && is_array($Typo3_htmlmail->theParts['attach'])) {
						foreach ($Typo3_htmlmail->theParts['attach'] as $k => $media) {
							$Typo3_htmlmail->theParts['attach'][$k]['filename'] = basename($media['filename']);
						}
					}
				}
				$Typo3_htmlmail->setContent();
				$Typo3_htmlmail->setRecipient(explode(',', $toEMail));

				$hookVar = 'sendMail';
				if ($hookVar && is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXT][$hookVar])) {
					foreach  ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXT][$hookVar] as $classRef) {
						$hookObj= &t3lib_div::getUserObj($classRef);
						if (method_exists($hookObj, 'init')) {
							$hookObj->init($Typo3_htmlmail);
						}
						if (method_exists($hookObj, 'sendMail')) {
							$rc = $hookObj->sendMail($Typo3_htmlmail, $toEMail, $subject, $message, $html, $fromEMail, $fromName, $attachment, $bcc);
						}
					}
				}

				if ($rc !== FALSE) {
					$Typo3_htmlmail->sendTheMail();
				}
			}
		}
	}
}


?>