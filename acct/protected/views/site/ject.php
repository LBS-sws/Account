<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnWFClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_DEFAULT,"class"=>"pull-left"));
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnWFSubmit','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit' => $submit));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'jectdialog',
					'header'=>Yii::t('contract','Rejected'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="form-group">
    <?php echo $form->labelEx($model,$rejectName,array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-9">
        <?php echo $form->textArea($model, $rejectName,
            array('rows'=>4,'cols'=>50,'maxlength'=>1000,'readonly'=>($model->scenario=='view'))
        ); ?>
    </div>
</div>

<?php
	$this->endWidget(); 
?>
