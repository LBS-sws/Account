<?php
	$content = "<p>".Yii::t('dialog','Are you sure to void?')."</p>";
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'voiddialog',
					'header'=>Yii::t('dialog','Void Record'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnVoidData','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>