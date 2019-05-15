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
		<?php echo !$this->model->isReadOnly() ?
				TbHtml::Button('+',array('id'=>'btnAddRowT','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
