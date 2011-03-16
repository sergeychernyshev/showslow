<?php
require_once(dirname(dirname(__FILE__)).'/global.php');

    $url = isset($_GET['url']) ? $_GET['url'] : 'http://www.yahoo.com/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ShowSlow Flot Testing</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        
        h1 {
            text-align: center;
            margin: 0 auto;
        }
        body {
            font-family: Arial;
            font-size: .9em;
        }
        
        #graph {
            width: 960px;
            height: 320px;
            margin: 0 auto;
        }
        
        #overview {
            width: 480px;
            height: 60px;
            margin: 10px auto;
        }
        
        .reset {
            text-align: center;
        }
        
        #reset {
        
        }
        
        fieldset {
            width: 960px;
            margin: 15px auto;
            border-radius: 5px;
        }
        
        legend {
            cursor: pointer;
            font-weight: bold;
            background-color: #eee;
            color: #000;
            padding: 5px;
            border: solid 1px #000;
        }
        
        label:hover {
            color: #f00;
        }
        
        .col {
            -moz-column-count: 3;
            -moz-column-width: 20em;
            -moz-column-gap: 2em;
            -moz-column-rule: 2px dotted #ccc;
            
            -webkit-column-count: 3;
            -webkit-column-width: 20em;
            -webkit-column-gap: 2em;
            -webkit-column-rule: 2px dotted #ccc;
            
            column-count: 3;
            column-width: 20em;
            column-gap: 2em;
            column-rule: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1><?php echo $url ?></h1>
    <div id="graph"></div>
    <div>
        <div id="overview"></div>
        <div class="reset">
            <button id="reset">Reset Zoom</button>
        </div>
    </div>
    <form>
    <fieldset id="yslow">
        <legend>YSlow Metrics</legend>
        <div class="col">
        <input type="checkbox" id="yslow-o">
        <label for="yslow-o">Overall rank</label><br/>

        <input type="checkbox" id="yslow-w">
        <label for="yslow-w">Page Size</label><br/>

        <input type="checkbox" id="yslow-r">
        <label for="yslow-r">Amount of requests with empty cache</label><br/>

        <input type="checkbox" id="yslow-lt">
        <label for="yslow-lt">Page Load time</label><br/>
        
        <input type="checkbox" id="ynumreq">
        <label for="ynumreq">Make fewer HTTP requests</label><br/>
        
        <input type="checkbox" id="ycdn">
        <label for="ycdn">Use a Content Delivery Network (CDN)</label><br/>
        
        <input type="checkbox" id="yexpires">
        <label for="yexpires">Add Expires headers</label><br/>
        
        <input type="checkbox" id="yemptysrc">
        <label for="yemptysrc">Avoid Empty Img src</label><br/>
        
        <input type="checkbox" id="ycsstop">
        <label for="ycsstop">Put CSS at top</label><br/>
        
        <input type="checkbox" id="yjsbottom">
        <label for="yjsbottom">Put JavaScript at bottom</label><br/>
        
        <input type="checkbox" id="yexpressions">
        <label for="yexpressions">Avoid CSS expressions</label><br/>
        
        <input type="checkbox" id="yexternal">
        <label for="yexternal">Make JavaScript and CSS external</label><br/>
        
        <input type="checkbox" id="ydns">
        <label for="ydns">Reduce DNS lookups</label><br/>
        
        <input type="checkbox" id="yminify">
        <label for="yminify">Minify JavaScript and CSS</label><br/>
        
        <input type="checkbox" id="yredirects">
        <label for="yredirects">Avoid URL redirects</label><br/>
        
        <input type="checkbox" id="ydupes">
        <label for="ydupes">Remove duplicate JavaScript and CSS</label><br/>
        
        <input type="checkbox" id="yetags">
        <label for="yetags">Configure entity tags (ETags)</label><br/>
        
        <input type="checkbox" id="yxhr">
        <label for="yxhr">Make AJAX cacheable</label><br/>
        
        <input type="checkbox" id="yxhrmethod">
        <label for="yxhrmethod">Use GET for AJAX requests</label><br/>
        
        <input type="checkbox" id="ymindom">
        <label for="ymindom">Reduce the number of DOM elements</label><br/>
        
        <input type="checkbox" id="yno404">
        <label for="yno404">Avoid HTTP 404 (Not Found) error</label><br/>
        
        <input type="checkbox" id="ymincookie">
        <label for="ymincookie">Reduce cookie size</label><br/>
        
        <input type="checkbox" id="ycookiefree">
        <label for="ycookiefree">Use cookie-free domains</label><br/>
        
        <input type="checkbox" id="ynofilter">
        <label for="ynofilter">Avoid AlphaImageLoader filter</label><br/>
        
        <input type="checkbox" id="yimgnoscale">
        <label for="yimgnoscale">Do not scale images in HTML</label><br/>
        
        <input type="checkbox" id="yfavicon">
        <label for="yfavicon">Make favicon small and cacheable</label>
        </div>
    </fieldset>
    
    
    <fieldset id="pagespeed">
        <legend>PageSpeed Metrics</legend>
        
        <div class="col">
        <input type="checkbox" id="pagespeed-o">
        <label for="pagespeed-o">Overall grade</label><br/>
        
        <input type="checkbox" id="pagespeed-w">
        <label for="pagespeed-w">Page Size</label><br/>

        <input type="checkbox" id="pagespeed-l">
        <label for="pagespeed-l">Page load time</label><br/>
        
        <input type="checkbox" id="pagespeed-t">
        <label for="pagespeed-t">Transfer size of all resources</label><br/>
        
        <input type="checkbox" id="pagespeed-r">
        <label for="pagespeed-r">Total Requests</label><br/>
        
        <input type="checkbox" id="pBrowserCache">
        <label for="pBrowserCache">Leverage browser caching</label><br/>
        
        <input type="checkbox" id="pCacheValid">
        <label for="pCacheValid">Leverage proxy caching</label><br/>
        
        <input type="checkbox" id="pMinDns">
        <label for="pMinDns">Minimize DNS lookups</label><br/>
        
        <input type="checkbox" id="pBadReqs">
        <label for="pBadReqs">Avoid bad requests</label><br/>
        
        <input type="checkbox" id="pCombineJS">
        <label for="pCombineJS">Combine external JavaScript</label><br/>
        
        <input type="checkbox" id="pCombineCSS">
        <label for="pCombineCSS">Combine external CSS</label><br/>
        
        <input type="checkbox" id="pSprite">
        <label for="pSprite">Combine images using CSS sprites</label><br/>
        
        <input type="checkbox" id="pCssJsOrder">
        <label for="pCssJsOrder">Optimize the order of styles and scripts</label><br/>
        
        <input type="checkbox" id="pDocWrite">
        <label for="pDocWrite">Avoid document.write</label><br/>
        
        <input type="checkbox" id="pCssImport">
        <label for="pCssImport">Avoid CSS @import</label><br/>
        
        <input type="checkbox" id="pPreferAsync">
        <label for="pPreferAsync">Prefer asynchronous resources</label><br/>
        
        <input type="checkbox" id="pParallelDl">
        <label for="pParallelDl">Parallelize downloads across hostnames</label><br/>
        
        <input type="checkbox" id="pMinReqSize">
        <label for="pMinReqSize">Minimize request size</label><br/>
        
        <input type="checkbox" id="pNoCookie">
        <label for="pNoCookie">Serve static content from a cookieless domain</label><br/>
        
        <input type="checkbox" id="pGzip">
        <label for="pGzip">Enable compression</label><br/>
        
        <input type="checkbox" id="pUnusedCSS">
        <label for="pUnusedCSS">Remove unused CSS</label><br/>
        
        <input type="checkbox" id="pMinifyJS">
        <label for="pMinifyJS">Minify JavaScript</label><br/>
        
        <input type="checkbox" id="pMinifyCSS">
        <label for="pMinifyCSS">Minify CSS</label><br/>
        
        <input type="checkbox" id="pMinifyHTML">
        <label for="pMinifyHTML">Minify HTML</label><br/>
        
        <input type="checkbox" id="pDeferJS">
        <label for="pDeferJS">Defer loading of JavaScript</label><br/>
        
        <input type="checkbox" id="pOptImages">
        <label for="pOptImages">Optimize images</label><br/>
        
        <input type="checkbox" id="pScaleImages">
        <label for="pScaleImages">Serve scaled images</label><br/>
        
        <input type="checkbox" id="pDupeRsrc">
        <label for="pDupeRsrc">Serve resources from a consistent URL</label><br/>
        
        <input type="checkbox" id="pCssSelect">
        <label for="pCssSelect">Use efficient CSS selectors</label><br/>
        
        <input type="checkbox" id="pCssInHead">
        <label for="pCssInHead">Put CSS in the document head</label><br/>
        
        <input type="checkbox" id="pImgDims">
        <label for="pImgDims">Specify image dimensions</label><br/>
        
        <input type="checkbox" id="pCharsetEarly">
        <label for="pCharsetEarly">Specify a character set early</label>
        </div>
    </fieldset>
    
    
    <fieldset id="dynatrace">
        <legend>dynaTrace Metrics</legend>
        
        <div class="col">
        <input type="checkbox" id="timetoimpression">
        <label for="timetoimpression">Time to first impression</label><br/>
        
        <input type="checkbox" id="timetoonload">
        <label for="timetoonload">Time to onLoad</label><br/>
        
        <input type="checkbox" id="timetofullload">
        <label for="timetofullload">Time to full page load</label><br/>
        
        <input type="checkbox" id="timeonnetwork">
        <label for="timeonnetwork">Total time on network</label><br/>
        
        <input type="checkbox" id="timeinjs">
        <label for="timeinjs">Total time in JavaScript</label><br/>
        
        <input type="checkbox" id="timeinrendering">
        <label for="timeinrendering">Total time in rendering</label><br/>
        
        <input type="checkbox" id="reqnumber">
        <label for="reqnumber">Number of requests</label><br/>
        
        <input type="checkbox" id="xhrnumber">
        <label for="xhrnumber">Number of XHR requests</label><br/>
        
        <input type="checkbox" id="pagesize">
        <label for="pagesize">Total page size</label><br/>
        
        <input type="checkbox" id="cachablesize">
        <label for="cachablesize">Total cachable size</label><br/>
        
        <input type="checkbox" id="noncachablesize">
        <label for="noncachablesize">Total non-cachable size</label><br/>
        
        <input type="checkbox" id="rank">
        <label for="rank">Overall rank</label><br/>
        
        <input type="checkbox" id="cache">
        <label for="cache">Caching rank</label><br/>
        
        <input type="checkbox" id="net">
        <label for="net">Network rank</label><br/>
        
        <input type="checkbox" id="server">
        <label for="server">Server rank</label><br/>
        
        <input type="checkbox" id="js">
        <label for="js">JavaScript rank</label>
        </div>
    </fieldset>
    
    
    <fieldset id="dommonster">
        <legend>DOM Monster! Metrics</legend>
        
        <div class="col">
        <input type="checkbox" id="elements">
        <label for="elements">Number of elements</label><br/>
        
        <input type="checkbox" id="nodecount">
        <label for="nodecount">Number of DOM nodes</label><br/>
        
        <input type="checkbox" id="textnodes">
        <label for="textnodes">Number of Text nodes</label><br/>
        
        <input type="checkbox" id="textnodesize">
        <label for="textnodesize">Size of Text nodes</label><br/>
        
        <input type="checkbox" id="contentpercent">
        <label for="contentpercent">Content percentage</label><br/>
        
        <input type="checkbox" id="average">
        <label for="average">Average nesting depth</label><br/>
        
        <input type="checkbox" id="domsize">
        <label for="domsize">Seralized DOM size</label><br/>
        
        <input type="checkbox" id="bodycount">
        <label for="bodycount">DOM tree serialization time</label>
        </div>
    </fieldset>
    </form>
    
    <!--[if lte IE 8]>
    <script language="javascript" type="text/javascript" src="../flot/excanvas.min.js"></script>
    <![endif]-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script src="<?php echo assetURL('flot/jquery.flot.js') ?>"></script>
    <script src="<?php echo assetURL('flot/jquery.flot.crosshair.js') ?>"></script>
    <script src="<?php echo assetURL('flot/jquery.flot.selection.js') ?>"></script>
    <script src="<?php echo assetURL('flot/jquery.flot.resize.js') ?>"></script>
    <script src="<?php echo assetURL('details/showslow.flot.js') ?>"></script>
    <script>
        // Event handlers to all <legend> elements
        // to allow click hide/show of metric selections
        $('legend').click(function () {
            $(this).next().toggle();
        });
        
        // Event handlers to all checkboxes to show/hide 
        // individual metrics within graph
        $('input[type="checkbox"]').each(function () {            
            $(this).change(function () {
                var $this = $(this),
                    id = $this.attr('id'), // Metric
                    pid = $this.parent().parent().attr('id'), // Provider
                    description = $this.next().text(), // Metric Description
                    re = /^(\w+)\-(\w+)$/i; // Regex to distinguish between YSlow and Pagespeed basic measurements
                
                // Distinguish between YSlow and PageSpeed basic measurements
                if (re.test(id)) {
                    id = id.replace(re, '$2');
                }
                
                if ($this.attr('checked')) {;
                    SS.getMetrics({
                        url: '<?php echo $url ?>',
                        provider: pid,
                        metrics: id,
                        callback: false,
                    });
                    $this.next().css('color', '#f00');
                } else {
                    SS.removeSeries(pid, id);
                    $this.next().css('color', '');
                }
            });
        });
        
        // Attach method to reset button
        $('#reset').click(function () {
            SS.resetZoomSelection();
        });
        
        // Not necessary but looks nice to have an empty graph present before use
        $.plot('#graph', [], {
            grid: {
                backgroundColor: {
                    colors: ['#fff', '#ccc']
                }
            }
        });
        
        $.plot('#overview', []);
    </script>
</body>
</html>
