<tr>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('delegated'),  $this->record['delegated'], $this->model->getUserList(),
								array('disabled'=>!Yii::app()->user->validRWFunction('XC07'))
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('XC07') 
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
	</td>
</tr>
