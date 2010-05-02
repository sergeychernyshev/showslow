/*jslint browser: true*/
/*global Timeplot, YAHOO*/
var timeplot;

function onLoad(data) {
	var timeGeometry = new Timeplot.DefaultTimeGeometry({
		gridColor: "#000000",
		axisLabelsPlacement: "bottom"
	});

	var valueGeometryGrades = new Timeplot.DefaultValueGeometry({
		max: 100,
		gridColor: "#000000",
		axisLabelsPlacement: "left"
	});

	var plotInfo = [];
	for (var url in data) {
		data[url].eventsource = new Timeplot.DefaultEventSource();

		plotInfo[plotInfo.length] = Timeplot.createPlotInfo({
			id: url,
			label: url,
			dataSource: new Timeplot.ColumnSource(data[url].eventsource,2),
//			dataSource: new Timeplot.Processor(
//				new Timeplot.ColumnSource(data[url].eventsource,2),
//				Timeplot.Operator.average, { size: 6 }
//			),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: data[url].color,
			showValues: true
		});
	}

	timeplot = Timeplot.create(document.getElementById("my-timeplot"), plotInfo);

	for (var url in data) {
		if (data[url].ranker == 'pagespeed') {
			timeplot.loadText('data_pagespeed.php?url=' + encodeURIComponent(url) + '&ver=' + data[url].version, ",", data[url].eventsource);
		} else {
			timeplot.loadText('data.php?profile=ydefault&url=' + encodeURIComponent(url) + '&ver=' + data[url].version, ",", data[url].eventsource);
		}
	}

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
