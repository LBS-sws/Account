<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnWFClose','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'flowinfodialog',
					'header'=>Yii::t('dialog','Flow Info'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>

<div class="box" id="flow-list" style="max-height: 300px; overflow-y: auto;">
	<table id="tblFlow" class="table table-hover">
		<thead>
			<tr>
				<th><?php echo Yii::t('dialog','Date');?></th>
				<th><?php echo Yii::t('dialog','Flow');?></th>
				<th><?php echo Yii::t('dialog','Resp. User');?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$wf = new WorkflowPayment;
	$connection = $wf->openConnection();
	$idfield = $model->wfIdField;
	$dtfield = $model->wfDateField;
	$wf->startProcess($model->wfCode, $model->$idfield, $model->$dtfield);
	echo $wf->genTableStateList();
?>
		</tbody>
	</table>
</div>

<?php
	$this->endWidget(); 
?>
