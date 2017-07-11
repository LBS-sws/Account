<?php
	$ftrbtn = array(
				TbHtml::button(Yii::t('misc','OK'), array('id'=>'btnRmkOk',
																'data-dismiss'=>'modal',
																'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
																'submit'=>Yii::app()->createUrl('transin/delete'),
															)),
				TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnRmkClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY))
			);
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'rmkdialog',
					'header'=>Yii::t('trans','Please enter reason'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
	<div class="form-group">
		<?php echo $form->label($model,'reason',array('class'=>"col-sm-2 control-label")); ?>	
		<div class="col-sm-7">
			<?php echo $form->textArea($model, 'reason', 
					array('rows'=>3,'cols'=>60,'maxlength'=>1000)
			); ?>
		</div>
	</div>
	
<?php
	$this->endWidget(); 
?>
