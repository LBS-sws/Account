<?php
	$content = "<p>".Yii::t('dialog','Mark read for all selected records?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'markreaddialog',
					'header'=>Yii::t('misc','Mark Read'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnMarkRead','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>