<?php
#
# Smooths a series of data rows
#
# @rows - reference to array of rows each of which is an associative array of different values to smooth
# @to_smooth - array of metrics to smooth
# @size = distance between the value and the boundary - full range will be from x - size to x + size
#
# Return: rows are updated inplace - nothing is returned
#
function smooth(&$rows, $to_smooth) {
	global $smoothDistance;

	$total = count($rows);

	for ($i = 0; $i <= $total - 1; $i++) {

		# from and to define windown boudaries
		if ($smoothDistance * 2 + 1 > $total) { # window is bigger then the array
			$from = 0;
			$to = $total - 1;
		} else if ($i < $smoothDistance) {
			$from = 0; # window starts at the beginnning of array
			$to = $smoothDistance * 2;
		} else if ($i > $total - 1 - $smoothDistance) {
			$to = $total - 1; # widnow ends at the end of array
			$from = $total - $smoothDistance * 2 - 1;
		} else {
			$from = $i - $smoothDistance;
			$to = $i + $smoothDistance;
		}

		# j iterates through the window
		for ($j = $from; $j <= $to; $j++) {
			foreach ($to_smooth as $metric) {
				if (!array_key_exists($metric.'_avg', $rows[$i])) {
					$rows[$i][$metric.'_avg'] = 0;
				}

				$rows[$i][$metric.'_avg'] += $rows[$j][$metric];
			}
		}
	}

	$windowsize = ($smoothDistance * 2 + 1 > $total) ? $total : ($smoothDistance * 2 + 1);

	for ($i = 0; $i <= $total - 1; $i++) {
		foreach ($to_smooth as $metric) {
			$rows[$i][$metric] = sprintf('%.2f', $rows[$i][$metric.'_avg'] / $windowsize);
			unset($rows[$i][$metric.'_avg']);
		}
	}
}
