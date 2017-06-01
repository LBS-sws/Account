<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<?php
// For download file use.
if (isset($url) && !empty($url)) {
	$self = Yii::app()->request->getHostInfo().Yii::app()->getBaseUrl();
	if (strpos($url,$self)!==false) {
		$js = "$(location).attr('href','$url');";
		Yii::app()->clientScript->registerScript('redirection',$js,CClientScript::POS_READY);
	}
}
?>