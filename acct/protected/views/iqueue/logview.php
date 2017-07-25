<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnLogClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'logviewdialog',
					'header'=>Yii::t('import','View Log'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
	<div class="form-group">
		<div class="col-sm-7">
			<?php echo TbHtml::textArea('log_content', '',array('rows'=>10,'cols'=>80,'readonly'=>true)); ?>
		</div>
	</div>
<?php
	$this->endWidget(); 
?>
