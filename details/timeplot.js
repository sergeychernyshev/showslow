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
	timeplot.loadXML(SHOWSLOW.base_url+'details/events.php?url=' + encodeURIComponent(url) + '&ver=' + eventversion, showslowevents);
	if (ydataversion) {
		timeplot.loadText(SHOWSLOW.base_url+'details/data.php?smooth=yes&subset=graph&profile=ydefault&url=' + encodeURIComponent(url) + '&ver=' + ydataversion, ",", eventSource2);
	}

	if (psdataversion) {
		timeplot.loadText(SHOWSLOW.base_url+'details/data_pagespeed.php?smooth=yes&subset=graph&url=' + encodeURIComponent(url) + '&ver=' + psdataversion, ",", pagespeed);
	}

	if (dtdataversion) {
		timeplot.loadText(SHOWSLOW.base_url+'details/data_dynatrace.php?smooth=yes&subset=graph&url=' + encodeURIComponent(url) + '&ver=' + dtdataversion, ",", dynatrace);
	}

	for (var name in metrics) {
		timeplot.loadText(SHOWSLOW.base_url+'details/data_metric.php?smooth=yes&metric=' + name + '&url=' + encodeURIComponent(url), ",", metrics[name].source);
	}
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
