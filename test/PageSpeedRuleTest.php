<?php
    //set_include_path(dirname(dirname(__FILE__)));
    require_once 'PHPUnit/Framework/TestCase.php';
    
    require_once (dirname(dirname(__FILE__)).'/global.php');
    require_once (dirname(dirname(__FILE__)).'/lib/PageSpeedRule.php');
    
    class PageSpeedRuleTest extends PHPUnit_Framework_TestCase
    {
        public function testNewUninit() {
            
            // Uninitialized object
            $psr = new PageSpeedRule();
            $this->assertInstanceOf('PageSpeedRule', $psr);
        }
        
        public function testNewInitFromJsonString() {
            
            $data = '{
                "name"       : "Test Rule",
                "shortName"  : "TestRule",
                "score"      : 100,
                "warnings"   : "Test warning",
                "information": "Test information",
                "statistics" : { "Test" : 100 }
            }';
            
            $psr = new PageSpeedRule($data);
            $this->assertInstanceOf('PageSpeedRule', $psr);
            
            $json = json_decode($data, 1);
            foreach ($json as $key => $val) {
                $this->assertObjectHasAttribute($key, $psr);
            }
        
        }
        public function testNewInitFromJsonArray() {
            
            $data = '{
                "name"       : "Test Rule",
                "shortName"  : "TestRule",
                "score"      : 100,
                "warnings"   : "Test warning",
                "information": "Test information",
                "statistics" : { "Test" : 100 }
            }';
            $json = json_decode($data, 1);
            
            $psr  = new PageSpeedRule($json);
            $this->assertInstanceOf('PageSpeedRule', $psr);
            
            $properties = get_object_vars($psr);
            
            foreach ($psr as $key => $value) {
                $this->assertEquals($value, $json[$key]);
            }
        }        
    }
?>
