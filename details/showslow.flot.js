var SS = (function ($) {
	var	formatter = {
			bytes: function (val, axis) {
				return val / 1000000 + ' Mb';
			},
			msec: function (val, axis) {
				return val / 1000 + ' sec';
			},
			percent: function (val, axis) {
				return val + '%';
			}
		},

		_metrics,

		_graph,
		_graph_options = {
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				points: {
					show: false
				},
				shadowSize: 1
			},

			legend: {
				show: true,
				position: 'sw',
			},

			grid: {
				hoverable: true,
				autoHighlight: true,
				backgroundColor: {
					colors: ['#fff', '#ccc']
				},
				markingsLineWidth: 1,
				markingsColor: "#e51837"
			},

			colors: ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#000000','#800000', '#008000', '#000080', '#808000', '#800080', '#008080', '#808080','#C00000', '#00C000', '#0000C0', '#C0C000', '#C000C0', '#00C0C0', '#C0C0C0','#400000', '#004000', '#000040', '#404000', '#400040', '#004040', '#404040','#200000', '#002000', '#000020', '#202000', '#200020', '#002020', '#202020','#600000', '#006000', '#000060', '#606000', '#600060', '#006060', '#606060','#A00000', '#00A000', '#0000A0', '#A0A000', '#A000A0', '#00A0A0', '#A0A0A0','#E00000', '#00E000', '#0000E0', '#E0E000', '#E000E0', '#00E0E0', '#E0E0E0'],

			crosshair: {
				mode: 'x',
				color: '#000'
			},

			selection: {
				mode: 'x',
				color: 'blue'
			},

			// Single X-axis for dates
			xaxis: {
				position: 'bottom',
				mode: 'time'
			},

			// Multiple Y-axes for each datatype
			// Index starts at 1
			yaxes: [
				{// Bytes (1)
					position: 'right',
					min: 0,
					tickFormatter: formatter.bytes
				},
				{// Scores/Grades (Percentage) (2)
					label: 'Scores/Grades',
					min: 0,
					max: 100
				},
				{// Milliseconds (3)
					position: 'right',
					min: 0,
					minTickSize: 1000,
					tickSize: 1000,
					tickFormatter: formatter.msec
				},
				{// Numbers (4)
					position: 'right',
					min: 0
				},
				{// Percentages (5)
					position: 'right',
					min: 0,
					max: 100,
					tickFormatter: formatter.percent
				}
			],

		},

		_overview,
		_overview_options = {
			legend: {
				show: false
			},
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				points: {
					show: false
				},
				shadowSize: 0
			},
			xaxis: { mode: 'time' },
			yaxis: { ticks: [], min: 0, autoscaleMargin: 0.1 },
			selection: { mode: 'x', color: 'blue' }
		},

		data = [],
		dataset = {},

		previous_point = null;  // Tooltip tracking

	// Ensure identical colors on both graphs
	_overview_options.colors = _graph_options.colors;

	// Perform selection on little graph based on big graph selection
	$("#flot").bind("plotselected", function (event, ranges) {
		_graph = $.plot($("#flot"), data,
					  $.extend(true, {}, _graph_options, {
						  xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
					  }));
		_overview.setSelection(ranges, true);
	});


	// Perform zooming on big graph based on little graph selection
	$("#overview").bind("plotselected", function (event, ranges) {
		_graph.setSelection(ranges);
	});

	$("#overview").bind("plotunselected", function () {
		_resetZoomSelection();
        });

	// Track hovering over items to display tooltip
	$("#flot").bind("plothover", function (event, pos, item) {
		if (item) {
			if (previous_point != item.dataIndex) {
				previous_point = item.dataIndex;

				$("#tooltip").remove();
				var date = new Date(item.datapoint[0]).toUTCString(),
					value = item.datapoint[1],
					content = '';

				content = '<span>' + date + '</span><br/><br/><span>' + item.series.label + ': ' + value + '</span>';
				showTooltip(item.pageX, item.pageY, content);
			}
		} else {
			$("#tooltip").remove();
			previous_point = null;
		}
	});

	function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #fdd',
			padding: '2px',
			'background-color': '#fee',
			opacity: 0.80
		}).appendTo("body").fadeIn(200);
	}

	// populate event lines
	function _getEvents() {
		$.ajax({
			url: 'events.php',
			data: 'url=' + url,
			dataType: 'json',
			error: function (jqXHR, textStatus, errorThrown) {
				alert('There was a problem with events request:\n\n' + errorThrown + ' : ' + textStatus);
				return;
			},
			success: function (results) {
				_graph_options.grid.markings = results;
			}
		});
	}

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

				_graph = $.plot($('#flot'), data, _graph_options);
				_overview = $.plot($('#overview'), data, _overview_options);

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
			_graph = $.plot($('#flot'), data, _graph_options);
			_overview = $.plot($('#overview'), data, _overview_options);
		}
	}


	function _resetZoomSelection () {
		_graph = $.plot($('#flot'), data, _graph_options);
		_overview = $.plot($('#overview'), data, _overview_options);
	}

	$(document).ready(function() {
		_getEvents();

		_metrics = flot_metrics;

		$('.metric-toggle').each(function () {
			$(this).attr('checked', false);
		});

//		var load_functions = [];
//		var func_num = 0;

		for (var pid in default_metrics) {
			for (var i=0; i < default_metrics[pid].length; i++) {
				var checkbox = $('#' + pid + '-' + default_metrics[pid][i]);
				checkbox.attr('checked', 'true');
				checkbox.next().css('color', '#f00');
			}
					SS.getMetrics({
						url: url,
						provider: pid,
						metrics: default_metrics[pid].join(','),
						callback: false
					});

			// this can be used to load data sequentially
/*
			load_functions[func_num] = (function(provider_id, next_func) {
				return function() {
					SS.getMetrics({
						url: url,
						provider: provider_id,
						metrics: default_metrics[provider_id].join(','),
						callback: false
					}, load_functions[next_func]);
				};
			})(pid, func_num + 1);

			func_num += 1;
 */		}

//		load_functions[func_num] = false;
//
		// load first function
//		load_functions[0]();

		// Event handlers to all checkboxes to show/hide
		// individual metrics within graph
		$('.metric-toggle').each(function () {
			$(this).change(function () {
				var $this = $(this),
					id = $this.attr('id'), // Metric
					pid = $this.parents('fieldset').attr('id'), // Provider
					description = $this.next().text(), // Metric Description
					re = /^(\w+)\-(\w+)$/i; // Regex to distinguish between YSlow and Pagespeed basic measurements

				// Distinguish between YSlow and PageSpeed basic measurements
				if (re.test(id)) {
					id = id.replace(re, '$2');
				}

				if ($this.attr('checked')) {;
					SS.getMetrics({
						url: url,
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
		$.plot('#flot', [], {
			grid: {
				backgroundColor: {
					colors: ['#fff', '#ccc']
				}
			}
		});

		$.plot('#overview', []);
	});

	return {	// Publicly accessible methods.
		getMetrics: function (options, callback) {
			_getMetrics(options, callback);
		},

		removeSeries: function (provider, metric) {
			_removeSeries(provider, metric);
		},

		resetZoomSelection: function () {
			_resetZoomSelection();
		}
	}
})(jQuery);
