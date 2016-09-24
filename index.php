<?php
/**
 *	Simple GPG Key manager, to display or allow key file download (must be ASCII armored!)
 *	by HacKan GNU GPL v3.0+
 *
 *	v1.0
 */
/**
 * Settings file as config/config.json is a JSON formatted file, in the form of:
 *
 * {
 *   "options": {
 *       "show_help": false,
 *       "show_keys": false,
 *       "default_first_key": false
 *   },
 *   "keys": {
 *       "keyidX": "keys/keyfilenameX.asc",
 *   }
 * }
 *
 * Options:
 * 	show_help (true/false): help message when wrong parameter or value.
 *	show_keys (true/false): show valid key values when showing help.
 *  default_first_key (true/false): if no key selected, then show the first key.
 * 
 * All options are optional (can be missing), and have a false value by default.
 *
 * Keys:
 * 	keyids must be written in uppercase, and must be composed of hex characters only! (GPG default)
 *
 * If file name changed, rewrite .htaccess to secure it. If not secured, it could be read by user.
 * Note that *if* keyfiles name are easy to guess, direct download could be forced by user...
 */

error_reporting(E_ALL);

require_once 'keymanager.class.php';

/* MAIN */

try {
    $keymager = new KeyManager('config/config.json');
} catch (Exception $e) {
    die(KeyManager::output('Error', $e->getMessage()));
}

$response = '';

// GET
$keymager->setDownloadFlag(isset($_GET['d']));
$keymager->setHelpFlag(isset($_GET['h']));
$keymager->setKeyid(
    isset($_GET['k']) 
    ? filter_input(INPUT_GET, 'k', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) 
    : ''
);

exit($keymager->run());