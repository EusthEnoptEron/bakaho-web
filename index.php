<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Bakaho - the Baka-Tsuki Reader</title>
		<link rel="stylesheet" href="themes/css/jqtouch.css" title="jQTouch">
		<link rel="stylesheet" href="style.css" title="jQTouch">

		<script src="js/lib/jquery-1.7.min.js" type="application/x-javascript" charset="utf-8"></script>

		<script src="js/jqtouch-jquery.js" type="application/x-javascript" charset="utf-8"></script>
		<script src="js/jqtouch.js" type="application/x-javascript" charset="utf-8"></script>
		<script src="js/underscore.js" type="application/x-javascript" charset="utf-8"></script>

		<script type="text/javascript" charset="utf-8">
		var jQT = new $.jQTouch({
			icon: 'img/icon.png',
			addGlossToIcon: false,
			startupScreen: 'img/startup.png',
			statusBar: 'black',
			preloadImages: []
		});
		</script>
		<script type="text/javascript" src="js/bakaho.js"></script>

	</head>
	<body>
		<div id="jqt">
			<div id="home">
				<div class="toolbar">
					<h1>Baka-Tsuki</h1>
				</div>
				<ul class="rounded">
				</ul>
			</div>
		</div>

		<div id="modal">
			<div></div>
		</div>


		<?php 
		//Load templates.
		$files = scandir('view');
		foreach($files as $file) {
			if(strpos($file, '.tmp')) {
				$name = substr($file, 0, strpos($file, '.tmp'));

				echo '<script type="text/template" class="template" id="template-'.$name.'">'
					 . file_get_contents('view' . DIRECTORY_SEPARATOR . $file)
					 . '</script>';
			}
		}

		?>
	</body>
</html>