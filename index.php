<?php
/**
 * Simple GPG Key manager, to display or allow key file download (must be ASCII armored!)
 * by HacKan GNU GPL v3.0+
 *
 * Example ready-to-use index file
 * v1.3.4
 *
 * ----------------------------------------------------------------------------
 *     Copyright (C) 2017 HacKan (https://hackan.net)
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------------
 *
 */

/*
 * The settings directory should be renamed to something random to keep
 * users from accessing it.
 * Settings file, as config.json, is a JSON formatted file, in the form of:
 *
 * {
 *   "options": {
 *       "show_help": false,
 *       "show_history": false,
 *       "show_keys": false,
 *       "default_first_key": false
 *   },
 *   "keys": {
 *       "keyidX": "keys/keyfilenameX.asc",
 *       ...
 *   },
 *   "history" : "history.asc"
 * }
 *
 * Options:
 *  show_help (true/false): show help message when wrong parameter or value is issued.
 *  show_history (true/false): enables showing history when requested.
 *  show_keys (true/false): show valid key id values when showing help.
 *  default_first_key (true/false): if no key selected, then show the first key.
 * 
 * All options are optional (can be missing), and have a false value by default.
 *
 * Keys:
 *  keyids must be written in uppercase, and must be composed of hex characters only! (GPG default)
 *
 * History:
 *  The optional key history file, can be any type of file
 *
 * If file name changed, rewrite .htaccess to secure it. If not secured, it could be read by user.
 * Note that *if* keyfiles name are easy to guess, direct download could be forced by user...
 */

error_reporting(E_ALL);

// Use some random name for the config dir, such as config-4752b464
$configDir = 'config';

require_once 'HC' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

/* MAIN */

try {
    // Use full path to settings file!
    $keymanager = new \HC\OpenPGP\KeyManager(__DIR__ . DIRECTORY_SEPARATOR . $configDir . DIRECTORY_SEPARATOR . 'config.json');
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Set data from user (options and keyid)
// using filter_input with INPUT_SERVER fails on certain host configurations, returning null; using filter_var is exactly the same and always works
$keymanager->parseRequest(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));

exit($keymanager->run());
