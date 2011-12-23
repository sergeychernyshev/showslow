<?php

    function detailsUrl($urlId, $url) {
        global $showslow_base;
        if(getenv('URLVERSIONREWRITE') == 'YES') {
            return $showslow_base."details/".$urlId."/".$url;
        } else {
            return $showslow_base."details/?urlid=".$urlId."&url=".$url;
        }
    }
