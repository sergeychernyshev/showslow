<?php 
require_once(dirname(__FILE__).'/global.php');

if (!is_null($webPageTestKey) && array_key_exists('compare', $_POST) && is_array($_POST['compare']))
{
	$comparison_url = $webPageTestBase.'video/compare.php?tests=';

	$repeat = 0;
	if (array_key_exists('repeat', $_POST)) {
		$repeat = 1;
	}

	$labels = null;
	if (array_key_exists('label', $_POST)) {
		$labels = array_reverse($_POST['label']);
	}

	$first = true;
	foreach (array_reverse($_POST['compare']) as $test_id) {
		if ($first) {
			$first = false;
		} else {
			$comparison_url .= ',';
		}
		$comparison_url .= $test_id.'-r:1-c:'.$repeat; // -l:labelname

		if (is_array($labels) && count($labels) > 0) {
			$label = str_replace('-', '/', array_shift($labels));
			$label = str_replace(':', '.', $label);
			$comparison_url .= '-l:'.urlencode($label);
		}
	}

	header('Location: '.$comparison_url);
	exit;
}

header('Location: '.$showslow_base);
