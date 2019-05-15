<tr>
	<th width='20%'>
		<?php echo TbHtml::label($this->getLabelName('adjtype'), false); ?>
	</th>
	<th width='60%'>
		<?php echo TbHtml::label($this->getLabelName('remarks'), false); ?>
	</th>
	<th width='15%'>
		<?php echo TbHtml::label($this->getLabelName('amount'), false); ?>
	</th>
	<th width='5%'>
		<?php echo Yii::app()->user->validRWFunction('XE06') ?
				TbHtml::Button('+',array('id'=>'btnAddRowL','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
