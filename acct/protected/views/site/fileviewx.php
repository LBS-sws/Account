<?php
	$doc = new DocMan($doctype,0,get_class($model));
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>$doc->closeButtonName,'data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>$doc->widgetName,
					'header'=>$header,
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
<div class="box" id="file-list" style="max-height: 300px; overflow-y: auto;">
	<table id="<?php echo $doc->tableName; ?>" class="table table-hover">
		<thead>
			<tr><th></th><th><?php echo Yii::t('dialog','File Name');?></th><th><?php echo Yii::t('dialog','Date');?></th></tr>
		</thead>
		<tbody id="fileview<?php echo strtolower($doctype); ?>">
		</tbody>
	</table>
</div>
<?php
	$this->endWidget(); 
?>
