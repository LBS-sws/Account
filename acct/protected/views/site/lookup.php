<?php
	$hidden1 = TbHtml::hiddenField('lookuptype', '');
	$hidden2 = TbHtml::hiddenField('lookupcodefield', '');
	$hidden3 = TbHtml::hiddenField('lookupvaluefield', '');
	$hidden4 = TbHtml::hiddenField('lookupotherfield', '');
	
	$search = TbHtml::textField('txtlookup', '', array('class'=>'form-control','maxlength'=>500,
				'append'=>TbHtml::button(Yii::t('misc','Search'),array('name'=>'btnLookup','id'=>'btnLookup')),
			)); 
	$list = TbHtml::listBox('lstlookup', '', array(), array(
				'size'=>'15')
			);

	$mesg = TbHtml::label(Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options'), false);
			
	$content = "
<div class=\"row\">
	$hidden1
	$hidden2
	$hidden3
	$hidden4
	<div class=\"col-sm-11\">
			$search
	</div>
</div>
<div class=\"row\">
	<div class=\"col-sm-11\" id=\"lookup-list\">
			$list
	</div>
</div>
<div class=\"row\">
	<div class=\"col-sm-11 small\" id=\"lookup-label\">
			$mesg
	</div>
</div>
<div id='fieldvalue' style='display: none'></div>
	";

	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'lookupdialog',
					'header'=>Yii::t('dialog','Lookup'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','Select'), array('id'=>'btnLookupSelect','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Close'), array('id'=>'btnLookupCancel','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>


