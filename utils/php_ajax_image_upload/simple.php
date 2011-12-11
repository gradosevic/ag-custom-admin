<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>	
		<script type="text/javascript" src="scripts/ajaxupload.js"></script>		
		<style type="text/css">
			iframe {
				display:none;
			}
		</style>
	</head>
	<body>	
	<form action="scripts/ajaxupload.php" method="post" name="sleeker" id="sleeker" enctype="multipart/form-data">
							<input type="hidden" name="maxSize" value="9999999999" />
							<input type="hidden" name="maxW" value="20000" />
							<input type="hidden" name="fullPath" value="" />
							<input type="hidden" name="relPath" value="../uploads/" />
							<input type="hidden" name="colorR" value="255" />
							<input type="hidden" name="colorG" value="255" />
							<input type="hidden" name="colorB" value="255" />
							<input type="hidden" name="maxH" value="30000" />
							<input type="hidden" name="filename" value="filename" />
							<input type="hidden" name="custom_id" value="agca_header_logo_custom" />
							<p><input type="file" name="filename" onchange="ajaxUpload(this.form,'scripts/ajaxupload.php','agca_header_logo_custom_upload_area','File Uploading Please Wait...&lt;br /&gt;&lt;img src=\'images/loader_light_blue.gif\' width=\'128\' height=\'15\' border=\'0\' /&gt;','&lt;img src=\'images/error.gif\' width=\'16\' height=\'16\' border=\'0\' /&gt; Error in Upload, check settings and path info in source code.'); return false;" /></p>
						</form><div id="agca_header_logo_custom_upload_area">Select photo for upload</div>
	
</body>
</html>