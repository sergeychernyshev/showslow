/*jslint browser: true*/
/*global Timeplot, YAHOO*/
var timeplot;

function onLoad(url, version) {
	var eventSource1 = new Timeplot.DefaultEventSource(); // YSlow1 measurements
	var eventSource2 = new Timeplot.DefaultEventSource(); // YSlow2 measurements
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
			id: "yslowgrade1",
			dataSource: new Timeplot.ColumnSource(eventSource1,2),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: "#55009D",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "yslowgrade2",
			dataSource: new Timeplot.ColumnSource(eventSource2,2),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryGrades,
			lineColor: "#2175D9",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pageweight1",
			dataSource: new Timeplot.ColumnSource(eventSource1,1),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryWeight,
			lineColor: "#D0A825",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "pageweight2",
			dataSource: new Timeplot.ColumnSource(eventSource2,1),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryWeight,
			lineColor: "#D0A825",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "requests1",
			dataSource: new Timeplot.ColumnSource(eventSource1,3),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryRequests,
			lineColor: "#75CF74",
			showValues: true
		}),
		Timeplot.createPlotInfo({
			id: "requests2",
			dataSource: new Timeplot.ColumnSource(eventSource2,3),
			timeGeometry: timeGeometry,
			valueGeometry: valueGeometryRequests,
			lineColor: "#75CF74",
			showValues: true
		})
	];

	timeplot = Timeplot.create(document.getElementById("my-timeplot"), plotInfo);
	timeplot.loadText('data.php?profile=yslow1&url=' + url + '&' + version, ",", eventSource1);
	timeplot.loadText('data.php?profile=ydefault&url=' + url + '&' + version, ",", eventSource2);

	    (function() {
		var myColumnDefs = [
			{key:"timestamp", label:"Timestamp", sortable:true, formatter:"date"},
			{key:"w", label:"Page Size (bytes)", sortable:true},
			{key:"r", label:"Total Requests", sortable:true},
			{key:"o", label:"YSlow Grade (0-100)", sortable:true},
			{key:"profile", label:"Profile used", sortable:true}
		];

		var myDataSource = new YAHOO.util.DataSource("data.php?");
		myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		myDataSource.responseSchema = {
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

		var oConfigs = {
			paginator: new YAHOO.widget.Paginator({
			    rowsPerPage: 15 
			}),
			initialRequest: "url=" + url + "&" + dataversion
		};
		var myDataTable = new YAHOO.widget.DataTable("measurementstable", myColumnDefs,
			myDataSource, oConfigs);
			
		return {
		    oDS: myDataSource,
		    oDT: myDataTable
		};
	    })();
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
