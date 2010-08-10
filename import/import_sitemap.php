<?php
// this tool accepts a list of user IDs and sitemap URLs to import into user account for monitoring
// The list is tab separated like so:
//
//	1	http://www.showslow.com/sitemap.xml
//	1	http://www.sergeychernyshev.com/sitemap.xml

require_once(dirname(dirname(__FILE__)).'/global.php');

$user_id = null; 
$temp_path = '/tmp/';
$depth = array();

$inLocTag = false;
$buffer = '';

function startElement($parser, $name, $attrs) 
{
	global $inLocTag, $buffer;

	if (strtolower($name) == 'loc') {
		$buffer = '';
		$inLocTag = true;
	}
}

function endElement($parser, $name) 
{
	global $inLocTag, $buffer, $user_id;

	if (strtolower($name) == 'loc') {
		$inLocTag = false;

		// Now, let's process the contents
		$url = $buffer;
		$buffer = '';

		$url_id = getUrlId(resolveRedirects($url), false);

		if (is_null($url_id)) {
			error_log("Troubles getting / creating a URL for $url. Skipping.");
			return;
		}

		$query = sprintf("INSERT IGNORE INTO user_urls (user_id, url_id) VALUES (%d, %d)",
			$user_id,
			$url_id
		);

		$result = mysql_query($query);

		if (!$result) {
			error_log(mysql_error());
		}
	}
}

function charData($xml_parser, $data)
{
	global $buffer;
	$buffer .= $data;
}


if ($list_fp = fopen('php://stdin', 'r')) {
	while ($line = fgets($list_fp)) {
		$params = explode("\t", $line);

		$user_id = trim($params[0]);
		$url = trim($params[1]);

		$tempfile = $temp_path . 'showslow_import_sitemap.xml.'.getmypid().'.'.time();
		$temp_fp = fopen($tempfile, 'w');

		// Now, let's download the sitemap
		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_FILE => $temp_fp,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_MAXREDIRS => 10
		));

		$curl_success = curl_exec($ch);

		curl_close($ch);
		fclose($temp_fp);

		if (!$curl_success) {
			error_log("Can't download the the sitemap: $url");
			continue;
		}

		// Now, let's open and parse the file
		if (!($fp = fopen($tempfile, "r"))) {
			error_log("Could not open XML input: $tempfile");
			continue;
		}

		$xml_parser = xml_parser_create();

		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser, "charData");

		while ($data = fread($fp, 4096)) {
		    if (!xml_parse($xml_parser, $data, feof($fp))) {
			die(sprintf("XML error: %s at line %d",
				    xml_error_string(xml_get_error_code($xml_parser)),
				    xml_get_current_line_number($xml_parser)));
		    }
		}
		fclose($fp);
		unlink($tempfile);

		xml_parser_free($xml_parser);
	}
}
fclose($list_fp);
