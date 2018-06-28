<tr>
	<th>
		<?php echo TbHtml::label($this->getLabelName('delegated'), false); ?>
	</th>
	<th>
		<?php echo Yii::app()->user->validRWFunction('XC07') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
