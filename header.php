<html>
<head>
<title>Show Slow</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/fonts/fonts-min.css&2.7.0/build/tabview/assets/skins/sam/tabview.css">
<style type="text/css">
body {
	margin:0;
	background-color: #FC0;
}

#header {
	padding: 1px;
	height: 60px;
	border-bottom: 2px solid black;
	margin: 0;
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
<?php if ($showFeedbackButton) {?>
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
	<a href="<?php echo $showslow_base ?>"><img src="<?php echo $showslow_base ?>showslow_icon.png" style="float: right; padding: 0.2em; margin-left: 1em; border: 0"/></a>
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

	<div id="<?php ?>"></div>
	<h1><a id="title" href="<?php echo $showslow_base ?>">Show Slow</a></h1>
	<div style="clear: both"></div>
</div>
<div id="main">
