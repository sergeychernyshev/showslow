/*jslint browser: true*/
/*global Timeplot, YAHOO*/
var timeplot;

YAHOO.util.Event.onDOMReady(function() {
	var timeGeometry = new Timeplot.DefaultTimeGeometry({
		gridColor: "#000000",
		axisLabelsPlacement: "bottom"
	});

	var valueGeometryGrades = new Timeplot.DefaultValueGeometry({
		max: 100,
		min: 0,
		gridColor: "#000000",
		axisLabelsPlacement: "left"
	});

	var plotInfo = [];
	for (var url in data) {
		data[url].eventsource = new Timeplot.DefaultEventSource();

		var column = 2; // second for yslow and pagespeed, first for dynatrace
		if (ranker == 'dynatrace') {
			column = 1;
		}

		plotInfo[plotInfo.length] = Timeplot.createPlotInfo({
			id: url,
			label: url,
			dataSource: new Timeplot.ColumnSource(data[url].eventsource,column),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: data[url].color,
			showValues: true
		});
	}

	timeplot = Timeplot.create(document.getElementById("my-timeplot"), plotInfo);

	for (var url in data) {
		if (ranker == 'pagespeed') {
			timeplot.loadText(SHOWSLOW.base_url+'details/data_pagespeed.php?smooth&subset=graph&urlid=' + encodeURIComponent(data[url].id) + '&ver=' + data[url].version, ",", data[url].eventsource);
		} else if (ranker == 'dynatrace') {
			timeplot.loadText(SHOWSLOW.base_url+'details/data_dynatrace.php?smooth&subset=graph&urlid=' + encodeURIComponent(data[url].id) + '&ver=' + data[url].version, ",", data[url].eventsource);
		} else {
			timeplot.loadText(SHOWSLOW.base_url+'details/data.php?smooth&subset=graph&profile=ydefault&urlid=' + encodeURIComponent(data[url].id) + '&ver=' + data[url].version, ",", data[url].eventsource);
		}
	}

});

var resizeTimerID = null;

YAHOO.util.Event.addListener(document, 'resize', function() {
	if (resizeTimerID === null) {
		resizeTimerID = window.setTimeout(function() {
			resizeTimerID = null;
			timeplot.repaint();
		}, 100);
	}
});
