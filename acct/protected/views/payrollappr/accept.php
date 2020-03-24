<?php
	$ftrbtn = array(
				TbHtml::button(Yii::t('misc','OK'), array('id'=>'btnAccOk',
																'data-dismiss'=>'modal',
																'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
																'submit'=>Yii::app()->createUrl('payrollappr/accept'),
															)),
				TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnAccClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY))
			);
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'acceptdialog',
					'header'=>Yii::t('trans','Please enter reason').Yii::t('trans','(if any)'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
	<div class="form-group">
		<?php echo $form->label($model,'reason_accept',array('class'=>"col-sm-2 control-label")); ?>	
		<div class="col-sm-7">
			<?php echo $form->textArea($model, 'reason_accept', 
					array('rows'=>3,'cols'=>60,'maxlength'=>1000)
			); ?>
		</div>
	</div>
	
<?php
	$this->endWidget(); 
?>
