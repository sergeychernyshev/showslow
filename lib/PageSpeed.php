<?php


/**
  *
  * @package 
  * @author  Elizabeth Cholet <elcholet.ext@orange.com>
  *
  */
 
require_once (dirname(dirname(__FILE__)).'/global.php'); //db connection here
require_once (dirname(__FILE__).'/PageSpeedRule.php');
 
/**
  * PageSpeed encapsulates the results of a single PageSpeed analysis.
  *
  * It processes both full and minimal beacons and provides methods for
  * database CRUD actions.
  *
  */
class PageSpeed {
    
    public $id;
    public $url;
    public $urlId;
    public $ip;
    public $version;
    public $userAgent;
    
    public $resultsFormatVersion;
    public $pagespeedVersion;
    public $firefoxVersion;
    public $firebugVersion;
    
    public $versions;
    public $pageLoadTime; 
    public $numRequests; 
    public $pageSize; 
    public $transferSize;
    public $overallScore;
    
    public $timestamp;
    
    public $mysql_error;
    public $asset_url_ids = array();
    
    private $is_full         = false;
    private $has_asset_table = false;
    public $rules            = array();
    
    public static $metric_rule_map = array(
	'pBadReqs'                => 'AvoidBadRequests',
	'pBrowserCache'           => 'LeverageBrowserCaching',
	'pCacheValid'             => 'SpecifyACacheValidator',
	'pCharsetEarly'           => 'SpecifyCharsetEarly',
	'pCombineCSS'             => 'CombineExternalCSS',
	'pCombineJS'              => 'CombineExternalJavaScript',
	'pCssImport'              => 'AvoidCssImport',
	'pCssInHead'              => 'PutCssInTheDocumentHead',
	'pCssJsOrder'             => 'OptimizeTheOrderOfStylesAndScripts',
	'pDocWrite'               => 'AvoidDocumentWrite',
	'pDupeRsrc'               => 'ServeResourcesFromAConsistentUrl',
	'pGzip'                   => 'EnableGzipCompression',
	'pImgDims'                => 'SpecifyImageDimensions',
	'pMinDns'                 => 'MinimizeDnsLookups',
	'pMinifyCSS'              => 'MinifyCss',
	'pMinifyHTML'             => 'MinifyHTML',
	'pMinifyJS'               => 'MinifyJavaScript',
	'pMinRedirect'            => 'MinimizeRedirects',
	'pMinReqSize'             => 'MinimizeRequestSize',
	'pNoCookie'               => 'ServeStaticContentFromACookielessDomain',
	'pOptImgs'                => 'OptimizeImages',
	'pParallelDl'             => 'ParallelizeDownloadsAcrossHostnames',
	'pPreferAsync'            => 'PreferAsyncResources',
	'pRemoveQuery'            => 'RemoveQueryStringsFromStaticResources',
	'pScaleImgs'              => 'ServeScaledImages',
	'pSprite'                 => 'SpriteImages',
	'pVaryAE'                 => 'SpecifyAVaryAcceptEncodingHeader',
	'pDeferParsingJavaScript' => 'DeferParsingJavaScript',
	'pEnableKeepAlive'        => 'EnableKeepAlive',
	'pInlineCSS'              => 'InlineSmallCss',
	'pInlineJS'               => 'InlineSmallJavaScript',
	'pMakeLandingPageRedirectsCacheable' => 'MakeLandingPageRedirectsCacheable',
	'pCssSelect'              => 'UseEfficientCSSSelectors',
	'pDeferJS'                => 'DeferLoadingOfJavaScript',
	'pUnusedCSS'              => 'RemoveUnusedCSS'
    );
    
    // Support metric names from previous versions of PageSpeed.
    public static $legacy_rule_names = array(
        'pSpecifyCharsetEarly'                     => 'pCharsetEarly',
        'pProxyCache'                              => 'pCacheValid',
        'pPutCssInTheDocumentHead'                 => 'pCssInHead',
        'pOptimizeTheOrderOfStylesAndScripts'      => 'pCssJsOrder',
        'pMinimizeRequestSize'                     => 'pMinReqSize',
        'pParallelizeDownloadsAcrossHostnames'     => 'pParallelDl',
        'pServeStaticContentFromACookielessDomain' => 'pNoCookie',
        'pAvoidBadRequests'                        => 'pBadReqs',
        'pLeverageBrowserCaching'                  => 'pBrowserCache',
        'pRemoveQueryStringsFromStaticResources'   => 'pRemoveQuery',
        'pServeScaledImages'                       => 'pScaleImgs',
        'pSpecifyACacheValidator'                  => 'pCacheValid',
        'pSpecifyAVaryAcceptEncodingHeader'        => 'pVaryAE',
        'pSpecifyImageDimensions'                  => 'pImgDims'
    );
    
    function __construct() {
        
        $args = func_get_args();
        $num  = func_num_args();
        
        if ($num == 0 || $args == null) { return; }
        
        //Parse those args:IP address and database connection.
        for ($i = 0; $i < sizeof($args); $i++) {
            if (filter_var($args[$i], FILTER_VALIDATE_IP)) {
                $ary   = array_splice($args, $i, 1);
                $this->ip = $ary[0]; 
            }
        }
        
        $type = gettype($args[0]);
        
        if ($type == 'string') {
           $this->__construct_from_full($args[0]); 
        }
        elseif ($type == 'array' && $array_key_exists('u', $args[0])) {
           $this->__construct_from_minimal($args[0]);
        }
        
        $query  = "SHOW TABLES FROM " . $GLOBALS['db'] . " LIKE 'pagespeed_asset_urls'";
        $result = mysql_query($query);
        $row    = mysql_fetch_row($result);
        
        if ($row[0] != null) {
            $this->has_asset_table = true;
        }
    }
    
    function __construct_from_full($string = null, $ip = null) {
        
        $this->is_full = true;
        
        if ( $string == null)  {
            error_log('PageSpeed::constructor:  no json string');
            return;
        }
        
        $json = json_decode($string, true); //return as assoc. array
        
        if ( $json == null)  {
            error_log('PageSpeed::constructor:  no json ');
            return;
        }

        $this->resultsFormatVersion = $json['resultsFormatVersion'];
        $this->pagespeedVersion     = $json['versions']['pageSpeed'];
        $this->firefoxVersion       = $json['versions']['firefox'];
        $this->firebugVersion       = $json['versions']['firebug'];
        $this->userAgent            = $json['versions']['userAgent'];
        
        $stats = $json['pageStats'];
        
        $this->url = filter_var($stats['initialUrl'], FILTER_VALIDATE_URL); 

        //Use the existing function defined in globals.php
        $this->urlId = getUrlId($this->url);
        
        $this->pageLoadTime = filter_var($stats['pageLoadTime'], FILTER_VALIDATE_INT);
        $this->numRequests  = filter_var($stats['numRequests'],  FILTER_VALIDATE_INT);
        $this->pageSize     = filter_var($stats['pageSize'],     FILTER_VALIDATE_INT);
        $this->transferSize = filter_var($stats['transferSize'], FILTER_VALIDATE_INT);
        $this->overallScore = filter_var($stats['overallScore'], FILTER_VALIDATE_INT);
        
        foreach ($json['rules'] as $rule) {
            $this->rules['p' . $rule['shortName']] =  new PageSpeedRule($rule);
        }
    }
    
    function __construct_from_minimal ($results = null) {
        
        $this->is_full = false;
        
        $this->version = $results['v'];
        $this->url     = filter_var($results['u'], FILTER_VALIDATE_URL);
        
        //Use the existing function defined in globals.php
        $this->urlId= getUrlId($this->url);
        
        $req_hdr         = http_get_request_headers();
        $this->userAgent = $req_hdr['User-Agent'];
        
	$this->pageLoadTime = filter_var($results['l'], FILTER_VALIDATE_INT);
	$this->overallScore = filter_var($results['o'], FILTER_VALIDATE_INT);
	$this->numRequests  = filter_var($results['r'], FILTER_VALIDATE_INT);
	$this->transferSize = filter_var($results['t'], FILTER_VALIDATE_INT);
	$this->pageSize     = filter_var($results['w'], FILTER_VALIDATE_INT);
        
        foreach ($results as $name =>  $value) {
            
            if (array_key_exists($name, $this->legacy_rule_names))  {
                $name = $this->legacy_rule_names[$name];
            }
            
            if (array_key_exists($name, self::$metric_rule_map))  {
                $this->rules[$name] =  new PageSpeedRule($results[$name]);
            }
        }
    }
    
    function getRuleMetricMap (){
	return array_flip(self::$metric_rule_map);
    }
    
    function getRule($rulename) {
        
        if (array_key_exists($rulename, $this->rules)) {
            return $this->rules[$rulename];
        }
        else {
            return null;
        }
    }
    
    function save () {
          
        if ($this->ip == null) {
            error_log('PageSpeed::save: no remote_addr');
            return 0;
        }
        elseif ( $this->urlId == null) {
            error_log('PageSpeed::save:  no url id');
            return 0 ;
        }
        
        $rules = array();
        
        foreach (self::$metric_rule_map as $name => $rule) {
            if (array_key_exists($name, $this->rules)) {
                $rule = $this->rules[$name];
                if ($rule->score) {
                    $rules[$name]  = "'" . mysql_real_escape_string($rule->score) ."'";
                }
            }
        }
	
        $query = sprintf(
        "INSERT INTO pagespeed (
            `ip`,
            `user_agent`,
            `url_id`,
            `v`,
            `w`,
            `o`,
            `l`,
            `r`,
            `t`,
            %s
        )", implode(",\n ", array_keys($rules)));
        
        $query .= sprintf(" VALUES (
                inet_aton('%s'),
                '%s',
                '%d',
                '%s',
                '%d',
                '%f',
                '%d',
                '%d',
                '%d',
                %s
            )",
            mysql_real_escape_string($this->ip),
            mysql_real_escape_string($this->userAgent),
            mysql_real_escape_string($this->urlId),
            mysql_real_escape_string($this->pagespeedVersion),
            mysql_real_escape_string($this->pageSize),
            mysql_real_escape_string($this->overallScore),
            mysql_real_escape_string($this->pageLoadTime),
            mysql_real_escape_string($this->numRequests),
            mysql_real_escape_string($this->transferSize),
            implode(",\n ", array_values($rules))
        );
        
        $result = mysql_query($query);
        
        if (! $result ) {
	    $this->mysql_error = mysql_error();
            error_log('PageSpeed::save: ' . $this->mysql_error);
            return 0;
        }
        
        $this->id = mysql_insert_id();
	
	$this->setLastUpdate();  # same as updateUrlAggregates
	
        if ($this->has_asset_table) {
            return $this->saveAssetUrls( $this->id );
        }
	else {
	    return $result;
	}
    }
    
    function saveAssetUrls ($pagespeed_id) {
        
	$asset_url_insert = "INSERT IGNORE INTO asset_urls (url, url_md5) VALUES ";
        $pagespeed_asset_url_insert = "INSERT INTO pagespeed_asset_urls (
            `pagespeed_id`,
            `asset_id`,
            `rule`
        ) VALUES ";
        
        foreach ($this->rules as $name => $rule) {
            
            if ($rule->warnings) {
                
                $asset_urls = $rule->extract_urls();
                
                foreach ($asset_urls as $url) {
                    
		    # insert into asset_urls
		    $url = mysql_real_escape_string($url);
		    
                    $query =
		        $asset_url_insert
		      . sprintf("('%s', UNHEX(MD5('%s')))", $url, $url )
		    ;
                    $result = mysql_query($query);
                    
                    if (! $result ) {
			$this->mysql_error = mysql_error();
                        error_log('PageSpeed::saveAssetUrls : ' .  $this->mysql_error);
			return $result;
                    }
		    
		    # make a linked record in pagespeed_asset_urls
		    $query = sprintf("SELECT id FROM asset_urls WHERE url_md5 = UNHEX(MD5('%s'))", $url);
		    $result = mysql_query($query);
		    $row = mysql_fetch_row($result);
		    $asset_url_id = $row[0];
		    
		    $ps_query =
		        $pagespeed_asset_url_insert
	              . sprintf("(%d, %d, '%s')", $this->id, $asset_url_id, $name)
		    ;
		    
                    $result = mysql_query($ps_query);
                    
                    if (! $result ) {
			$this->mysql_error = mysql_error();
                        error_log('PageSpeed::saveAssetUrls : ' . $this->mysql_error);
   		        return $result;
                    }
                }
            }
        }
	return 1;
    }
    
    function setLastUpdate() {
	
        $query = sprintf("
            UPDATE urls
            SET pagespeed_last_id = %d,
                last_update       = now(),
                p_refresh_request = 0
            WHERE id = %d",
            mysql_real_escape_string($this->id),
            mysql_real_escape_string($this->urlId)
        );
            
        $result = mysql_query($query);
        if (! $result ) {
	    $this->mysql_error = mysql_error();
            error_log('PageSpeed::setLastUpdate: ' . $this->mysql_error);
        }
    }
    
    function delete (){
        if (! $this->id ) {
            # error
            return;
        }
        $query = sprintf(
            'DELETE from pagespeed WHERE id = %d',
            mysql_real_escape_string($this->id)
        );
        
        mysql_query($query);
        if (! $result ) {
            error_log('PageSpeed::delete : ' . mysql_error());
        }
        
        if ($this->has_asset_table) {
            $query =  sprintf(
                'DELETE  from pagespeed_asset_urls WHERE pagespeed_id = %d',
                mysql_real_escape_string($this->id)
            );
            mysql_query($query);
            if (! $result ) {
                error_log('PageSpeed::delete : ' . mysql_error());
            }
        }
    }
}
?>
