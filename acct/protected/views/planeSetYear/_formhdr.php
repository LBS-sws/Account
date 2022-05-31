<tr>
    <th>
        <?php echo TbHtml::label($this->getLabelName('value_name'), false); ?>
    </th>
	<th>
		<?php echo TbHtml::label($this->getLabelName('value_money'), false); ?>
	</th>

	<th>
		<?php echo Yii::app()->user->validRWFunction('PS05') ?
				TbHtml::Button('+',array('id'=>'btnAddRow','title'=>Yii::t('misc','Add'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
	</th>
</tr>
