/*global custom_metric_colors: true, url: true, default_metrics: true, flot_metrics: true, jQuery: false */
var SS = (function ($) {
	var	formatter = {
			bytes: function (val, axis) {
				return val / 1000 + ' Kb';
			},
			msec: function (val, axis) {
				return val + ' msec';
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
					show: true
				},
				shadowSize: 1
			},

			legend: {
				show: true,
				position: 'sw'
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

			colors: ['#F00', '#0F0', '#00F', '#FF0', '#F0F', '#0FF', '#D00', '#0D0', '#00D', '#DD0', '#D0D', '#0DD', '#B00', '#0B0', '#00B', '#BB0', '#B0B', '#0BB', '#A00', '#0A0', '#00A', '#AA0', '#A0A', '#0AA', '#900', '#090', '#009', '#990', '#909', '#099', '#800', '#080', '#008', '#880', '#808', '#088', '#600', '#060', '#006', '#660', '#606', '#066', '#400', '#040', '#004', '#440', '#404', '#044', '#000'].concat(custom_metric_colors),

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
					label: 'Scores/Grades'
//					min: 0,
//					max: 100
				},
				{// Milliseconds (3)
					position: 'right',
					min: 0,
					//max: 30000,
					minTickSize: 1000,
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
			]
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
			selection: { mode: 'x', color: 'blue' },
			grid: {
				markingsLineWidth: 1,
				markingsColor: "#e51837"
			}
		},

		data = [],
		dataset = {},

		previous_point = null;  // Tooltip tracking

	// Ensure identical colors on both graphs
	_overview_options.colors = _graph_options.colors;

	function showEventTooltip(x, y, contents) {
		$('<div id="eventtooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid #3b5999',
			padding: '0.7em',
			'background-color': '#3b5999',
			'color': 'white',
			opacity: 0.90
		}).appendTo("body").fadeIn(200);
	}

	function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			border: '1px solid black',
			padding: '0.7em',
			'background-color': 'black',
			'color': 'white',
			opacity: 0.90
		}).appendTo("body").fadeIn(200);
	}

	// populate event lines
	function _getEvents() {
		$.ajax({
			url: SHOWSLOW.base_url+'details/events2.php',
			data: 'url=' + encodeURIComponent(url) + '&ver=' + encodeURIComponent(eventversion),
			dataType: 'json',
			cache: true,
			error: function (jqXHR, textStatus, errorThrown) {
				alert('There was a problem with events request:\n\n' + errorThrown + ' : ' + textStatus);
				return;
			},
			success: function (results) {
				_graph_options.grid.markings = results;
				_overview_options.grid.markings = results;
			}
		});
	}

	function _getMetrics (options, callback) {
		if (typeof(callback) !== 'function') { callback = false; }

		$.ajax({
			url: SHOWSLOW.base_url+'details/data2.php',
			dataType: 'json',
			cache: true,
			data: 'urlid=' + encodeURIComponent(options.urlid) + '&provider=' + encodeURIComponent(options.provider) + '&metrics=' + encodeURIComponent(options.metrics) + '&format=json&ver=' + encodeURIComponent(flot_versions[options.provider]),
			error: function (jqXHR, textStatus, errorThrown) {
				alert('There was a problem with the request:\n\n' + errorThrown + ' : ' + textStatus);
				return;
			},
			success: function (results) {
				// reordering the array values from earliest to latest
				results.sort(function(a, b) {
					return (a[0] - b[0]);
				});

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

				$('#clear').removeAttr('disabled');

				if (callback) { callback(results); }
			}
		});
	}

	function _removeSeries (provider, metric) {
		if (typeof(dataset[provider + '-' + metric]) !== 'undefined') {
			delete dataset[provider + '-' + metric];
			data = [];
			$.each(dataset, function (key, val) {
				data.push(val);
			});
			_graph = $.plot($('#flot'), data, _graph_options);
			_overview = $.plot($('#overview'), data, _overview_options);
		}
	}

	function _clearMetrics() {
		$('.metric-toggle').each(function () {
			$(this).attr('checked', false);
		});
		$('.metric-toggle').change();

		$('#clear').attr('disabled', 'disabled');
	}

	function _resetZoomSelection() {
		_graph = $.plot($('#flot'), data, _graph_options);
		_overview = $.plot($('#overview'), data, _overview_options);
		$('#reset').attr('disabled', 'disable');
	}

	function _setDefaultMetrics() {
//		var load_functions = [];
//		var func_num = 0;

		_clearMetrics();

		for (var pid in default_metrics) {
			if (default_metrics.hasOwnProperty(pid)) {
				for (var i=0; i < default_metrics[pid].length; i++) {
					var checkbox = $('#' + pid + '-' + default_metrics[pid][i]);

					checkbox.attr('checked', 'true');
					checkbox.next().css('color', '#f00');

					// loading custom metrics one by one
					if (pid == 'custom') {
						SS.getMetrics({
							urlid: urlid,
							provider: pid,
							metrics: default_metrics[pid][i],
							callback: false
						});
					}
				}

				if (pid != 'custom') {
					SS.getMetrics({
						urlid: urlid,
						provider: pid,
						metrics: default_metrics[pid].join(','),
						callback: false
					});
				}

				// this can be used to load data sequentially
/*				load_functions[func_num] = (function(provider_id, next_func) {
					return function() {
						SS.getMetrics({
							urlid: urlid,
							provider: provider_id,
							metrics: default_metrics[provider_id].join(','),
							callback: false
						}, load_functions[next_func]);
					};
				})(pid, func_num + 1);

				func_num += 1;
 */
			}
		}

//		load_functions[func_num] = false;
//
		// load first function
//		load_functions[0]();

	}

	var updateLegendTimeout = null;
	var latestPosition = null;

	function updateLegend() {
		var legends = $("#flot .legendLabel");

		updateLegendTimeout = null;

		var pos = latestPosition;

		var axes = _graph.getAxes();
		if (pos.x < axes.xaxis.min ||
			pos.x > axes.xaxis.max ||
			pos.y < axes.yaxis.min ||
			pos.y > axes.yaxis.max
		) {
			return;
		}

		var i, j, dataset = _graph.getData();
		for (i = 0; i < dataset.length; ++i) {
			var series = dataset[i];

			// find the nearest points, x-wise
			for (j = 0; j < series.data.length; ++j) {
				var index = j;
				if (series.data[0][0] > series.data[series.data.length - 1][0]) {
					// if we're working in revere order, then start from the other end
					index = series.data.length - 1 - j;
				}

				if (series.data[j][0] > pos.x) {
					break;
				}
			}

			// using previous point
			var y, p1 = series.data[j - 1], p2 = series.data[j];
			if (typeof(p1) === 'undefined') {
				y = p2[1];
			} else {
				y = p1[1];
			}

			legends.eq(i).text(series.label + ': ' + series.yaxis.tickFormatter(y, series.yaxis));
		}
	}

	// Track hovering over items to display tooltip
	$("#flot").bind("plothover", function (event, pos, item) {
		$("#eventtooltip").remove();

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

			var marking;
			var marking_start_coords;
			var marking_end_coords;

			var cursor_coords = _graph.pointOffset(pos);

			var markings = _graph_options.grid.markings;
			var mark_num = markings.length;
			for (var i = 0; i < mark_num; i += 1) {
				marking = markings[i];

				marking_start_coords = _graph.pointOffset({ x: marking.xaxis.from, y: 0 });
				marking_end_coords = _graph.pointOffset({ x: marking.xaxis.to, y: 0 });

				if (cursor_coords.left >= marking_start_coords.left - 2 &&
					cursor_coords.left <= marking_end_coords.left + 2
				) {
					showEventTooltip(pos.pageX, pos.pageY,
						$('<div/>').text(marking.type).html() +
						': <b>' + $('<div/>').text(marking.title).html() + '</b>'
					);

					break; // try to show only first tooltip
				}
			}
		}
	});

	// Perform selection on little graph based on big graph selection
	$("#flot").bind("plotselected", function (event, ranges) {
		_graph = $.plot($("#flot"), data,
					  $.extend(true, {}, _graph_options, {
						  xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
					  }));
		_overview.setSelection(ranges, true);
		$('#reset').removeAttr('disabled');
	});

	// Perform zooming on big graph based on little graph selection
	$("#overview").bind("plotselected", function (event, ranges) {
		_graph.setSelection(ranges);
		$('#reset').removeAttr('disabled');
	});

	$("#overview").bind("plotunselected", function () {
		_resetZoomSelection();
        });


	$("#flot").bind("plothover",  function (event, pos, item) {
		latestPosition = pos;
		if (!updateLegendTimeout) {
			updateLegendTimeout = setTimeout(updateLegend, 50);
		}
	});

	$(document).ready(function() {
		_getEvents();

		_metrics = flot_metrics;

		_setDefaultMetrics();

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

				if ($this.attr('checked')) {
					SS.getMetrics({
						urlid: urlid,
						provider: pid,
						metrics: id,
						callback: false
					});
					$this.next().css('color', '#f00');
				} else {
					SS.removeSeries(pid, id);
					$this.next().css('color', '');
				}
			});
		});

		// Attach methods to buttons
		$('#reset').click(_resetZoomSelection);
		$('#clear').click(_clearMetrics);
		$('#default').click(_setDefaultMetrics);

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
	};
})(jQuery);
