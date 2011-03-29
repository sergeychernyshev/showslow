<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/global.php');
?><html>
<head>
<title>DOM Monster + Show Slow</title>
<style>
.bookmarklet {
	padding: 3px 4px;
	margin: 0 3px;
	background: #dfdfdf;
	border: 1px solid gray;
	color: black;
	text-decoration: none;
	font-size: xx-small;
	font-family: verdana
}
</style>
</head>
<body>
<h1>DOM Monster + Show Slow bookmarklet</h1>
<h2>Installing on mobile devices</h2>

<h3>Method 1</h3>
<p>Bookmark this link: <a href="#javascript:(function(){SHOWSLOWINSTANCE%20='<?php echo $showslow_base?>';var%20script=document.createElement('script');script.src='<?php echo assetURL('beacon/dommonster/dom-monster/src/dommonster.js')?>?'+Math.floor((+new Date));document.body.appendChild(script);})()">DOM Monster + ShowSlow</a> and then edit it removing everything up to # symbol.</p>
<h3>Method 2</h3>
<p>
<ol>
<li>Bookmark this page</li>
<li>Select all code from the field below and copy to your clipboard<br/>
<input style="width: 100%; font-size: 1em" type="text" value="javascript:(function(){SHOWSLOWINSTANCE%20='<?php echo $showslow_base?>';var%20script=document.createElement('script');script.src='<?php echo assetURL('beacon/dommonster/dom-monster/src/dommonster.js')?>?'+Math.floor((+new Date));document.body.appendChild(script);})()"/></li>
<li>Edit bookmarklet you created in step one and paste the code instead of the URL of the page</li>
</ol>
</p>
<hr/>
<p><a href="./">&lt;&lt; back to beacon page</a></p>
</body></html>
