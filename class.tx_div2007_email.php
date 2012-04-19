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
*  http://www.gnu.org/copyleft/gpl.HTMLContent.
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
 * @subpackage div2007
 *
 *
 */


require_once(PATH_BE_div2007 . 'class.tx_div2007_alpha5.php');



class tx_div2007_email {

	/**
	* sends the email in plaintext or HTML format or both
	*
	* @param string  $toEMail: recipients email address
	* @param string  $subject: subject of the message
	* @param string  $PLAINContent: plain version of the message
	* @param string  $HTMLContent: HTML version of the message
	* @param string  $fromEmail: email address
	* @param string  $fromName: name
	* @param string  $attachment: file name
	* @param string  $cc: CC
	* @param string  $bcc: BCC
	* @param string  $returnPath: return path
	* @param string  $replyTo: email address
	* @param string  $extKey: extension key
	* @param string  $hookVar: name of the hook
	* @return void
	*/
	static public function sendMail (
		$toEMail,
		$subject,
		$PLAINContent,
		$HTMLContent,
		$fromEMail,
		$fromName,
		$attachment = '',
		$cc = '',
		$bcc = '',
		$returnPath = '',
		$replyTo = '',
		$extKey = '',
		$hookVar = ''
	) {
		global $TYPO3_CONF_VARS;

		$result = TRUE;

		if ($subject == '') {
			$defaultSubject = 'message from ' . $fromName . ($fromName != '' ? '<' : '') . $fromEMail . ($fromName != '' ? '>' : '');
				// First line is subject
			if ($HTMLContent) {
				$parts = preg_split('/<title>|<\\/title>/i', $HTMLContent, 3);
				$subject = trim($parts[1]) ? strip_tags(trim($parts[1])) : $defaultSubject;
			} else {
					// First line is subject
				$parts = explode(chr(10), $PLAINContent, 2);
				$subject = trim($parts[0]) ? trim($parts[0]) : $defaultSubject;
				$PLAINContent = trim($parts[1]);
			}
		}

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
			$mail = t3lib_div::makeInstance('t3lib_mail_Message');
			$mail->setTo($toEMail)
				->setFrom(array($fromEMail => $fromName))
				->setReturnPath($returnPath)
				->setSubject($subject)
				->setBody($HTMLContent, 'text/HTMLContent', $GLOBALS['TSFE']->renderCharset)
				->addPart($PLAINContent, 'text/plain', $GLOBALS['TSFE']->renderCharset);

			if ($replyTo) {
				$mail->setReplyTo(array($replyTo => $fromEmail));
			}

			if (isset($attachment)) {
				if (is_array($attachment)) {
					$attachmentArray = $attachment;
				} else {
					$attachmentArray = array($attachment);
				}
				foreach ($attachmentArray as $theAttachment) {
					if (file_exists($theAttachment)) {
						$mail->attach(Swift_Attachment::fromPath($theAttachment));
					}
				}
			}

				// HTML
			if (trim($HTMLContent)) {
				$HTMLContent = self::embedMedia($mail, $HTMLContent);
				$mail->setBody($HTMLContent, 'text/html', $GLOBALS['TSFE']->renderCharset);
			}

			if ($bcc != '') {
				$mail->addBcc($bcc);
			}
		} else {
			$fromName = tx_div2007_alpha5::slashName($fromName);
			t3lib_div::requireOnce(PATH_t3lib . 'class.t3lib_htmlmail.php');
			$cls = t3lib_div::makeInstanceClassName('t3lib_htmlmail');
			if (is_array($toEMail)) {
				list($email, $name) = each($toEMail);
				$toEMail = tx_div2007_alpha5::slashName($name) . ' <' . $email . '>';
			}

			if (class_exists($cls)) {
				$mail = t3lib_div::makeInstance('t3lib_htmlmail');
				$mail->start();
				$mail->mailer = 'TYPO3 HTMLMail';
				// $mail->useBase64(); TODO
				$PLAINContent = html_entity_decode($PLAINContent);
				if ($mail->linebreak == chr(10)) {
					$PLAINContent =
						str_replace(
							chr(13) . chr(10),
							$mail->linebreak,
							$PLAINContent
						);
				}

				$mail->subject = $subject;
				$mail->from_email = $fromEMail;
				$mail->returnPath = $fromEMail;
				$mail->from_name = $fromName;
				if ($replyTo) {
					$mail->replyto_email = $replyTo;
					$mail->replyto_name = $mail->from_name;
				}
				$mail->organisation = '';

				if (isset($attachment)) {
					if (is_array($attachment)) {
						$attachmentArray = $attachment;
					} else {
						$attachmentArray = array($attachment);
					}
					foreach ($attachmentArray as $theAttachment) {
						if (file_exists($theAttachment)) {
							$mail->attach(Swift_Attachment::fromPath($theAttachment));
						}
					}
				}

				if ($HTMLContent) {
					$mail->theParts['HTMLContent']['content'] = $HTMLContent;
					$mail->theParts['HTMLContent']['path'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
					$mail->extractMediaLinks();
					$mail->extractHyperLinks();
					$mail->fetchHTMLMedia();
					$mail->substMediaNamesInHTML(0);	// 0 = relative
					$mail->substHREFsInHTML();
					$mail->setHTML(
						$mail->encodeMsg($mail->theParts['HTMLContent']['content'])
					);
				}
				if ($PLAINContent) {
					$mail->addPlain($PLAINContent);
				}
				$mail->setHeaders();
				if ($bcc != '') {
					$mail->add_header('Bcc: ' . $bcc);
				}

				if (isset($attachment) && is_array($attachment) && count($attachment)) {
					if (
						isset($mail->theParts) &&
						is_array($mail->theParts) &&
						isset($mail->theParts['attach']) &&
						is_array($mail->theParts['attach'])
					) {
						foreach ($mail->theParts['attach'] as $k => $media) {
							$mail->theParts['attach'][$k]['filename'] = basename($media['filename']);
						}
					}
				}
				$mail->setContent();
				$mail->setRecipient(explode(',', $toEMail));
			}
		}

		if (
			isset($mail) &&
			is_object($mail) &&
			$extKey &&
			$hookVar &&
			isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]) &&
			is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]) &&
			isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar]) &&
			is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar])
		) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar] as $classRef) {
				$hookObj= &t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, 'init')) {
					$hookObj->init($mail);
				}

				if (method_exists($hookObj, 'sendMail')) {
					$result = $hookObj->sendMail(
						$toEMail,
						$subject,
						$PLAINContent,
						$HTMLContent,
						$fromEMail,
						$fromName,
						$attachment,
						$cc,
						$bcc,
						$returnPath,
						$replyTo,
						$extKey,
						$hookVar
					);
				}
			}
		}

		if (
			$result !== FALSE &&
			isset($mail) &&
			is_object($mail)
		) {
			$mailClass = get_class($mail);
			if ($mailClass == 't3lib_mail_Message') {
				$mail->send();
			} else {
				$mail->sendTheMail();
			}
		}
	}

	/**
	 * Embeds media into the mail message
	 *
	 * @param t3lib_mail_Message $mail: mail message
	 * @param string $htmlContent: the HTML content of the message
	 * @return string the subtituted HTML content
	 */
	static public function embedMedia (t3lib_mail_Message $mail, $htmlContent) {
		$substitutedHtmlContent = $htmlContent;
		$media = array();
		$attribRegex = self::makeTagRegex(array('img', 'embed', 'audio', 'video'));
			// Split the document by the beginning of the above tags
		$codepieces = preg_split($attribRegex, $htmlContent);
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		$reg = array();
		for ($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($htmlContent, $len + 1, 10), ' '));
			$len += strlen($tag) + strlen($codepieces[$i]) + 2;
			$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
				// Fetches the attributes for the tag
			$attributes = self::getTagAttributes($reg[0]);
			if ($attributes['src'] != '' && $attributes['src'] != 'clear.gif') {
				$media[] = $attributes['src'];
			}
		}

		foreach ($media as $key => $source) {
			$substitutedHtmlContent = str_replace(
				'"' . $source . '"',
				'"' . $mail->embed(Swift_Image::fromPath($source)) . '"',
				$substitutedHtmlContent
			);
		}

		return $substitutedHtmlContent;
	}

	/**
	 * Creates a regular expression out of an array of tags
	 *
	 * @param	array		$tags: the array of tags
	 * @return	string		the regular expression
	 */
	static public function makeTagRegex (array $tags) {
		$regexpArray = array();
		foreach ($tags as $tag) {
			$regexpArray[] = '<' . $tag . '[[:space:]]';
		}
		return '/' . implode('|', $regexpArray) . '/i';
	}

	/**
	 * This function analyzes a HTML tag
	 * If an attribute is empty (like OPTION) the value of that key is just empty. Check it with is_set();
	 *
	 * @param string $tag: is either like this "<TAG OPTION ATTRIB=VALUE>" or this " OPTION ATTRIB=VALUE>" which means you can omit the tag-name
	 * @return array array with attributes as keys in lower-case
	 */
	static public function getTagAttributes ($tag) {
		$attributes = array();
		$tag = ltrim(preg_replace('/^<[^ ]*/', '', trim($tag)));
		$tagLen = strlen($tag);
		$safetyCounter = 100;
			// Find attribute
		while ($tag) {
			$value = '';
			$reg = preg_split('/[[:space:]=>]/', $tag, 2);
			$attrib = $reg[0];

			$tag = ltrim(substr($tag, strlen($attrib), $tagLen));
			if (substr($tag, 0, 1) == '=') {
				$tag = ltrim(substr($tag, 1, $tagLen));
				if (substr($tag, 0, 1) == '"') {
						// Quotes around the value
					$reg = explode('"', substr($tag, 1, $tagLen), 2);
					$tag = ltrim($reg[1]);
					$value = $reg[0];
				} else {
						// No quotes around value
					preg_match('/^([^[:space:]>]*)(.*)/', $tag, $reg);
					$value = trim($reg[1]);
					$tag = ltrim($reg[2]);
					if (substr($tag, 0, 1) == '>') {
						$tag = '';
					}
				}
			}
			$attributes[strtolower($attrib)] = $value;
			$safetyCounter--;
			if ($safetyCounter < 0) {
				break;
			}
		}
		return $attributes;
	}

}


?>