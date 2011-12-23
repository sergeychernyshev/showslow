/*jslint browser: true*/
/*global YAHOO*/

YAHOO.util.Event.onDOMReady(function() {

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
		var yDataSource = new YAHOO.util.DataSource(SHOWSLOW.base_url+"details/data.php?subset=table&");
		yDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		yDataSource.responseSchema = {
			recordDelim : "\n",
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "r", "lt", "profile"]
		};

		var psDataSource = new YAHOO.util.DataSource(SHOWSLOW.base_url+"details/data_pagespeed.php?subset=table&");
		psDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
		psDataSource.responseSchema = {
			recordDelim : "\n",
			fieldDelim : "," ,
			resultsList: "records",
			fields: ["timestamp", "w", "o", "l", "r"]
		};

		var dtDataSource = new YAHOO.util.DataSource(SHOWSLOW.base_url+"details/data_dynatrace.php?subset=table&");
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
			initialRequest: "urlid=" + encodeURIComponent(urlid) + "&ver=" + ydataversion
		});

		var psDataTable = new YAHOO.widget.ScrollingDataTable("ps_measurementstable", psColumnDefs, psDataSource,
		{
			height: "15em",
			initialRequest: "urlid=" + encodeURIComponent(urlid) + "&ver=" + psdataversion
		});

		var dtDataTable = new YAHOO.widget.ScrollingDataTable("dt_measurementstable", dtColumnDefs, dtDataSource,
		{
			height: "15em",
			initialRequest: "urlid=" + encodeURIComponent(urlid) + "&ver=" + dtdataversion
		});
	    }
	});
	loader.insert();
});
