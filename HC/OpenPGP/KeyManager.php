<?php
namespace HC\OpenPGP;

/**
 *    Simple GPG Key manager, to display or allow key file download 
 *    (must be ASCII armored!)
 *    by HacKan GNU GPL v3.0+
 *
 *    v2.1.9
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
class KeyManager
{
    /**
     * Structure for config array
     */
    const CONFIGSTRUCT = [
        'options' => [
            'show_help' => false,
            'show_history' => false,
            'show_keys'=> false,
            'default_first_key' => false,
        ],
        'keys' => [],
        'history' => ''
    ];

    const OUTPUT_TYPE_TEXT = 1;
    const OUTPUT_TYPE_FILE = 2;

    /**
     * @var string Full path configuration directory (extracted from $configfile)
     */
    protected $ConfigDir = '';

    /**
     *
     * @var string User provided KeyID
     */
    private $Keyid = '';
    
    /**
     *
     * @var bool User provided Download Flag
     */
    private $DownloadFlag = false;
    
    /**
     *
     * @var bool User provided Help Flag
     */
    private $HelpFlag = false;

    /**
     *
     * @var bool User provided Show History Flag
     */
    private $ShowHistoryFlag = false;

    /**
     *
     * @var array Options (Options key from the Config)
     */
    private $Options = [];
    
    /**
     *
     * @var array Keys (Keys key from the Config)
     */
    private $Keys = [];

    /**
     *
     * @var string History file name (inside Config dir)
     */
    private $History = '';
    
    protected function readConfig($configfile)
    {
        if (file_exists($configfile) 
            && ($settings = json_decode(file_get_contents($configfile), true))
        ) {
            // NetBeans 8.1 shows error here but, worry not, the sentence is fine.
            $settings['options'] = isset($settings['options'])
                ? array_merge(self::CONFIGSTRUCT['options'], $settings['options'])
                : self::CONFIGSTRUCT['options'];
            
            $settings['keys'] = isset($settings['keys']) ? $settings['keys'] : [];

            $settings['history'] = isset($settings['history']) ? $settings['history'] : '';

            return $settings;
        }
        
        return [];
    }
    
    protected function getUsageMessage()
    {
        $message = "Usage: " . PHP_EOL
                . "\tDisplay key:\t\t/key/<key id>" . PHP_EOL
                . "\tDownload key:\t\t/key/<key id>?d" . PHP_EOL
                . "\tShow keys history:\t/history" . PHP_EOL
                . "\tDownload keys history:\t/history?d" . PHP_EOL;

        if (!empty($this->Keys)) {
            if ($this->Options['show_keys']) {
                $message .= PHP_EOL . "Valid key ids: " . PHP_EOL;
                $keyids = array_keys($this->Keys);
                foreach ($keyids as $k) {
                    $message .= "\t" . $k . PHP_EOL;
                }
            }

            if ($this->Options['default_first_key']) {
                $message .= PHP_EOL . "Default key id: " . PHP_EOL
                    . "\t" . array_keys($this->Keys)[0] . PHP_EOL;
            }
        }

        return $message;
    }
    
    protected static function outputHeaders(
        $description, 
        $size, 
        $outputType = self::OUTPUT_TYPE_TEXT
    ) {
        header('Content-Description: ' . $description);
        
        switch ($outputType) {
            case self::OUTPUT_TYPE_FILE:
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $description . '"');
                break;

            default:
                header('Content-Type: text/plain; charset=utf-8');
                header('Content-Disposition: inline');
                break;
        }
        
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $size);
    }
    
    public static function output($description, $content, $type = self::OUTPUT_TYPE_TEXT)
    {
        self::outputHeaders($description, strlen($content), $type);

        return $content;
    }

    public function __construct($configfile = '')
    {
        $this->setConfig($configfile);
    }
    
    public function setConfig($configfile)
    {
        $settings = $this->readConfig($configfile);
        
        if (isset($settings['options']) 
            && isset($settings['keys'])
        ) {
            $this->Options = $settings['options'];
            $this->Keys = $settings['keys'];
            $this->ConfigDir = dirname($configfile);
            $this->History = $this->ConfigDir . DIRECTORY_SEPARATOR . $settings['history'];
        } else {
            throw new \Exception('Config file not found or not valid.');
        }
    }
    
    public function setKeyid($keyid)
    {
        if (ctype_xdigit($keyid)) {
            $this->Keyid = strtoupper($keyid);
        }
    }
    
    public function setDownloadFlag($downloadflag)
    {
        $this->DownloadFlag = (bool) $downloadflag;
    }
    
    public function setHelpFlag($helpflag)
    {
        $this->HelpFlag = (bool) $helpflag;
    }
    
    public function setShowHistoryFlag($showhistoryflag)
    {
        $this->ShowHistoryFlag = (bool) $showhistoryflag;
    }

    public function parseRequest($requestURL)
    {
        if (!empty($requestURL)) {
            // If behind proxy, REQUEST_URI might deliver full URL, remove domain and protocol
            $requestURL = preg_replace(
                '/^https?:\/\/.*?\//',
                '/',
                filter_var($requestURL, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH),
                1
            );

            // Show help if requested, or if URL is not valid 
            $this->setHelpFlag(
                (preg_match('/.*?[\?&]h(&|=|$)/', $requestURL) === 1) 
                ?: (preg_match('/^\/(k(ey)?(\/[a-fA-F0-9]{0,40}|\/)?\??d?|history[\?\/]?|\?(d&)?k(=[a-fA-F0-9]{0,40}|=)?(&d)?)?$/', $requestURL) != 1)
            );

            $this->setDownloadFlag((preg_match('/.*?[\?&]d(&|=|$)/', $requestURL) === 1));
            $this->setShowHistoryFlag((preg_match('/^\/history[\?\/]?$/', $requestURL) === 1));

                                                                    // Compatibility with old version key url
            if (preg_match('/^\/(k(ey)?\/[a-fA-F0-9]{1,40}\??d?|\?(d&)?k=[a-fA-F0-9]{1,40}(&d)?)$/', $requestURL) === 1) {
                $this->setKeyid(str_replace(['key', '?k=', '&k=', 'k', '/', '?d', '&d', '?'], '', $requestURL));
            }
        }
    }
    
    public function run()
    {
        $data = 'Unknown error';
        $desc = 'error';

        if ($this->Options['show_help'] && $this->HelpFlag) {
            $data = $this->getUsageMessage();
            $desc = 'help';
        } elseif ($this->Options['show_history'] && $this->ShowHistoryFlag) {
            $data = file_exists($this->History)
                ? file_get_contents($this->History)
                : 'Error: No history file!' . PHP_EOL . PHP_EOL;
            $desc = 'gpg_history';
        } else {
            $keyid = empty($this->Keyid)
                ? (
                    ($this->Options['default_first_key'] && !empty($this->Keys))
                        ? array_keys($this->Keys)[0]
                        : ''
                )
                : $this->Keyid;

            if (array_key_exists($keyid, $this->Keys)) {
                $keyfile = $this->Keys[$keyid];
                if (file_exists($keyfile)) {
                    $data = file_get_contents($keyfile);
                    $desc = $keyid . '.asc';
                } else {
                    $data = "Error: File corresponding to ID $keyid doesn't exist!" . PHP_EOL . PHP_EOL;
                }
            } else {
                $data = 'Error: Invalid options or key id.' . PHP_EOL . PHP_EOL
                    . ($this->Options['show_help'] ? $this->getUsageMessage() : '');
            }
        }
        
        
        return self::output(
            $desc, 
            $data, 
            $this->DownloadFlag 
                ? self::OUTPUT_TYPE_FILE 
                : self::OUTPUT_TYPE_TEXT
        );
    }
}
