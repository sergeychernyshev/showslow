<?php
    require_once 'PHPUnit/Framework/TestCase.php';
    require_once (dirname(dirname(__FILE__)).'/global.php');
    require_once (dirname(dirname(__FILE__)).'/lib/PageSpeed.php');
    
    class PageSpeedTest extends PHPUnit_Framework_TestCase
    {
        private $db_ids;
        
        public function testNewUninit() {
            $ps = new PageSpeed;
            $this->assertInstanceOf('PageSpeed', $ps);
        }
        
        public function testNewFromJsonString() {
            
            $jsonfiles = dirname(__FILE__) . '/pagespeed_*.json';
            
            foreach (glob($jsonfiles) as $file) {
                $string = file_get_contents($file); 
                $ps     = new PageSpeed($string);
                $this->assertProperties($ps, $string);
            }
        }
        
        public function testSaveToDbMissingInfo() {
            
            $tmperr = './error.log';
            $jsonfiles = dirname(__FILE__) . '/pagespeed_*.json';
            
            foreach (glob($jsonfiles) as $file) {
                $string = file_get_contents($file); //JSON string
                
                ini_set('error_log', $tmperr);
                
                $ps = new PageSpeed($string);
                $this->assertInstanceOf('PageSpeed', $ps);
                
                $retval = $ps->save();
                
                $msg = file_get_contents($tmperr);
                $this->assertRegExp('!PageSpeed::save: no remote_addr!', $msg);
                unlink($tmperr);
                ini_restore('error_log');
                
                $this->assertEquals(0, $retval);
                $this->assertNull($ps->id);
            }
        }
        
        public function testSaveToDb() {
            
            $jsonfiles = dirname(__FILE__) . '/pagespeed_*.json';
            
            $db_ids = array();
            
            foreach (glob($jsonfiles) as $file) {
                $string = file_get_contents($file); //JSON string
                
                $ps = new PageSpeed($string, '127.0.0.1');
                $this->assertProperties($ps, $string);
                
                $ps_id = $ps->save();
                
                $this->assertGreaterThan(0, $ps_id);
                $this->assertNotNull($ps_id);
                //$db_ids[] = $ps_id;
            }
            //return $db_ids;
        }
        
        
        private function assertProperties ($ps, $json_string) {
            
            $this->assertInstanceOf('PageSpeed', $ps);
            
            $json = json_decode($json_string, true);
            $v    = $json['versions'];
            $s    = $json['pageStats'];
            
            $this->assertAttributeEquals(
                $json['resultsFormatVersion'],
                'resultsFormatVersion',
                $ps
            );
            
            $this->assertAttributeEquals($v['pageSpeed'],   'pagespeedVersion', $ps);
            $this->assertAttributeEquals($v['firefox'],     'firefoxVersion',   $ps);
            $this->assertAttributeEquals($v['firebug'],     'firebugVersion',   $ps);
            $this->assertAttributeEquals($v['userAgent'],   'userAgent',        $ps);
            $this->assertAttributeEquals($s['initialUrl'],  'sourceUrl',       $ps);
            $this->assertAttributeEquals($s['url'],         'destinationUrl',  $ps);
            $this->assertAttributeEquals($s['pageSize'],    'pageSize',         $ps);
            $this->assertAttributeEquals($s['overallScore'],'overallScore',     $ps);
            $this->assertAttributeEquals($s['pageLoadTime'],'pageLoadTime',     $ps);
            $this->assertAttributeEquals($s['numRequests'], 'numRequests',      $ps);
            $this->assertAttributeEquals($s['transferSize'],'transferSize',     $ps);
            
            $rules = $json['rules'];
            
            foreach ($rules as $rule) {
                $name =  $rule['shortName'];
                
                $ps_rule = $ps->rules['p'. $name];
                
                $this->assertInstanceOf('PageSpeedRule', $ps_rule);
                $this->assertEquals($ps_rule->shortName, $name);
            }
        }
    }
?>
