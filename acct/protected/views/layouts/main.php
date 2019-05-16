<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php 
		Yii::app()->bootstrap->bootstrapPath = Yii::app()->basePath.'/../../AdminLTE/bower_components/bootstrap';
		Yii::app()->bootstrap->adminLtePath = Yii::app()->basePath.'/../../AdminLTE';
		Yii::app()->bootstrap->register(); 

		$sfile = Yii::app()->baseUrl.'/js/dms.js';
		Yii::app()->clientScript->registerScriptFile($sfile,CClientScript::POS_HEAD);
	?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="language" content="<?php echo Yii::app()->language; ?>" />
	
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body class="hold-transition <?php echo Yii::app()->params['appcolor']; ?> sidebar-mini">
<div class="wrapper">

	<header class="main-header">
		<!-- Logo -->
		<a href="<?php echo Yii::app()->baseUrl; ?>" class="logo">
			<!-- mini logo for sidebar mini 50x50 pixels -->
			<span class="logo-mini"><b>LBS</b></span>
			<!-- logo for regular state and mobile devices -->
			<span class="logo-lg"><?php echo CHtml::encode(Yii::t('app',Yii::app()->params['appname'])); ?></span>
		</a>

		<nav class="navbar navbar-static-top">
			<!-- Sidebar toggle button-->
			<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
				<?php
					$sysmap = General::systemMapping();
					$sysId = Yii::app()->session['system'];
					$sysTitle = $sysmap[$sysId]['name'];
					$sysIcon = $sysmap[$sysId]['icon'];
				?>
			<button id='btnSysChange' type='button' class='btn btn-default navbar-btn navbar-left' data-toggle='tooltip' data-placement='bottom' title='<?php echo Yii::t('app','System Change'); ?>'>
				<?php echo Yii::t('app',$sysTitle); ?>
			</button>
			<a href="<?php echo Yii::app()->baseUrl; ?>" class="navbar-brand">
				<small><?php echo Yii::t('app','Region').': '.Yii::app()->user->city_name(); ?></small>
			</a>

			<!-- Navbar Right Menu -->
			<div class="navbar-custom-menu">
				<ul class="nav navbar-nav">
				<?php
					$this->widget('ext.layout.NoticeWidget');
					$this->widget('ext.layout.UserMenuWidget');
				?>
				</ul>
			</div>
			<!-- /.container-fluid -->
		</nav>
	</header>

	<?php
		$this->widget('ext.layout.SidebarWidget',array('config'=>Yii::app()->basePath.'/config/menu.php',));
	?>

	<?php $this->widget('ext.widgets.loading.LoadingWidget'); ?>
	
	<!-- Full Width Column -->
	<div class="content-wrapper">
<!--		<div class="container"> -->
			<?php echo $content; ?>
<!--		</div> -->
		<!-- /.container -->
	</div>
	<!-- /.content-wrapper -->
	
	<?php $this->renderPartial('//site/dialog'); ?>
	<?php $this->renderPartial('//site/system'); ?>

	<footer class="main-footer">
		<div class="pull-right hidden-xs">
			<b><?php echo Yii::t('app',$sysTitle);?></b> <small><?php echo Yii::t('app','Last Update Date ');?> <?php echo General::getUpdateDate();?></small>
		</div>
		<strong>Copyright &copy; 2016-<?php echo date('Y'); ?> <a href="http://www.lbsgroup.com.hk">LBS Group</a>.</strong> <?php echo Yii::t('misc', 'All rights reserved'); ?>
	</footer>
</div>
<!-- ./wrapper -->

</body>
<?php
if (!Yii::app()->user->isGuest) {
	$this->widget('ext.layout.SessionExpiryWidget');		// Session Expiry
	$this->widget('ext.layout.AjaxNotifyWidget');			// Notification Counter Refresh
	$this->widget('ext.layout.SystemButtonWidget');			// System Change
	$this->widget('ext.layout.NotifyBadgeWidget',
		array('config'=>Yii::app()->basePath.'/config/notifybadge.php',
			'url'=>Yii::app()->createUrl('ajax/notifybadge'),
		)
	);
}
?>
</html>
