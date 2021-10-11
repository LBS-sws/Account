<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('type_id'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('operator'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('month_num'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('rate'), false); ?>
	</th>

	<th>
		<?php echo Yii::app()->user->validRWFunction('XS03') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
