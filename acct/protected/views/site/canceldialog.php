<?php
	$content = "<p>".Yii::t('dialog','Are you sure to cancel?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'canceldialog',
					'header'=>Yii::t('dialog','Cancel Record'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnCancelData','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>