<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="en" />
<!--
<link rel="stylesheet" type="text/css" href="<?php echo  Yii::app()->request->baseUrl; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
-->
<title><?php echo $this->pageTitle; ?></title>
</head>

<frameset id="myframeset" rows="40,*" frameborder=0 noresize framespacing=0>
<frame frameborder=0 name="sip" id="sip" src="<?php echo Yii::app()->createUrl('site/voip'); ?>" scrolling = "no" noresize marginwidth="0" marginheight=0>
<frame frameborder=0 name="main" id="main" src="<?php echo Yii::app()->createUrl('site/home'); ?>" marginwidth="0" marginheight="0" >
</frameset>

</html>