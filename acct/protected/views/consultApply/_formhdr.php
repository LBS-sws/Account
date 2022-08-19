<tr>
    <th width="70%">
        <?php echo TbHtml::label($this->getLabelName('set_id'), false); ?>
    </th>
	<th width="30%">
		<?php echo TbHtml::label($this->getLabelName('good_money'), false); ?>
	</th>

	<th>
		<?php echo !$this->model->isReady() ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
