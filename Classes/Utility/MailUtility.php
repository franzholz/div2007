<?php

namespace JambageCom\Div2007\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
/**
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * Mailing functions
 * TYPO3 >= 6.2 is required
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
use TYPO3\CMS\Core\Mail\MailMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Exception\TransportException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailUtility
{
    /**
     * sends the email in plaintext or HTML format or both.
     *
     * @return bool if the email has been sent
     */
    public static function send(
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
        $extensionKey = '',
        $hookVar = '',
        $defaultSubject = ''
    ) {
        $result = true;
        $debug =
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['debug']['mail'];
        if (
            $debug == 'DEBUG_AND_SEND' ||
            $debug == 'DEBUG'
        ) {
            debug($toEMail, '$toEMail'); // keep this
            debug($subject, '$subject'); // keep this
            debug($PLAINContent, '$PLAINContent'); // keep this
            debug($HTMLContent, '$HTMLContent'); // keep this
            debug($fromEMail, '$fromEMail'); // keep this
            debug($fromName, '$fromName'); // keep this
        }

        if (
            $debug == 'NO' ||
            $debug == 'DEBUG'
        ) {
            return $result;
        }

        $charset = 'utf-8';

        if (!is_array($toEMail) && strlen($toEMail)) {
            $emailArray = GeneralUtility::trimExplode(',', $toEMail);
            $toEMail = [];
            foreach ($emailArray as $email) {
                $toEMail[] = $email;
            }
        }

        if (is_array($toEMail) && count($toEMail)) {
            $emailArray = $toEMail;
            $errorEmailArray = [];
            foreach ($toEMail as $k => $v) {
                if (
                    (
                        !is_numeric($k) &&
                        !GeneralUtility::validEmail($k)
                    ) &&
                    (
                        $v == '' ||
                        !GeneralUtility::validEmail($v)
                    )
                ) {
                    unset($emailArray[$k]);
                    $errorEmailArray[$k] = $v;
                }
            }
            $toEMail = $emailArray;

            if (
                count($errorEmailArray)
            ) {
                foreach ($errorEmailArray as $k => $v) {
                    $email = $k;
                    if (is_numeric($k)) {
                        $email = $v;
                    }

                    debug('MailUtility::send invalid email address: to "' . $email . '"'); // keep this
                }
            }

            if (
                !count($toEMail)
            ) {
                debug('MailUtility::send exited with error 1'); // keep this

                return false;
            }
        } else {
            debug('MailUtility::send exited with error 2'); // keep this

            return false;
        }

        if (
            !GeneralUtility::validEmail($fromEMail)
        ) {
            debug('MailUtility::send invalid email address: from "' . $fromEMail . '"'); // keep this
            debug('MailUtility::send exited with error 3'); // keep this

            return false;
        }

        $fromName = str_replace('"', '\'', $fromName);

        if ($subject == '') {
            if ($defaultSubject == '') {
                $fromNameSlashed = FrontendUtility::slashName($fromName);
                $defaultSubject = 'message from ' . $fromNameSlashed . ($fromNameSlashed != '' ? '<' : '') . $fromEMail . ($fromNameSlashed != '' ? '>' : '');
            }

            // First line is subject
            if ($HTMLContent) {
                $parts = preg_split('/<title>|<\\/title>/i', $HTMLContent, 3);
                $subject = trim($parts[1]) ? strip_tags(trim($parts[1])) : $defaultSubject;
            } else {
                // First line is subject
                $parts = explode(chr(10), $PLAINContent, 2);
                $subject = trim($parts[0]) ?: $defaultSubject;
                $PLAINContent = trim($parts[1]);
            }
        }

        $mail = GeneralUtility::makeInstance(MailMessage::class);
        // HTML
        if (trim($HTMLContent)) {
            $HTMLContent = static::embedMedia($mail, $HTMLContent);
        }

        if ($mail instanceof Email) {
            $mail
                ->setTo($toEMail)
                ->from(new Address($fromEMail, $fromName))
                ->subject($subject)
            ;
            if ($HTMLContent != '') {
                $mail->html($HTMLContent);
            }
            if ($PLAINContent != '') {
                $mail->text($PLAINContent);
            }
        } else {
            throw new \RuntimeException('Extension ' . DIV2007_EXT . ' MailUtility: unsupported mailer class ' . $mail::class . '. ', 1612276260
            );
        }

        if ($returnPath) {
            $mail->returnPath($returnPath);
        }

        if ($replyTo) {
            $mail->replyTo($replyTo);
        }

        if (isset($attachment)) {
            if (is_array($attachment)) {
                $attachmentArray = $attachment;
            } else {
                $attachmentArray = [$attachment];
            }

            foreach ($attachmentArray as $theAttachment) {
                if (file_exists($theAttachment)) {
                    $mail->attachFromPath($theAttachment);
                }
            }
        }

        if ($bcc != '') {
            $mail->addBcc($bcc);
        }

        if (
            isset($mail) &&
            is_object($mail) &&
            $extensionKey &&
            $hookVar &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey][$hookVar]) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey][$hookVar])
        ) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey][$hookVar] as $classRef) {
                $hookObj = GeneralUtility::makeInstance($classRef);
                if (method_exists($hookObj, 'init')) {
                    $hookObj->init($mail);
                }

                if (method_exists($hookObj, 'sendMail')) {
                    $result = $hookObj->sendMail(
                        $mail,
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
                        $extensionKey,
                        $hookVar,
                        $result
                    );

                    if ($result === false) {
                        debug('MailUtility::send exited with error 5'); // keep this
                        break;
                    }
                }
            }
        }

        if (
            $result !== false &&
            isset($mail) &&
            is_object($mail)
        ) {
            $signerRow = null;

            if (
                isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]) &&
                isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['dkimFile']) &&
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['dkimFile'] != ''
            ) {
                $signerXmlFilename =
                    GeneralUtility::resolveBackPath(
                        Environment::getLegacyConfigPath() . '../' . $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['dkimFile']
                    );
                // determine the file type
                $basename = basename($signerXmlFilename);
                $posFileExtension = strrpos($basename, '.');
                $fileExtension = substr($basename, $posFileExtension + 1);
                $absFilename = GeneralUtility::getFileAbsFileName($signerXmlFilename);
                $handle = fopen($absFilename, 'rt');
                if ($handle === false) {
                    throw new \Exception(DIV2007_EXT . ': DKIM Signer XML file not found ("' . $absFilename . '")');
                }

                if ($fileExtension == 'xml') {
                    $objDom = new \DOMDocument();
                    $objDom->encoding = 'utf-8';
                    $resultLoad = $objDom->load($absFilename, LIBXML_COMPACT);

                    if ($resultLoad) {
                        $bRowFits = false;
                        $objRows = $objDom->getElementsByTagName('Row');

                        foreach ($objRows as $myRow) {
                            $tag = $myRow->nodeName;

                            if ($tag == 'Row') {
                                $objRowDetails = $myRow->childNodes;
                                $xmlRow = [];
                                $count = 0;

                                foreach ($objRowDetails as $rowDetail) {
                                    $count++;
                                    $detailValue = '';
                                    $detailTag = $rowDetail->nodeName;

                                    if ($detailTag != '#text') {
                                        $detailValue = trim($rowDetail->nodeValue);
                                        $xmlRow[$detailTag] = $detailValue;
                                    }
                                    if ($count > 30) {
                                        break;
                                    }
                                }
                            }
                            $parts = explode('@', $fromEMail);
                            $fromEmailDomain = $parts['1'];
                            if ($fromEmailDomain == $xmlRow['domain']) {
                                $signerRow = $xmlRow;
                                break;
                            }
                        }
                    } else {
                        throw new \Exception($extKey . ': The DKIM Signer XML file "' . $absFilename . '" is not XML valid.');
                    }
                } else {
                    throw new \Exception($extKey . ': The DKIM Signer XML file "' . $absFilename . '" has an invalid extension.');
                }
            }

            if (
                isset($signerRow) &&
                is_array($signerRow) &&
                isset($signerRow['privateKeyFile']) &&
                isset($signerRow['selector'])
            ) {
                $signerFilename =
                    GeneralUtility::resolveBackPath(
                        Environment::getLegacyConfigPath() . '../' . $signerRow['privateKeyFile']
                    );

                $absFilename = GeneralUtility::getFileAbsFileName($signerFilename);
                $handle = fopen($absFilename, 'rt');
                if ($handle === false) {
                    throw new \Exception(DIV2007_EXT . ': Signer file not found ("' . $absFilename . '")');
                }

                if (class_exists(DkimSigner::class)) {
                    $signer = GeneralUtility::makeInstance(
                        DkimSigner::class,
                        $absFilename,
                        $signerRow['domain'],
                        $signerRow['selector']
                    );
                    $mail = $signer->sign($mail);
                } else {
                    throw new \RuntimeException('Extension ' . DIV2007_EXT . ' MailUtility: no mail signer class found.', 1612340604
                    );
                }
            }

            if (
                method_exists($mail, 'send') &&
                method_exists($mail, 'isSent') &&
                !$mail->isSent()
            ) {
                try {
                    $resultSend = $mail->send(); // TODO: debug and test mode to not send an email
                    if (
                        (
                            $debug == 'DEBUG_AND_SEND' ||
                            $debug == 'DEBUG'
                        ) &&
                        is_object($resultSend) &&
                        $resultSend instanceof SentMessage
                    ) {
                        debug($resultSend->getOriginalMessage(), 'MailUtility::send original message'); // keep this
                        debug($resultSend->getDebug(), 'MailUtility::send debug'); // keep this
                    }
                    $result = $mail->isSent();
                    if (!$result) {
                        debug('MailUtility::send exited with error 6'); // keep this
                        $undelivered = $mail->getFailedRecipients();
                        if (is_array($undelivered)) {
                            debug('MailUtility::send undelivered: ' . implode(',', $undelivered)); // keep this
                        }
                    }
                } catch (Exception $e) {
                    if ($e instanceof TransportException) {
                        debug($e->getDebug(), 'MailUtility::send Exception debug'); // keep this
                    }
                }
            } else {
                // This must never be reached:
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Embeds media into the mail message.
     *
     * @return string the subtituted HTML content
     */
    public static function embedMedia(
        MailMessage $mail,
        $htmlContent
    ) {
        $substitutedHtmlContent = $htmlContent;
        if (is_object($GLOBALS['TYPO3_REQUEST'])) {
            $normalizedParams = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams');
        }
        $media = [];
        $attribRegex = static::makeTagRegex(['img', 'embed', 'audio', 'video']);
        // Split the document by the beginning of the above tags
        $codepieces = preg_split($attribRegex, $htmlContent);
        $len = strlen($codepieces[0]);
        $pieces = count($codepieces);
        $reg = [];
        for ($i = 1; $i < $pieces; $i++) {
            $tag = strtolower(strtok(substr($htmlContent, $len + 1, 10), ' '));
            $len += strlen($tag) + strlen($codepieces[$i]) + 2;
            $dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
            // Fetches the attributes for the tag
            $attributes = static::getTagAttributes($reg[0]);
            if ($attributes['src'] != '' && $attributes['src'] != 'clear.gif') {
                $httpDomain = '';
                if (is_object($normalizedParams)) {
                    $httpDomain = $normalizedParams->getSiteUrl();
                } else {
                    $httpDomain = GeneralUtility::getIndpEnv('REQUEST_URI');
                }
                $filename = str_replace($httpDomain, '', $attributes['src']);
                $key = basename($filename);
                $j = '';
                while (isset($media[$key . $j]) && $j < 30) {
                    $j = intval($j);
                    $j++;
                }
                $media[$key] = $filename;
            }
        }

        foreach ($media as $key => $filename) {
            $embedded = '';
            $source = ltrim($filename, '/');
            $mail->embedFromPath(Environment::getPublicPath() . '/' . $source, $key);
            $embedded = 'cid:' . $key;

            $substitutedHtmlContent = str_replace(
                '"' . $filename . '"',
                '"' . $embedded . '"',
                $substitutedHtmlContent
            );
        }

        return $substitutedHtmlContent;
    }

    /**
     * Creates a regular expression out of an array of tags.
     *
     * @return	string		the regular expression
     */
    public static function makeTagRegex(array $tags)
    {
        $regexpArray = [];
        foreach ($tags as $tag) {
            $regexpArray[] = '<' . $tag . '[[:space:]]';
        }

        return '/' . implode('|', $regexpArray) . '/i';
    }

    /**
     * This function analyzes a HTML tag
     * If an attribute is empty (like OPTION) the value of that key is just empty. Check it with is_set();.
     *
     * @return array array with attributes as keys in lower-case
     */
    public static function getTagAttributes($tag)
    {
        $attributes = [];
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

    /**
     * This function checks if the corresponding DNS has a valid MX record.
     *
     * @return	bool		true if a MX record has been found
     */
    public static function checkMXRecord($email)
    {
        if ($email != '' && !GeneralUtility::validEmail($email)) {
            return false;
        }

        // gets domain name
        [$username, $domain] = explode('@', $email);
        // checks for if MX records in the DNS
        $mxhosts = [];
        if (!getmxrr($domain, $mxhosts)) {
            // no mx records, ok to check domain
            if (@fsockopen($domain, 25, $errno, $errstr, 30)) {
                return true;
            } else {
                return false;
            }
        } else {
            // mx records found
            foreach ($mxhosts as $host) {
                if (@fsockopen($host, 25, $errno, $errstr, 30)) {
                    return true;
                }
            }

            return false;
        }
    }
}
