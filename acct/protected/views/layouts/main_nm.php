<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php 
		Yii::app()->bootstrap->bootstrapPath = Yii::app()->basePath.'/../../bootstrap-3.3.7-dist';
		Yii::app()->bootstrap->adminLtePath = Yii::app()->basePath.'/../../AdminLTE-2.3.7';
		Yii::app()->bootstrap->register(); 
	?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="language" content="<?php echo Yii::app()->language; ?>" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body class="hold-transition skin-black-light">
	<?php echo $content; ?>
	<?php $this->renderPartial('//site/dialog'); ?>
</body>
</html>
