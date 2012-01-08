<?php
    include('../global.php');
    class UrlTest extends PHPUnit_Framework_TestCase
    {
        public function testDetailsUrl() {
            global $showslow_base;
            putenv("URLVERSIONREWRITE=YES");
            $url = 'http://www.foo.com';
            $urlId = 2345;
            $this->assertEquals(detailsUrl($urlId, $url), $showslow_base.'details/'.$urlId.'/'.$url);

            putenv("URLVERSIONREWRITE=NO");
            $this->assertEquals(detailsUrl($urlId, $url), $showslow_base.'details/?urlid='.$urlId.'&url='.$url);
        }
    }
?>
