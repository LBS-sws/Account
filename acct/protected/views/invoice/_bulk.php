<?php
//	$typelist = General::getServiceTypeList(true);
//	$listbox = TbHtml::dropDownList('lsttypelookup', '', $typelist);
//	$label = TbHtml::label(Yii::t('qc','Service Type'),false,array('class'=>"col-sm-2 control-label"));
	$date=date('Y/m/d');
	$content = '
<div id="report-form" class="form-horizontal">
			<div class="form-group">
				<label class="control-label" style="width: 150px;padding-right: 15px;" for="InvoiceList_bulkDate">'.Yii::t("invoice","Bulk Date").'</label>
				
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<input class="form-control pull-right" name="InvoiceList[bulkDate]" id="InvoiceList_bulkDate" type="text" value="'.$date.'">					</div>
				
			</div>
			</div>';
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'bulkEditDialog',
					'header'=>Yii::t('invoice','Bulk Edit'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'),
								array(
									'id'=>'btnBulk',
									'data-dismiss'=>'modal',
									'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
								)
							),
					),
					'show'=>false,
				));
?>
<?php
$datefields = array();
 $datefields[] = 'InvoiceList_bulkDate';
if (!empty($datefields)) {
    $js = Script::genDatePicker($datefields);
    Yii::app()->clientScript->registerScript('datePickBulk',$js,CClientScript::POS_READY);
}
?>
<?php
$url = Yii::app()->createAbsoluteUrl('invoice/bulkEdit');
$js = <<<EOF
	$('#btnBulk').on('click', function() {
		jQuery.yii.submitForm(this,'$url',{});
	});
EOF;
Yii::app()->clientScript->registerScript('bulkClick',$js,CClientScript::POS_READY);
?>
