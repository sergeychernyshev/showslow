<?php require_once(dirname(__FILE__).'/users/users.php');

if (!isset($TITLE)) {
	$TITLE = null;
}
if (!isset($SECTION)) {
	$SECTION = null;
}

if (!isset($current_user)) {
	$current_user = User::get();
}

if (!isset($noMoreURLs)) {
	$noMoreURLs = false;
}

if (array_key_exists('group', $_GET)) {
	$current_group = $_GET['group'];
} else if (!is_null($DefaultURLGroup)) {
	$current_group = $DefaultURLGroup;
} else {
	$current_group = '__show_all__';
}

?><!DOCTYPE HTML>
<html version="HTML+RDFa 1.1" lang="en"
	xmlns:og="http://opengraphprotocol.org/schema/"
	xmlns:fb="http://developers.facebook.com/schema/"
>
<head>
<title><?php if (isset($TITLE)) { echo htmlentities($TITLE).' | '; } ?>Show Slow</title>
<link rel="stylesheet" media="all" type="text/css" href="<?php echo assetURL('css/stacklayout.css')?>" />
<!--[if lte IE 7]>
<link rel="stylesheet" media="all" type="text/css" href="<?php echo assetURL('css/stacklayout_lte_ie7.css')?>" />
<![endif]-->
<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo assetURL('css/common.css')?>" />
<?php StartupAPI::head() ?>
<script>
    var SHOWSLOW = SHOWSLOW || {};
    SHOWSLOW.base_url = <?php echo json_encode($showslow_base); ?>;
</script>
<?php
if (isset($STYLES)) {
	foreach ($STYLES as $_style) {
		?><link rel="stylesheet" type="text/css" href="<?php echo $_style; ?>"/><?php
	}
}

if (isset($SCRIPTS)) {
	foreach ($SCRIPTS as $_script) {
		if (is_array($_script)) {
			if (array_key_exists('condition', $_script)) {
				?><!--[<?php echo $_script['condition'] ?>]><script type="text/javascript" src="<?php echo $_script['url']; ?>"></script><![endif]-->
<?php
			}
		} else {
			?><script type="text/javascript" src="<?php echo $_script; ?>"></script>
<?php
		}
	}
}

if ($kissMetricsKey) {
?><script type="text/javascript">
  var _kmq = _kmq || [];
  function _kms(u){
    setTimeout(function(){
      var s = document.createElement('script'); var f = document.getElementsByTagName('script')[0]; s.type = 'text/javascript'; s.async = true;
      s.src = u; f.parentNode.insertBefore(s, f);
    }, 1);
  }
  _kms('//i.kissmetrics.com/i.js');_kms('//doug1izaerwt3.cloudfront.net/<?php echo $kissMetricsKey ?>.1.js');
</script>
<?php
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
<?php if ($googleAnalyticsProfile && !excludeGoogleAnalytics()) {?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?php echo $googleAnalyticsProfile ?>']);
_gaq.push(['_setAllowAnchor', true]);
_gaq.push(['_setCustomVar', 1, 'User Type', <?php if (is_null($current_user)) { ?>'Anonymous'<?php }else{ ?>'Member'<?php } ?>, 2]);
_gaq.push(['_trackPageview']);
_gaq.push(['_trackPageLoadTime']);

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

<style type="text/css">

td, th { white-space: nowrap; }

.score {
	text-align: right;
	padding: 0 10px 0 10px;
}

.gbox {
	background-color: #EFEFEF;
	background: -webkit-gradient(linear, left top, left bottom, from(#EFEFEF), to(#CFCFCF));
	background: -moz-linear-gradient(top, #EFEFEF, #CFCFCF);
	width: 101px;
}

.moreinfo {
	width: 14px;
	height: 14px;
	background-image: url('<?php echo assetURL('info.png')?>');
}
.ccol {
	background-image: url('<?php echo assetURL('collecting.gif')?>')
}

.url {
	padding-left:10px;
}

.bar {
	height: 15px;
}

<?php for($i=1; $i<=count($colorSteps); $i++) {?>
.c<?php echo $i; ?> {
	background: #<?php echo $colorSteps[$i-1]; ?>;
	background: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $colorSteps[$i-1]; ?>), to(#<?php echo $colorStepShades[$i-1]; ?>));
	background: -moz-linear-gradient(top, #<?php echo $colorSteps[$i-1]; ?>, #<?php echo $colorStepShades[$i-1]; ?>);
}
<?php } ?>
</style>

<?php if ($homePageMetaTags && $SECTION == 'home') { echo $homePageMetaTags; } ?>
<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo assetURL('favicon.ico')?>">
<link rel="apple-touch-icon" href="<?php echo assetURL('showslow_iphone_icon.png')?>" />

<?php if ($extraHeadHTML) { echo $extraHeadHTML; } ?>
</head>
<body>
<div class="stack">
	<div id="header">
		<div class="stackContent">
			<h1 style="color: white"><a href="<?php echo $showslow_base ?>"><img src="<?php echo assetURL('img/logo-shadow.png')?>" width="72" height="70" alt="Show Slow" /></a> Is your website <b>getting faster</b>?
				<?php if ($enableMyURLs && is_null($current_user)) {?><a class="btn btn-warning" href="<?php echo $showslow_base.'/users/register.php' ?>">Sign up now!</a><?php } ?>
			</h1>

<?php if ($enableMyURLs) {?>
			<ul id="headerNav">
				<?php include(dirname(__FILE__).'/users/navbox.php'); ?>
			</ul>
<?php } ?>

		</div><!-- stackContent -->
	</div><!-- header -->

	<div id="topNav">
		<div class="stackContent">
			<ul>

<?php if ($enableMyURLs) { ?><li><a <?php if ($SECTION == 'my') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>my.php">+ Add a URL</a></li><?php } ?>
<li><a <?php if ($SECTION == 'home') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>">Last measurements</a></li>
<li><a <?php if ($SECTION == 'all') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>all.php">URLs measured</a></li>
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
<li><a <?php if ($SECTION == 'compare') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>details/compare.php<?php echo $compareParams?>">Compare rankings</a></li>
<?php
foreach ($customLists as $list_id => $list) {
	if (array_key_exists('hidden', $list) && $list['hidden']) {
		continue;
	}

	?><li><a <?php if ($SECTION == 'custom_list_'.$list_id) {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>list.php?id=<?php echo $list_id; ?>"><?php echo $list['title'] ?></a></li><?php
}

foreach ($additionalMenuItems as $menu_item) {
	?><li><a href="<?php echo htmlentities($menu_item['url']) ?>"><?php echo htmlentities($menu_item['title']) ?></a></li><?php
}
?>
<li><a <?php if ($SECTION == 'configure') {?>class="current" <?php } ?>href="<?php echo $showslow_base ?>configure.php">Configuring tools</a></li>
<li><a href="http://www.showslow.org/downloads/">Download ShowSlow</a></li>

			</ul>
		</div><!-- stackContent -->
	</div><!-- topNav -->

<?php
if (!isset($MESSAGES)) {
	$MESSAGES = null;
}

if (is_array($MESSAGES) && count($MESSAGES > 0)) {
	?><div id="messages">
	<?php
	foreach ($MESSAGES as $message) {
		$messagetype = '';
		if (is_array($message)) {
			$messagetype = $message[0];
			$message = $message[1];
		}
		?>
		<div class="message<?php echo $messagetype ?>">
			<div class="stackContent">
				<h3><?php echo $message ?></h3>
			</div><!-- stackContent -->
		</div><!-- message -->
		<?php
	}
	?></div><!-- messages -->
	<?php
}

if (!$noMoreURLs && $enableMyURLs && ($SECTION == 'home' || $SECTION == 'my')) { ?>
	<div id="feature">
		<div class="stackContent">
			<form class="form form-inline" style="margin-bottom: 0" action="<?php echo $showslow_base ?>my.php" method="GET">
			<span style="margin-right: 0.5em">Add your URL to be monitored</span>
			<input class="input-xlarge" style="margin-right: 0.5em" type="url" placeholder="http://www.example.com/" size="60" name="url"<?php if ($noMoreURLs) {?> disabled="disabled"<?php } ?>/>
			<input class="btn" type="submit" name="add" value="Add URL" title="add URL to be measured"<?php if ($noMoreURLs) {?> disabled="disabled"<?php } ?>/>
			</form>
		</div><!-- stackContent -->
	</div><!-- feature -->
<?php
}

if ($SECTION == 'all') { ?>
	<div id="search">
		<div class="stackContent">
		<form class="form-inline" style="margin-bottom: 0" name="searchform" action="<?php echo $showslow_base ?>all.php" method="GET">

		<input class="search-query input-xlarge" placeholder="Search" type="search" id="urlsearch" size="60" name="search" value="<?php echo array_key_exists('search', $_GET) ? htmlentities(trim($_GET['search'])) : ''?>"/>
			<button class="btn" type="submit"><i class="icon-search"></i> Search URLs</button>

			<?php if ($DefaultURLGroup != $current_group) { ?>
			<input type="hidden" name="group" value="<?php echo htmlentities($current_group) ?>"/>
			<?php }

			$searched = false;
			if (array_key_exists('search', $_GET) && $_GET['search'] != '') {
				$searched = true;
			}
			?>

			<a class="btn<?php if ($searched) { ?>" onclick="document.getElementById('urlsearch').value=''; document.searchform.submit()"<?php } else { ?> disabled"<?php } ?> href="#"><span class="close<?php if (!$searched) { ?> disabled<?php } ?> pull-left" style="margin-right: 0.5em">×</span> Clear</a>
			</form>
		</div><!-- stackContent -->
	</div><!-- feature -->
<?php
}

if ($SECTION == 'home' && !is_null($ShowSlowIntro)) { ?>
	<div id="writeUp">
		<div class="stackContent">
			<?php echo $ShowSlowIntro ?>
		</div><!-- stackContent -->
	</div><!-- writeUp -->
<?php } ?>

<div id="main">
<div class="stackContent">
