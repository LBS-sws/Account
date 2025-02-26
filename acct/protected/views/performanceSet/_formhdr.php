<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('name'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('operator'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('new_amount'), false); ?>
	</th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('bonus_amount'), false); ?>
	</th>

	<th>
		<?php echo Yii::app()->user->validRWFunction('XS13') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
