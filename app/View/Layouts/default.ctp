<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo __('foursquare to GoogleCalender') ?>:
		<?php echo $title_for_layout; ?>
	</title>

	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
		echo $this->Html->css('main.css');

		echo $this->Html->script('http://code.jquery.com/jquery-1.7.min.js');

		echo $scripts_for_layout;
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1><?php echo $this->Html->link('foursquare to GoogleCalender', '/'); ?></h1>
		</div>

		<div id="content">
			<?php echo $this->Session->flash(); ?>

			<?php echo $content_for_layout; ?>
		</div>

		<div id="footer">&copy; istb16 2011</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>