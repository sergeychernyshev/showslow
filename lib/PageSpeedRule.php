<?php
/**
  *
  * @package PageSpeedRule
  * @author  Elizabeth Cholet <elcholet.ext@orange.com>
  * 
  * The Tidy package must be installed and enabled.
  * Debian/Ubuntu: apt-get install php5-tidy
  *
  */
 
/**
  *
  */

class PageSpeedRule {
    public $name;
    public $shortName;
    public $score;
    public $warnings;
    public $information;
    public $statistics;
    
    function __construct() {
        // Constructor can take:
        // A JSON string,
        // Decoded JSON in associative-array form,
        // or a key value pair.
        
        $num  = func_num_args();
        $args = func_get_args();
        
        $json;
        
        if ($num == 0) { return; }
        
        if  ($num == 2) {
            // Assume a key-value pair.
            $this->shortName = $args[0]; // TODO: Validate
            $this->score     = filter_var($args[1], FILTER_VALIDATE_INT);
        }
        elseif ($json = PageSpeedRule::is_full_beacon($args[0])) {
            
            $this->name         = $json['name'];
            $this->shortName    = $json['shortName'];
            $this->score        = filter_var($json['score'], FILTER_VALIDATE_INT);
            $this->warnings     = $json['warnings'];
            $this->information  = $json['information'];
            $this->statistics   = $json['statistics'];
        }
        else {
            // throw an error here
        }
        
    }
    
    function iterateVisible() {
        foreach($this as $key => $value) {
           print "$key => $value\n";
        }
    }
    
    function is_full_beacon($arg) {
        
        $json;
        
        $type = gettype($arg);
        
        if ($type == "string") {
            // try decoding
            $json = json_decode($arg, 1);
        }
        elseif ($type == 'array') {
            //assume it's JSON
            $json = $arg;
        }
        
        if ($type == 'array'
            && array_key_exists('name',       $json)
            && array_key_exists('shortName',  $json)
            && array_key_exists('statistics', $json)
        ) {
            return $json;
        }
        else {
            return null;
        }
    }
    
    function extract_urls () {
        
        if ($this->warnings == null) { return array(); }
        
        $tidy = new tidy;
        $tidy->parseString($this->warnings);
        $tidy->cleanRepair();
        
        try {
            $dom   = DOMDocument::loadHTML("$tidy");
        }
        catch (Exception $e) {
            error_log($this->shortName . ": Cannot parse urls: " . $e->getMessage());
            return array();
        };
        
        $nodes = $dom->getElementsByTagName('a');
                    
        foreach ($nodes as $node) {
            $url = $node->getAttribute('href');
            if (preg_match('!^https?://!', $url)) {
                $urls[] = $url;
            }
        }
        return $urls;
    }
}

?>

