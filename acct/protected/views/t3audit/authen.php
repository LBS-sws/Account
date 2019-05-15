<?php
	$ftrbtn = array(
				TbHtml::button(Yii::t('misc','Confirm'), array('id'=>'btnAuthConfirm',
																'data-dismiss'=>'modal',
																'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
																'submit'=>Yii::app()->createUrl('t3audit/confirm'),
															)),
				TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnAuthClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY))
			);
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'authdialog',
					'header'=>Yii::t('trans','Please enter A/C staff user id and password'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
	<div class="form-group has-feedback">
		<?php echo $form->label($model,'audit_user',array('class'=>"col-sm-2 control-label")); ?>	
		<div class="col-sm-6">
			<?php echo $form->textField($model,'audit_user',
				array('placeholder'=>Yii::t('user','User ID'),'class'=>'form-control')); 
			?>			
			<span class="glyphicon glyphicon-user form-control-feedback"></span>
		</div>
	</div>
	
	<div class="form-group has-feedback">
		<?php echo $form->label($model,'audit_user_pwd',array('class'=>"col-sm-2 control-label")); ?>	
		<div class="col-sm-6">
			<?php echo $form->passwordField($model,'audit_user_pwd',
				array('placeholder'=>Yii::t('user','Password'),'class'=>'form-control')); 
			?>
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
	</div>
<?php
	$this->endWidget(); 
?>
