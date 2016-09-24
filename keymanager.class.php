<?php

/**
 *	Simple GPG Key manager, to display or allow key file download 
 * (must be ASCII armored!)
 *	by HacKan GNU GPL v3.0+
 *
 *	v1.0
 */
class KeyManager
{
    /**
     * Structure for config array
     */
	const CONFIGSTRUCT = [
        'options' => [
            'show_help' => false,
            'show_keys'=> false,
            'default_first_key' => false,
        ],
        'keys' => [],
    ];

	const OUTPUT_TYPE_TEXT = 1;
	const OUTPUT_TYPE_FILE = 2;

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
     * @var array Options (Options key from the Config)
     */
    private $Options = [];
    
    /**
     *
     * @var array Keys (Keys key from the Config)
     */
    private $Keys = [];
    
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

            return $settings;
		}
        
        return [];
	}
    
    protected function getUsageMessage()
    {
        $message = "Usage: " . PHP_EOL
                . "\tDisplay key: ?k=<keyId>" . PHP_EOL
                . "\tDownload key: ?k=<keyId>&d" . PHP_EOL;

        if ($this->Options['show_keys']) {
            $message .= PHP_EOL . "Valid key ids: " . PHP_EOL;
            $keyids = array_keys($this->Keys);
            foreach ($keyids as $k) {
                $message .= "\t" . $k . PHP_EOL;
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
        } else {
            throw new Exception('Config file not found or not valid.');
        }
    }
    
    public function setKeyid($keyid)
    {
        $this->Keyid = ctype_xdigit($keyid) 
            ? strtoupper($keyid) 
            : '';
    }
    
    public function setDownloadFlag($downloadflag)
    {
        $this->DownloadFlag = (bool) $downloadflag;
    }
    
    public function setHelpFlag($helpflag)
    {
        $this->HelpFlag = (bool) $helpflag;
    }
    
    public function run()
    {
        if ($this->HelpFlag && $this->Options['show_help']) {
            return self::output('Help', $this->getUsageMessage());
        } 
        
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
                return self::output(
                    'gpg_key', 
                    file_get_contents($keyfile), 
                    $this->DownloadFlag 
                        ? self::OUTPUT_TYPE_FILE 
                        : self::OUTPUT_TYPE_TEXT
                );
            } else {
                return self::output(
                    'Error', 
                    "File: " . $keyfile . " doesn't exist!" . PHP_EOL . PHP_EOL
                );
            }
        }
        
        return self::output(
            'Error', 
            'Invalid options or key id.' . PHP_EOL . PHP_EOL
            . ($this->Options['show_help'] ? $this->getUsageMessage() : '')
        );
    }
}