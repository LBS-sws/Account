<?php
	$content = "<p>".Yii::t('trans','Are you sure to accept?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'confirmdialog',
					'header'=>Yii::t('trans','Accept Record'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnAcceptData','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>