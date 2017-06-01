<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnDtlClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'dtlviewdialog',
					'header'=>Yii::t('dialog','Transaction Detail'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
<div class="box box-info" style="max-height: 350px; overflow-y: auto;">
	<table id="dtl-list" class="table table-bordered">
	</table>
</div>
<?php
	$this->endWidget(); 
?>
