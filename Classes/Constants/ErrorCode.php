<?php

namespace JambageCom\Div2007\Constants;

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
 * Constants for the fields
 */
class ErrorCode
{
    const SECURITY_RSA_AUTH_FAILED_INCOMING = 'rsaauth_process_incoming_field_failed';
    const SECURITY_RSA_AUTH_FAILED_PRIVATE_KEY = 'rsaauth_retrieve_private_key_failed';
    const SECURITY_RSA_AUTH_BACKEND_NOT_AVAILABLE = 'rsaauth_backend_not_available'
    const SECURITY_RSA_AUTH_DECRYPTION_FAILED = 'rsaauth_decrypt_auto_login_failed';
}

