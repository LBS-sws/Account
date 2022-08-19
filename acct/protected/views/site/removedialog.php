<?php
	$content = isset($content)?$content:"<p>".Yii::t('dialog','Are you sure to delete?')."</p>";
	$header = isset($header)?$header:Yii::t('dialog','Delete Record');
	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'removedialog',
					'header'=>$header,
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnDeleteData','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>