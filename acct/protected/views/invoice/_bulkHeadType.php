<?php
//	$typelist = General::getServiceTypeList(true);
//	$listbox = TbHtml::dropDownList('lsttypelookup', '', $typelist);
//	$label = TbHtml::label(Yii::t('qc','Service Type'),false,array('class'=>"col-sm-2 control-label"));
	$date=date('Y/m/d');
	$content = '
<div id="report-form" class="form-horizontal">
			<div class="form-group">
				<label class="control-label" style="width: 150px;padding-right: 15px;" for="InvoiceList_bulkHeadType">'.Yii::t("invoice","Bulk Date").'</label>
				
					<div class="input-group">
						<select class="form-control pull-right" name="InvoiceList[bulkHeadType]" id="InvoiceList_bulkHeadType">
							<option value="0">佳駿企業有限公司</option>
							<option value="1">史伟莎（澳门）一人有限公司/LBS (Macau) Limited</option>
						</select>
					</div>
			</div>
			</div>';
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'bulkHeadTypeDialog',
					'header'=>Yii::t('invoice','Bulk Head Type'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'),
								array(
									'id'=>'btnBulkHeadType',
									'data-dismiss'=>'modal',
									'color'=>TbHtml::BUTTON_COLOR_PRIMARY,
								)
							),
					),
					'show'=>false,
				));
?>
<?php
$url = Yii::app()->createAbsoluteUrl('invoice/bulkHeadType');
$js = <<<EOF
	$('#btnBulkHeadType').on('click', function() {
		jQuery.yii.submitForm(this,'$url',{});
	});
EOF;
Yii::app()->clientScript->registerScript('bulkHeadTypeClick',$js,CClientScript::POS_READY);
?>
