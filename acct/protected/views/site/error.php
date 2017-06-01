<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Message';
?>

<div class="grid_24 frame">
<div class="grid_24">
	<h2 class="page-heading"><?php echo Yii::t('misc','Message').' '.$code; ?></h2>
</div>
<div class="clear"></div>

<div class="grid_7 block">
	<div class="error">
		<?php echo CHtml::encode($message); ?>
	</div>	
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
