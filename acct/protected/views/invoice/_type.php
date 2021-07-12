<?php
//	$typelist = General::getServiceTypeList(true);
//	$listbox = TbHtml::dropDownList('lsttypelookup', '', $typelist);
//	$label = TbHtml::label(Yii::t('qc','Service Type'),false,array('class'=>"col-sm-2 control-label"));
	$date=date('Y/m/d');
	$content = '
<form id="report-form" class="form-horizontal" action="" method="post">
			<div class="form-group" style="height: 34px;line-height: 34px">
				<label class="col-sm-2 control-label" for="Report01Form_start_dt">start date</label>				<div class="col-sm-3" style="width: 240px;">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<input class="form-control pull-right" name="Report01Form[start_dt]" id="Report01Form_start_dt" type="text" value="'.$date.'">					</div>
				</div>
			</div>
			<div class="form-group" style="height: 34px;line-height: 34px">
				<label class="col-sm-2 control-label" for="Report01Form_start_dt">end date</label>				<div class="col-sm-3" style="width: 240px;">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<input class="form-control pull-right" name="Report01Form[end_dt]" id="Report01Form_end_dt" type="text" value="'.$date.'">					</div>
				</div>
			
			</div>
			</form>';
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'addrecdialog',
					'header'=>Yii::t('misc','Add Record'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'),
								array(
									'id'=>'btnOk',
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
 $datefields[] = 'Report01Form_start_dt';
 $datefields[] = 'Report01Form_end_dt';
if (!empty($datefields)) {
    $js = Script::genDatePicker($datefields);
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
?>
<?php
$url = Yii::app()->createAbsoluteUrl('invoice/add');
$js = <<<EOF
	$('#btnOk').on('click', function() {
		var start = $('#Report01Form_start_dt').val();
		var end = $('#Report01Form_end_dt').val();
		window.location.href = '$url?start='+start+'&&end='+end;
	});
EOF;
Yii::app()->clientScript->registerScript('okClick',$js,CClientScript::POS_READY);
?>
