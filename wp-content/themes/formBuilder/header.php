<?php

?><!DOCTYPE html>
<!DOCTYPE html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<!-- Set the viewport width to device width for mobile -->
	<meta name="viewport" content="width=device-width" />

	<title>Test Install</title>

	<!-- Included CSS Files -->
	<link type="text/css" rel="stylesheet" href="<?php bloginfo('stylesheet_directory') ?>/components/bootstrap/bootstrap.css" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory') ?>/stylesheets/zice.style.css">



<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="components/flot/excanvas.min.js"></script><![endif]-->  
        
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/javascripts/jquery.min.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/ui/jquery.ui.min.js"></script> 
        <script type="text/javascript" src="c<?php bloginfo('stylesheet_directory') ?>/omponents/bootstrap/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/ui/timepicker.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/colorpicker/js/colorpicker.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/form/form.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/elfinder/js/elfinder.full.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/datatables/dataTables.min.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/fancybox/jquery.fancybox.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/jscrollpane/jscrollpane.min.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/editor/jquery.cleditor.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/chosen/chosen.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/validationEngine/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/validationEngine/jquery.validationEngine-en.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/fullcalendar/fullcalendar.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/flot/flot.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/uploadify/uploadify.js"></script>       
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/Jcrop/jquery.Jcrop.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/components/smartWizard/jquery.smartWizard.min.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/javascripts/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php bloginfo('stylesheet_directory') ?>/javascripts/zice.custom.js"></script>
        <script>
		$(document).ready(function() {
			// Form Cloning 
			var sheepItForm = $('#cloneForm').sheepIt({
				separator: '',
				allowRemoveLast: true,
				allowRemoveCurrent: true,
				allowRemoveAll: true,
				allowAdd: true,
				maxFormsCount: 10,
				minFormsCount: 0,
				iniFormsCount: 2
			});
		});
		</script>


	<!--[if lt IE 9]>
		<link rel="stylesheet" href="stylesheets/ie.css">
	<![endif]-->

	<script src="<?php bloginfo('stylesheet_directory') ?>/javascripts/modernizr.foundation.js"></script>

	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<?php wp_head(); ?>
</head>

<body>

<div class="container-fluid">