/*jslint browser: true*/
/*global Timeplot, YAHOO*/
var timeplot;

function onLoad(url, ydataversion, psdataversion, eventversion) {
	var eventSource1 = new Timeplot.DefaultEventSource(); // YSlow1 measurements
	var eventSource2 = new Timeplot.DefaultEventSource(); // YSlow2 measurements
	var pagespeed = new Timeplot.DefaultEventSource(); // YSlow2 measurements
	var showslowevents = new Timeplot.DefaultEventSource(); // ShowSlow Events

	var timeGeometry = new Timeplot.DefaultTimeGeometry({
		gridColor: "#000000",
		axisLabelsPlacement: "bottom"
	});

	var valueGeometryGrades = new Timeplot.DefaultValueGeometry({
		min: 0,
		max: 100,
		gridColor: "#000000",
		axisLabelsPlacement: "left"
	});

	var valueGeometryWeight = new Timeplot.DefaultValueGeometry({
		min: 0,
		max: 300000,
		gridColor: "#000000",
		axisLabelsPlacement: "right"
	});

	var valueGeometryRequests = new Timeplot.DefaultValueGeometry({
		min: 0,
		max: 100,
		gridColor: "#000000",
		axisLabelsPlacement: "left"
	});

	var plotInfo = [
		Timeplot.createPlotInfo({
			id: "yslowgrade2",
			label: "YSlow2 Grade",
			dataSource: new Timeplot.ColumnSource(eventSource2,2),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: "#2175D9",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pagespeed",
			label: "Page Speed Grade",
			dataSource: new Timeplot.ColumnSource(pagespeed,2),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: "#6F4428",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pageload",
			label: "Page Load Time (Page Speed)",
			dataSource: new Timeplot.ColumnSource(pagespeed,3),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryWeight,
			lineColor: "#EE4F00",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pageweight2",
			label: "Page Size (bytes)",
			dataSource: new Timeplot.ColumnSource(eventSource2,1),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryWeight,
			lineColor: "#D0A825",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "requests2",
			label: "Total Requests",
			dataSource: new Timeplot.ColumnSource(eventSource2,3),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryRequests,
			lineColor: "#75CF74",
			showValues: true
		}), 
		Timeplot.createPlotInfo({
			id: "showslowevents",
			timeGeometry: timeGeometry,
			eventSource: showslowevents,
			lineColor: "#3638AF"
		})
	];

	timeplot = Timeplot.create(document.getElementById("my-timeplot"), plotInfo);
	timeplot.loadXML('events.php?url=' + url + '&ver=' + eventversion, showslowevents);
	timeplot.loadText('data.php?profile=ydefault&url=' + url + '&ver=' + ydataversion, ",", eventSource2);
	timeplot.loadText('data_pagespeed.php?url=' + url + '&ver=' + psdataversion, ",", pagespeed);

	var loader = new YAHOO.util.YUILoader({
	    require: ["dom", "container", "paginator", "datatable", "datasource"],
	    loadOptional: true,
	    onSuccess: function() {
		for (name in details) {
			if (details.hasOwnProperty(name)) {
				var el = YAHOO.util.Dom.get('details_'+name);
				el.innerHTML='+';

				new YAHOO.widget.Tooltip("tt_"+name,  
				{
					context:	el,
					text:		details[name].join('<br/>')
				});
			}
		}

		var yColumnDefs = [
			{key:"timestamp", label:"Timestamp", sortable:true, formatter:"date"},
			{key:"w", label:"Page Size (bytes)", sortable:true},
			{key:"r", label:"Total Requests", sortable:true},
			{key:"o", label:"Grade (0-100)", sortable:true},
			{key:"profile", label:"Profile used", sortable:true}
		];

		var psColumnDefs = [
			{key:"timestamp", label:"Timestamp", sortable:true, formatter:"date"},
			{key:"w", label:"Page Size (bytes)", sortable:true},
			{key:"r", label:"Total Requests", sortable:true},
			{key:"o", label:"Grade (0-100)", sortable:true},
			{key:"l", label:"Load Time (ms)", sortable:true}
		];

		var yDataSource = new YAHOO.util.DataSource("data.php?");
		yDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		yDataSource.responseSchema = {
			recordDelim : "\n", 
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "r", 
			'ynumreq','ycdn','yexpires','ycompress','ycsstop',
			'yjsbottom','yjsbottom','yexternal','ydns','yminify',
			'yredirects','ydupes','yetags','yxhr','yxhrmethod',
			'ymindom','yno404','ymincookie','ycookiefree','ynofilter',
	                'yimgnoscale','yfavicon', "profile"]
		};

		var psDataSource = new YAHOO.util.DataSource("data_pagespeed.php?");
		psDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		psDataSource.responseSchema = {
			recordDelim : "\n", 
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "l", "r", "t", "v",
			"pMinifyCSS", "pMinifyJS", "pOptImgs", "pImgDims", "pCombineJS", "pCombineCSS",
			"pCssInHead", "pBrowserCache", "pProxyCache", "pNoCookie", "pCookieSize",
			"pParallelDl", "pCssSelect", "pCssJsOrder", "pDeferJS", "pGzip",
			"pMinRedirect", "pCssExpr", "pUnusedCSS", "pMinDns", "p.pDupeRsrc"]
		};

		var yDataTable = new YAHOO.widget.DataTable("measurementstable", yColumnDefs, yDataSource,
		{
			paginator: new YAHOO.widget.Paginator({
			    rowsPerPage: 10 
			}),
			initialRequest: "url=" + url + "&ver=" + ydataversion
		});

		var psDataTable = new YAHOO.widget.DataTable("ps_measurementstable", psColumnDefs, psDataSource,
		{
			paginator: new YAHOO.widget.Paginator({
			    rowsPerPage: 10
			}),
			initialRequest: "url=" + url + "&ver=" + psdataversion
		});
	    },
	    timeout: 10000,
	    combine: true
	});
	loader.insert();
}

var resizeTimerID = null;
function onResize() {
	if (resizeTimerID === null) {
		resizeTimerID = window.setTimeout(function() {
			resizeTimerID = null;
			timeplot.repaint();
		}, 100);
	}
}
