<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php 
		Yii::app()->bootstrap->bootstrapPath = Yii::app()->basePath.'/../../AdminLTE/plugins/bootstrap';
		Yii::app()->bootstrap->adminLtePath = Yii::app()->basePath.'/../../AdminLTE';
		Yii::app()->bootstrap->register(); 
	?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="language" content="<?php echo Yii::app()->language; ?>" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body class="hold-transition skin-black-light">
	<?php echo $content; ?>
	<?php $this->renderPartial('//site/dialog'); ?>
</body>
</html>
