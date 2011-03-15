var SS = (function ($) {
    var PERCENT_GRADE = 1,
        BYTES = 2,
        MS = 3,
        NUMBER = 4,
        PERCENTS = 5,

        formatter = {
            bytes: function (val, axis) {
                return val + ' bytes';
            },
            msec: function (val, axis) {
                return val + ' msec';
            },
            percent: function (val, axis) {
                return val + '%';
            }
        },
        
        _metrics = {
            'yslow': {
                'o': { color: 0, label: 'Overall rank', data: [], yaxis: PERCENT_GRADE },
                'w': { color: 1, label: 'Page Size', data: [], yaxis: BYTES },
                'r': { color: 2, label: 'Amount of requests with empty cache', data: [], yaxis: NUMBER },
                'lt': { color: 3, label: 'Page Load time', data: [], yaxis: MS },
                'ynumreq': { color: 4, label: 'Make fewer HTTP requests', data: [], yaxis: PERCENT_GRADE },
                'ycdn': { color: 5, label: 'Use a Content Delivery Network (CDN)', data: [], yaxis: PERCENT_GRADE },
                'yexpires': { color: 6, label: 'Add Expires headers', data: [], yaxis: PERCENT_GRADE },
                'yemptysrc': { color: 7, label: 'Avoid Empty Img src', data: [], yaxis: PERCENT_GRADE },
                'ycsstop': { color: 8, label: 'Put CSS at top', data: [], yaxis: PERCENT_GRADE },
                'yjsbottom': { color: 9, label: 'Put JavaScript at bottom', data: [], yaxis: PERCENT_GRADE },
                'yexpressions': { color: 10, label: 'Avoid CSS expressions', data: [], yaxis: PERCENT_GRADE },
                'yexternal': { color: 11, label: 'Make JavaScript and CSS external', data: [], yaxis: PERCENT_GRADE },
                'ydns': { color: 12, label: 'Reduce DNS lookups', data: [], yaxis: PERCENT_GRADE },
                'yminify': { color: 13, label: 'Minify JavaScript and CSS', data: [], yaxis: PERCENT_GRADE },
                'yredirects': { color: 14, label: 'Avoid URL redirects', data: [], yaxis: PERCENT_GRADE },
                'ydupes': { color: 15, label: 'Remove duplicate JavaScript and CSS', data: [], yaxis: PERCENT_GRADE },
                'yetags': { color: 16, label: 'Configure entity tags (ETags)', data: [], yaxis: PERCENT_GRADE },
                'yxhr': { color: 17, label: 'Make AJAX cacheable', data: [], yaxis: PERCENT_GRADE },
                'yxhrmethod': { color: 18, label: 'Use GET for AJAX requests', data: [], yaxis: PERCENT_GRADE },
                'ymindom': { color: 19, label: 'Reduce the number of DOM elements', data: [], yaxis: PERCENT_GRADE },
                'yno404': { color: 20, label: 'Avoid HTTP 404 (Not Found) error', data: [], yaxis: PERCENT_GRADE },
                'ymincookie': { color: 21, label: 'Reduce cookie size', data: [], yaxis: PERCENT_GRADE },
                'ycookiefree': { color: 22, label: 'Use cookie-free domains', data: [], yaxis: PERCENT_GRADE },
                'ynofilter': { color: 23, label: 'Avoid AlphaImageLoader filter', data: [], yaxis: PERCENT_GRADE },
                'yimgnoscale': { color: 24, label: 'Do not scale images in HTML', data: [], yaxis: PERCENT_GRADE },
                'yfavicon': { color: 25, label: 'Make favicon small and cacheable', data: [], yaxis: PERCENT_GRADE }        
            },
            'pagespeed': {
                'o': { color: 26, label: 'Overall grade', data: [], yaxis: PERCENT_GRADE },
                'w': { color: 27, label: 'Page Size', data: [], yaxis: BYTES },
                'l': { color: 28, label: 'Page load time', data: [], yaxis: MS },
                't': { color: 29, label: 'Transfer size of all resources', data: [], yaxis: BYTES },
                'r': { color: 30, label: 'Total Requests', data: [], yaxis: NUMBER },
                'pBrowserCache': { color: 31, label: 'Leverage browser caching', data: [], yaxis: PERCENT_GRADE },
                'pCacheValid': { color: 32, label: 'Leverage proxy caching', data: [], yaxis: PERCENT_GRADE },
                'pMinDns': { color: 33, label: 'Minimize DNS lookups', data: [], yaxis: PERCENT_GRADE },
                'pBadReqs': { color: 34, label: 'Avoid bad requests', data: [], yaxis: PERCENT_GRADE },
                'pCombineJS': { color: 35, label: 'Combine external JavaScript', data: [], yaxis: PERCENT_GRADE },
                'pCombineCSS': { color: 36, label: 'Combine external CSS', data: [], yaxis: PERCENT_GRADE },
                'pSprite': { color: 37, label: 'Combine images using CSS sprites', data: [], yaxis: PERCENT_GRADE },
                'pCssJsOrder': { color: 38, label: 'Optimize the order of styles and scripts', data: [], yaxis: PERCENT_GRADE },
                'pDocWrite': { color: 39, label: 'Avoid document.write', data: [], yaxis: PERCENT_GRADE },
                'pCssImport': { color: 40, label: 'Avoid CSS @import', data: [], yaxis: PERCENT_GRADE },
                'pPreferAsync': { color: 41, label: 'Prefer asynchronous resources', data: [], yaxis: PERCENT_GRADE },
                'pParallelDl': { color: 42, label: 'Parallelize downloads across hostnames', data: [], yaxis: PERCENT_GRADE },
                'pMinReqSize': { color: 43, label: 'Minimize request size', data: [], yaxis: PERCENT_GRADE },
                'pNoCookie': { color: 44, label: 'Serve static content from a cookieless domain', data: [], yaxis: PERCENT_GRADE },
                'pGzip': { color: 45, label: 'Enable compression', data: [], yaxis: PERCENT_GRADE },
                'pUnusedCSS': { color: 46, label: 'Remove unused CSS', data: [], yaxis: PERCENT_GRADE },
                'pMinifyJS': { color: 47, label: 'Minify JavaScript', data: [], yaxis: PERCENT_GRADE },
                'pMinifyCSS': { color: 48, label: 'Minify CSS', data: [], yaxis: PERCENT_GRADE },
                'pMinifyHTML': { color: 49, label: 'Minify HTML', data: [], yaxis: PERCENT_GRADE },
                'pDeferJS': { color: 50, label: 'Defer loading of JavaScript', data: [], yaxis: PERCENT_GRADE },
                'pOptImages': { color: 51, label: 'Optimize images', data: [], yaxis: PERCENT_GRADE },
                'pScaleImages': { color: 52, label: 'Serve scaled images', data: [], yaxis: PERCENT_GRADE },
                'pDupeRsrc': { color: 53, label: 'Serve resources from a consistent URL', data: [], yaxis: PERCENT_GRADE },
                'pCssSelect': { color: 54, label: 'Use efficient CSS selectors', data: [], yaxis: PERCENT_GRADE },
                'pCssInHead': { color: 55, label: 'Put CSS in the document head', data: [], yaxis: PERCENT_GRADE },
                'pImgDims': { color: 56, label: 'Specify image dimensions', data: [], yaxis: PERCENT_GRADE },
                'pCharsetEarly': { color: 57, label: 'Specify a character set early', data: [], yaxis: PERCENT_GRADE }
            },
            'dynatrace': {
                'timetoimpression': { color: 58, label: 'Time to first impression', data: [], yaxis: MS },
                'timetoonload': { color: 59, label: 'Time to onLoad', data: [], yaxis: MS },
                'timetofullload': { color: 60, label: 'Time to full page load', data: [], yaxis: MS },
                'timeonnetwork': { color: 61, label: 'Total time on network', data: [], yaxis: MS },
                'timeinjs': { color: 62, label: 'Total time in JavaScript', data: [], yaxis: MS },
                'timeinrendering': { color: 63, label: 'Total time in rendering', data: [], yaxis: MS },
                'reqnumber': { color: 64, label: 'Number of requests', data: [], yaxis: NUMBER },
                'xhrnumber': { color: 65, label: 'Number of XHR requests', data: [], yaxis: NUMBER },
                'pagesize': { color: 66, label: 'Total page size', data: [], yaxis: BYTES },
                'cachablesize': { color: 67, label: 'Total cachable size', data: [], yaxis: BYTES },
                'noncachablesize': { color: 68, label: 'Total non-cachable size', data: [], yaxis: BYTES },
                'rank': { color: 69, label: 'Overall rank', data: [], yaxis: PERCENT_GRADE },
                'cache': { color: 70, label: 'Caching rank', data: [], yaxis: PERCENT_GRADE },
                'net': { color: 71, label: 'Network rank', data: [], yaxis: PERCENT_GRADE },
                'server': { color: 72, label: 'Server rank', data: [], yaxis: PERCENT_GRADE },
                'js': { color: 73, label: 'JavaScript rank', data: [], yaxis: PERCENT_GRADE }  
            },
            'dommonster': {
                'elements': { color: 74, label: 'Number of elements', data: [], yaxis: NUMBER },
                'nodecount': { color: 75, label: 'Number of DOM nodes', data: [], yaxis: NUMBER },
                'textnodes': { color: 76, label: 'Number of Text nodes', data: [], yaxis: NUMBER },
                'textnodesize': { color: 77, label: 'Size of Text nodes', data: [], yaxis: BYTES },
                'contentpercent': { color: 78, label: 'Content percentage', data: [], yaxis: PERCENTS },
                'average': { color: 79, label: 'Average nesting depth', data: [], yaxis: NUMBER },
                'domsize': { color: 80, label: 'Seralized DOM size', data: [], yaxis: BYTES },
                'bodycount': { color: 81, label: 'DOM tree serialization time', data: [], yaxis: MS }
            }
        },
        
        _graph,
        _graph_options = {
            series: {
                lines: { show: true },
                points: { 
                    show: true,
                    radius: 4.5
                }
            },
        
            legend: {
                show: true,
                position: 'sw',
                //container: $('#legend'),
                //noColumns: 10
            },
        
            grid: {
                hoverable: true,
                autoHighlight: true,
                backgroundColor: {
                    colors: ['#fff', '#ccc']
                }
            },
            
            colors: ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#000000','#800000', '#008000', '#000080', '#808000', '#800080', '#008080', '#808080','#C00000', '#00C000', '#0000C0', '#C0C000', '#C000C0', '#00C0C0', '#C0C0C0','#400000', '#004000', '#000040', '#404000', '#400040', '#004040', '#404040','#200000', '#002000', '#000020', '#202000', '#200020', '#002020', '#202020','#600000', '#006000', '#000060', '#606000', '#600060', '#006060', '#606060','#A00000', '#00A000', '#0000A0', '#A0A000', '#A000A0', '#00A0A0', '#A0A0A0','#E00000', '#00E000', '#0000E0', '#E0E000', '#E000E0', '#00E0E0', '#E0E0E0'],
            
            crosshair: {
                mode: 'x',
                color: '#000'
            },
            
            selection: {
                mode: 'x'
            },
            
            // Single X-axis for dates
            xaxis: {
                position: 'bottom',
                mode: 'time',
                timeformat: '%b %d %y',
                //minTickSize: [1, 'month'],
                //tickSize: [1, 'day']
            },
        
            // Multiple Y-axes for each datatype
            // Index starts at 1
            yaxes: [
                {   // Scores/Grades (Percentage) (1)
                    label: 'Scores/Grades',
                    min: 0,
                    max: 100
                },
                {   // Bytes (2)
                    position: 'right',
                    min: 0,
                    tickFormatter: formatter.bytes
                },
                {   // Milliseconds (3)
                    position: 'right',
                    min: 0,
                    minTickSize: 1000,
                    tickSize: 1000,
                    tickFormatter: formatter.msec
                },
                {   // Numbers (4)
                    position: 'right',
                    min: 0
                },
                {   // Percentages (5)
                    position: 'right',
                    min: 0,
                    max: 100,
                    tickFormatter: formatter.percent
                }
            ]
        },
        data = [],
        dataset = {};

    
    function _getMetrics (options, callback) {
        if (typeof callback !== 'function') { callback = false; }
        
        $.ajax({
            url: 'data2.php',
            data: 'url=' + options.url + '&provider=' + options.provider + '&metrics=' + options.metrics + '&format=json',
            error: function (jqXHR, textStatus, errorThrown) {
                alert('There was a problem with the request:\n\n' + errorThrown + ' : ' + textStatus);
                return;
            },
            success: function (results) {
                var metrics_in_order = options.metrics.split(','), // Order matters
                    i = 0, j = 0,
                    results_max = results.length,
                    metric_max = metrics_in_order.length;

                // Initialize dataset object
                $.each(metrics_in_order, function (index, metric) {
                    dataset[options.provider + '-' + metric] = {};
                    $.extend(true, dataset[options.provider + '-' + metric], _metrics[options.provider][metric]);
                });

                // Iterate through results array
                for (; i < results_max; i += 1) {
                    // Iterate through each inner-array within results array
                    for (; j < metric_max; j += 1) {
                        dataset[ options.provider + '-' + metrics_in_order[j] ].data.push( [ results[i][0], results[i][j+1] ] );
                    }
                    j = 0;
                }

                if (data) { data = []; }
                
                $.each(dataset, function (key, val) {
                    data.push(val);
                });

                _graph = $.plot($('#graph'), data, _graph_options);               

                if (callback) { callback(results); }
            }
        });      
    }

 
    function _removeSeries (provider, metric) {
        if (typeof dataset[provider + '-' + metric] !== 'undefined') {
            delete dataset[provider + '-' + metric];
            data = [];
            $.each(dataset, function (key, val) {
                data.push(val);
            });
            $('#legend').empty();
            _graph = $.plot($('#graph'), data, _graph_options);
        }
    }

    
    return {    // Publicly accessible methods.
        getMetrics: function (options, callback) {
            _getMetrics(options, callback);
        },
   
        removeSeries: function (provider, metric) {
            _removeSeries(provider, metric);
        }
    }
})(jQuery);