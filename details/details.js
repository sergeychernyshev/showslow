/*jslint browser: true*/
/*global Timeplot, YAHOO*/
var timeplot;

YAHOO.util.Event.onDOMReady(function() { 
	var eventSource2 = new Timeplot.DefaultEventSource();	// YSlow2 measurements
	var pagespeed = new Timeplot.DefaultEventSource();	// Page Speed measurements
	var dynatrace = new Timeplot.DefaultEventSource();	// dynaTrace  measurements
	var showslowevents = new Timeplot.DefaultEventSource();	// ShowSlow Events

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
		gridColor: "#000000",
		axisLabelsPlacement: "right"
	});

	var valueGeometryRequests = new Timeplot.DefaultValueGeometry({
		min: 0,
		gridColor: "#75CF74",
		axisLabelsPlacement: "left"
	});

	var valueGeometryTime = new Timeplot.DefaultValueGeometry({
		min: 0,
		max: 2000,
		gridColor: "#800080",
		axisLabelsPlacement: "right"
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
			id: "dynatrace",
			label: "dynaTrace rank",
			dataSource: new Timeplot.ColumnSource(dynatrace,1),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: "#AB0617",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pageload",
			label: "Page Load Time (Page Speed)",
			dataSource: new Timeplot.ColumnSource(pagespeed,3),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryTime,
			lineColor: "#EE4F00",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "lt",
			label: "Page Load Time (YSlow)",
			dataSource: new Timeplot.ColumnSource(eventSource2,4),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryTime,
			lineColor: "purple",
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

	for (var name in metrics) {
		var metric = metrics[name];

		metric['source'] = new Timeplot.DefaultEventSource();

		var config = {};
		if (typeof(metric.min) !== 'undefined') {
			config.min = metric.min;
		}
		if (typeof(metric.max) !== 'undefined') {
			config.max = metric.max;
		}

		plotInfo[plotInfo.length] = Timeplot.createPlotInfo({
			id: "showslowmetric"+name,
			label: metric.title,
			dataSource: new Timeplot.ColumnSource(metric.source,1),
			timeGeometry: timeGeometry,
			valueGeometry: new Timeplot.DefaultValueGeometry(config),
			lineColor:  metric.color,
			showValues: true
		});
	}

	timeplot = Timeplot.create(document.getElementById("my-timeplot"), plotInfo);
	timeplot.loadXML('events.php?url=' + url + '&ver=' + eventversion, showslowevents);
	if (ydataversion) {
		timeplot.loadText('data.php?smooth=yes&subset=graph&profile=ydefault&url=' + url + '&ver=' + ydataversion, ",", eventSource2);
	}

	if (psdataversion) {
		timeplot.loadText('data_pagespeed.php?smooth=yes&subset=graph&url=' + url + '&ver=' + psdataversion, ",", pagespeed);
	}

	if (dtdataversion) {
		timeplot.loadText('data_dynatrace.php?smooth=yes&subset=graph&url=' + url + '&ver=' + dtdataversion, ",", dynatrace);
	}

	for (var name in metrics) {
		timeplot.loadText('data_metric.php?smooth=yes&metric=' + name + '&url=' + url, ",", metrics[name].source);
	}

	var loader = new YAHOO.util.YUILoader({
	    require: ["dom", "container", "datatable", "datasource"],
	    loadOptional: true,
	    timeout: 10000,
	    combine: true,
	    onSuccess: function() {
		if (typeof(details) !== 'undefined' ) {
			for (name in details) {
				if (details.hasOwnProperty(name)) {
					var el = YAHOO.util.Dom.get('details_'+name);

					if (!el) {
						continue;
					}

					el.innerHTML='<div class="moreinfo"></div>';

					new YAHOO.widget.Tooltip("tt_"+name,  
					{
						context:	el,
						text:		decodeURIComponent(details[name].join('<br/>'))
					});
				}
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

		var dtColumnDefs = [
			{key:"timestamp", label:"Timestamp", sortable:true, formatter:"date"},
			{key:"size", label:"Page Size (bytes)", sortable:true},
			{key:"numreq", label:"Total Requests", sortable:true},
			{key:"rank", label:"Overall Rank (0-100)", sortable:true},
			{key:"timetoimp", label:"Time to First Impression (ms)", sortable:true},
		];
		var yDataSource = new YAHOO.util.DataSource("data.php?subset=table&");
		yDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		yDataSource.responseSchema = {
			recordDelim : "\n", 
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "r", "lt", "profile"]
		};

		var psDataSource = new YAHOO.util.DataSource("data_pagespeed.php?subset=table&");
		psDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		psDataSource.responseSchema = {
			recordDelim : "\n", 
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "l", "r"]
		};

		var dtDataSource = new YAHOO.util.DataSource("data_dynatrace.php?subset=table&");
		dtDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		dtDataSource.responseSchema = {
			recordDelim : "\n", 
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "size", "numreq", "rank", "timetoimp"]
		};

		var yDataTable = new YAHOO.widget.ScrollingDataTable("measurementstable", yColumnDefs, yDataSource,
		{
			height: "15em",
			initialRequest: "url=" + url + "&ver=" + ydataversion
		});

		var psDataTable = new YAHOO.widget.ScrollingDataTable("ps_measurementstable", psColumnDefs, psDataSource,
		{
			height: "15em",
			initialRequest: "url=" + url + "&ver=" + psdataversion
		});

		var dtDataTable = new YAHOO.widget.ScrollingDataTable("dt_measurementstable", dtColumnDefs, dtDataSource,
		{
			height: "15em",
			initialRequest: "url=" + url + "&ver=" + dtdataversion
		});
	    }
	});
	loader.insert();
})

var resizeTimerID = null;
YAHOO.util.Event.addListener(window, 'resize', function() {
	if (resizeTimerID === null) {
		resizeTimerID = window.setTimeout(function() {
			resizeTimerID = null;
			timeplot.repaint();
		}, 100);
	}
});
