<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>PHP AJAX Image Upload, Truly Web 2.0!</title>
		<meta name="description" content="PHP AJAX Image Upload, Truly Web 2.0!" />
		<meta name="keywords" content="PHP AJAX Image Upload, Truly Web 2.0!" />
		<meta name="robots" content="index,follow" />
		<meta name="revisit-after" content="10 days" />
		<meta name="author" content="AT Web Results, Inc. - http://www.atwebresults.com" />
		<meta name="copyright" content="AT Web Results, Inc." />
		<meta name="distribution" content="global" />
		<meta name="resource-type" content="document" />
		<link href="css/styles.css" rel="stylesheet" type="text/css" media="all" />
		<!-- MAKE SURE TO REFERENCE THIS FILE! -->
		<script type="text/javascript" src="scripts/ajaxupload.js"></script>
		<!-- END REQUIRED JS FILES -->
		<!-- THIS CSS MAKES THE IFRAME NOT JUMP -->
		<style type="text/css">
			iframe {
				display:none;
			}
		</style>
		<!-- THIS CSS MAKES THE IFRAME NOT JUMP -->
	</head>
	<body>
		<div id='topblock'>
			<a href="http://atwebresults.com/" title="AT Web Results"><img src="images/logo.gif" width="120" height="120" alt="AT Web Results - Online Website Design Consultant" /></a>
			<h1>AT Web Results - <a href="http://atwebresults.com/php_ajax_image_upload/" title="PHP and AJAX Image Upload, Finally Revealed">PHP and AJAX Image Upload</a>, Finally Revealed</h1>
			<p>OK, I know it's not AJAX, but it sure feels like it! The truth is AJAX cannot upload images, it wont happen, it can't do it, stop your search and take a deep breathe as you begin to accept it! But never fear we have developed a cross browser solution that is comparable with every major JavaScript Library. Keep reading and check out the demo!<br /><br /><a href="http://atwebresults.com/forum/viewtopic.php?f=25&amp;t=391">Have questions? Check out the forum</a></p>
		</div>
		<div id="container">
			<p>Take a look at the 3 examples below, without looking at the source code you probably wouldn't realize its not AJAX. Well the cat is out of the bag its not! It actually uses the ugly nasty relics from the 90's, IFRAMES, urghhh. I know. Well you're thinking about it do a view source, you wont see it there. The JavaScript adds it in a hidden state and then when it is done with the dirty little thing it removes it. Pretty slick, huh.<br />
			<br />OK so play around with it and send me some feedback through the forum you will also find the full source code including the PHP image uploader I use in most of my scripts! Oh and before I forget, if any of this looks familiar it should there are a few of these out there, but none were exactly what I wanted.<br /><br />
			The license is very flexible, feel free to use this in your app's, commercial or otherwise, distribute it, package it, sell it, be prosperous! It's all good. All I ask is that if you improve it, change it, alter it post your code in the forum to make this code even better! Also make sure to keep all Copyright info intact and if it is a commercial application, please give credit where credit is due. If you find it <strong>really helped</strong> you out <strong>donations</strong> are always <strong>appreciated</strong>, see the link below.</p>
			<script type="text/javascript"><!--
				google_ad_client = "pub-0563899222267716";
				/* AJAX Image Upload Page */
				google_ad_slot = "4310161700";
				google_ad_width = 728;
				google_ad_height = 90;
				//-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"> </script>
			<br />
			<!-- THIS IS THE IMPORTANT STUFF! -->
			<div id="demo_area">
				<div id="left_col">
					<fieldset>
						<legend>Standard Use</legend>
						<!-- 
							VERY IMPORTANT! Update the form elements below ajaxUpload fields:
							1. form - the form to submit or the ID of a form (ex. this.form or standard_use)
							2. url_action - url to submit the form. like 'action' parameter of forms.
							3. id_element - element that will receive return of upload.
							4. html_show_loading - Text (or image) that will be show while loading
							5. html_error_http - Text (or image) that will be show if HTTP error.

							VARIABLE PASSED BY THE FORM:
							maximum allowed file size in bytes:
							maxSize		= 9999999999
							
							maximum image width in pixels:
							maxW			= 200
							
							maximum image height in pixels:
							maxH			= 300
							
							the full path to the image upload folder:
							fullPath		= http://www.atwebresults.com/php_ajax_image_upload/uploads/
							
							the relative path from scripts/ajaxupload.php -> uploads/ folder
							relPath		= ../uploads/
							
							The next 3 are for cunstom matte color of transparent images (gif,png), use RGB value
							colorR		= 255
							colorG		= 255
							colorB		= 255

							The form name of the file upload script
							filename		= filename
						-->
						<form action="scripts/ajaxupload.php" method="post" name="standard_use" id="standard_use" enctype="multipart/form-data">
							<p><input type="file" name="filename" /></p>
							<button onclick="ajaxUpload(this.form,'scripts/ajaxupload.php?filename=filename&amp;maxSize=9999999999&amp;maxW=200&amp;fullPath=http://www.atwebresults.com/php_ajax_image_upload/uploads/&amp;relPath=../uploads/&amp;colorR=255&amp;colorG=255&amp;colorB=255&amp;maxH=300&amp;custom_id=agca_header_logo_custom','upload_area','File Uploading Please Wait...&lt;br /&gt;&lt;img src=\'images/loader_light_blue.gif\' width=\'128\' height=\'15\' border=\'0\' /&gt;','&lt;img src=\'images/error.gif\' width=\'16\' height=\'16\' border=\'0\' /&gt; Error in Upload, check settings and path info in source code.'); return false;">Upload Image</button>
						</form>
					</fieldset>
					<fieldset>
						<legend>Sleeker More "Web 2.0" onChange Use</legend>
						<form action="scripts/ajaxupload.php" method="post" name="sleeker" id="sleeker" enctype="multipart/form-data">
							<input type="hidden" name="maxSize" value="9999999999" />
							<input type="hidden" name="maxW" value="200" />
							<input type="hidden" name="fullPath" value="http://www.atwebresults.com/php_ajax_image_upload/uploads/" />
							<input type="hidden" name="relPath" value="../uploads/" />
							<input type="hidden" name="colorR" value="255" />
							<input type="hidden" name="colorG" value="255" />
							<input type="hidden" name="colorB" value="255" />
							<input type="hidden" name="maxH" value="300" />
							<input type="hidden" name="filename" value="filename" />
							<p><input type="file" name="filename" onchange="ajaxUpload(this.form,'scripts/ajaxupload.php?filename=name&amp;maxSize=9999999999&amp;maxW=200&amp;fullPath=http://www.atwebresults.com/php_ajax_image_upload/uploads/&amp;relPath=../uploads/&amp;colorR=255&amp;colorG=255&amp;colorB=255&amp;maxH=300','upload_area','File Uploading Please Wait...&lt;br /&gt;&lt;img src=\'images/loader_light_blue.gif\' width=\'128\' height=\'15\' border=\'0\' /&gt;','&lt;img src=\'images/error.gif\' width=\'16\' height=\'16\' border=\'0\' /&gt; Error in Upload, check settings and path info in source code.'); return false;" /></p>
						</form>
					</fieldset>
					<fieldset>
						<legend>Unobtrusive (Falls Back to a Standard Form)</legend>
						<form action="scripts/ajaxupload.php" method="post" name="unobtrusive" id="unobtrusive" enctype="multipart/form-data">
							<input type="hidden" name="maxSize" value="9999999999" />
							<input type="hidden" name="maxW" value="200" />
							<input type="hidden" name="fullPath" value="http://www.atwebresults.com/php_ajax_image_upload/uploads/" />
							<input type="hidden" name="relPath" value="../uploads/" />
							<input type="hidden" name="colorR" value="255" />
							<input type="hidden" name="colorG" value="255" />
							<input type="hidden" name="colorB" value="255" />
							<input type="hidden" name="maxH" value="300" />
							<input type="hidden" name="filename" value="filename" />
							<p><input type="file" name="filename" id="filename" value="filename" onchange="ajaxUpload(this.form,'scripts/ajaxupload.php?filename=filename&amp;maxSize=9999999999&amp;maxW=200&amp;fullPath=http://www.atwebresults.com/php_ajax_image_upload/uploads/&amp;relPath=../uploads/&amp;colorR=255&amp;colorG=255&amp;colorB=255&amp;maxH=300','upload_area','File Uploading Please Wait...&lt;br /&gt;&lt;img src=\'images/loader_light_blue.gif\' width=\'128\' height=\'15\' border=\'0\' /&gt;','&lt;img src=\'images/error.gif\' width=\'16\' height=\'16\' border=\'0\' /&gt; Error in Upload, check settings and path info in source code.'); return false;" /></p>
							<noscript><p><input type="submit" name="submit" value="Upload Image" /></p></noscript>
						</form>
					</fieldset>
					<br /><small style="font-weight: bold; font-style:italic;">Supported File Types: gif, jpg, png</small>
				</div>
				<div id="right_col">
					<div id="upload_area">
						Images Will Be uploaded here for the demo.<br /><br />
						You can direct them to do any thing you want!
					</div>
					Help Spread the Word<br /><br />
					<script type="text/javascript">var addthis_pub="atwebresults";</script>
					<a href="http://www.addthis.com/bookmark.php?v=20" onmouseover="return addthis_open(this, '', '[URL]', '[TITLE]')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="http://s7.addthis.com/static/btn/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a><script type="text/javascript" src="http://s7.addthis.com/js/200/addthis_widget.js"></script>
					<div id="download_now"><a href="http://atwebresults.com/forum/viewtopic.php?f=25&amp;t=391" title="Download">Download</a></div>
					<div id="donate_now"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=3101401" title="Download">Donations</a></div>
				</div>
				<div class="clear"> </div>
			</div>
			<!-- END IMPORTANT STUFF -->
			<p style="text-align:center;">Compatible with all of today's popular browsers, including webkit browsers such as: Google Chrome and Safari. Also this has been tested with MooTools 1.1 and 1.2, Prototype, jQuery and no comparability issues were found!<br />
			<br /><img src="images/browsers.jpg" width="448" height="100" alt="AJAX Image Upload Browser compatibility" /></p>
			Pretty Cool, huh? Well kids I promised the source code for the perfect AJAX / PHP style image uploader. Please visit the forum for download and credits <a href="http://atwebresults.com/forum/viewtopic.php?f=25&amp;t=391" title="Download">Click Here</a><br /><br />
			<p><a rel="license" href="http://creativecommons.org/licenses/by/3.0/us/"><img alt="Creative Commons License" style="border-width:0; float:left; margin-right: 20px;margin-top:4px;" src="http://creativecommons.org/images/public/somerights20.png" /></a><small>PHP AJAX Image Upload Revealed by <a xmlns:cc="http://creativecommons.org/ns#" href="http://atwebresults.com/php_ajax_image_upload/" rel="cc:attributionURL">Tim Wickstrom</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/us/">Creative Commons Attribution 3.0 United States License</a>. Based on a work at <a xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://atwebresults.com/php_ajax_image_upload/" rel="dc:source">atwebresults.com</a></small></p>
			<br />
			<script type="text/javascript"><!--
				google_ad_client = "pub-0563899222267716";
				/* 728x90, created 2/7/09 */
				google_ad_slot = "8399216650";
				google_ad_width = 728;
				google_ad_height = 90;
				//-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"> </script>
			<br /><br /><br />
	</div>	
		<div id="footer">&copy; <?php $firstyr = 2005; $nextyr = date('Y'); if($nextyr <= $firstyr){ echo $firstyr; }else echo "$firstyr - $nextyr"; ?>, AT Web Results, Inc. - Using 100% W3C Valid <a href="http://validator.w3.org/check?uri=referer">xHTML {STRICT}</a> - <a href="http://jigsaw.w3.org/css-validator/check?uri=referer">CSS</a></div>
		<div style="position: fixed; right: 5px; bottom: 65px;"><img src="images/webbadge.gif" width="150" height="150" alt="Web 2.0" /></div>
</body>
</html>