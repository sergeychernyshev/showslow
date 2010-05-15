<html>
<head>
<title><?php if (isset($TITLE)) { echo htmlentities($TITLE).' | '; } ?>Show Slow</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.1/build/fonts/fonts-min.css">
<style type="text/css">
body {
	margin:0;
	background-color: #98993D;
}

#header {
	padding: 1px;
	height: 60px;
	border-bottom: 2px solid black;
	margin: 0;
}

#header a {
	color: #403300;
}

#menu {
	background-color: #261F00;
	border-bottom: 2px solid black;
}

#menu a {
	color: #FFDE4C;
	padding: 0.8em;
	text-decoration: none;
	font-weight: bold;
}

#menu a:hover {
	background-color: #7F6F26;
	color: black;
}

#menu a.current {
	background-color: #7F6F26;
	color: white;
}

#menu td {
	padding: 0.3em 0.1em;
}

#footer {
	border-top: 2px solid black;
	padding: 5px;
	font-size: smaller;
}

#title {
	color: black; 
	text-decoration: none;
	padding: 0 0.5em;
}

#main {
	padding: 1px 1em 1em 1em;
	background: white;
}

#poweredby {
	float: right;
}

#navbox {
	float: right;
	margin-right: 1em;
}
</style>
<?php
if (isset($STYLES)) {
	foreach ($STYLES as $_style) {
		?><link rel="stylesheet" type="text/css" href="<?php echo $_style; ?>"/><?php
	}
}

if (isset($SCRIPTS)) {
	foreach ($SCRIPTS as $_script) {
		?><script type="text/javascript" src="<?php echo $_script; ?>"></script><?php
	}
}

if ($showFeedbackButton) {?>
<script type="text/javascript">
var uservoiceOptions = {
  /* required */
  key: 'showslow',
  host: 'showslow.uservoice.com', 
  forum: '18807',
  showTab: true,  
  /* optional */
  alignment: 'right',
  background_color:'#f00', 
  text_color: 'white',
  hover_color: '#06C',
  lang: 'en'
};

function _loadUserVoice() {
  var s = document.createElement('script');
  s.setAttribute('type', 'text/javascript');
  s.setAttribute('src', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
  document.getElementsByTagName('head')[0].appendChild(s);
}
_loadSuper = window.onload;
window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>
<?php } ?>
<?php if ($googleAnalyticsProfile) {?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $googleAnalyticsProfile ?>']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script');
ga.src = ('https:' == document.location.protocol ?
    'https://ssl' : 'http://www') +
    '.google-analytics.com/ga.js';
ga.setAttribute('async', 'true');
document.documentElement.firstChild.appendChild(ga);
})();
</script>
<?php }?>
</head>
<body class="yui-skin-sam">
<div id="header">
	<a href="<?php echo $showslow_base ?>"><img src="<?php echo assetURL('showslow_icon.png')?>" style="float: right; padding: 0.2em; margin-left: 1em; border: 0"/></a>
	<div id="poweredby">powered by <a href="http://www.showslow.org/">showslow</a></div>

	<div id="navbox">
	<?php
	if (!isset($current_user)) {
		$current_user = User::get();
	}

	if (!is_null($current_user))
	{?>
		<span id="username"><a href="<?php echo $showslow_base ?>users/edit.php" title="<?php echo htmlentities($current_user->getName())?>'s user information"><?php echo htmlentities($current_user->getName()) ?></a></span> | 
		<span id="logout"><a href="<?php echo $showslow_base ?>users/logout.php">logout</a></span>
	<?php }
	else
	{ ?>
		<span id="signup"><a href="<?php echo $showslow_base ?>users/register.php">Sign Up Now!</a></span> |
		<span id="login"><a href="<?php echo $showslow_base ?>users/login.php">log in</a></span>
	<?php }?>
	</div>

	<h1><a id="title" href="<?php echo $showslow_base ?>">Show Slow</a></h1>
	<div style="clear: both"></div>
</div>
<div id="menu">
<table><tr>
<?php if ($enableMyURLs) { ?><td><a <?php if ($SECTION == 'my') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>my.php">+ add URL</a></td><?php } ?>
<td><a <?php if ($SECTION == 'home') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>">Last 100 measurements</a></td>
<td><a <?php if ($SECTION == 'all') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>all.php">URLs measured</a></td>
<?php
$compareParams = '';
if (is_array($defaultURLsToCompare)) {
	$compareParams = '?';

	if ($defaultRankerToCompare == 'pagespeed') {
		$compareParams .= 'ranker=pagespeed&';
	}

	$first = true;
	foreach ($defaultURLsToCompare as $_url) {
		if ($first) {
			$first = false;	
		}
		else {
			$compareParams.= '&';
		}
		$compareParams.='url[]='.urlencode($_url);
	}
}
?>
<td><a <?php if ($SECTION == 'compare') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>details/compare.php<?php echo $compareParams?>">Compare rankings</a></td>
<td><a <?php if ($SECTION == 'configure') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>configure.php">Configuring YSlow / Page Speed</a></td>
<td><a href="http://code.google.com/p/showslow/source/checkout">Download ShowSlow</a></td>
</tr></table>
</div>
<div id="main">
