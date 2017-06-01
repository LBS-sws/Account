<?php
	$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
			'id'=>'savedialog',
			'options'=>array(
				'show' => 'blind',
				'hide' => 'fade',
				'modal' => 'true',
				'title' => Yii::t('dialog','Save Record'),
				'autoOpen'=>false,
				'buttons'=>array(
					Yii::t('dialog','OK')=>'js:function(){$(this).dialog("close");savedata();}',
					Yii::t('dialog','Cancel')=>'js:function(){$(this).dialog("close");}',
				),
		),
	));

	printf('<span class="dialog">%s</span>', Yii::t('dialog','Are you sure?'));

	$this->endWidget('zii.widgets.jui.CJuiDialog');
?>