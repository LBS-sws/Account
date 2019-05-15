<tr>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('adjtype'),  $this->record['adjtype'], 
								array(''=>Yii::t('misc','-- None --'),'T1'=>Yii::t('trans','T3 Rec. LBS Not'),'T2'=>Yii::t('trans','T3 Paid LBS Not')),
								array('disabled'=>!Yii::app()->user->validRWFunction('XE06'))
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('remarks'), $this->record['remarks'], 
			array('readonly'=>!Yii::app()->user->validRWFunction('XE06'), 
				'size'=>'100', 'maxlength'=>'500',)
		); ?>
	</td>
	<td>
		<?php echo TbHtml::textField($this->getFieldName('amount'), $this->record['amount'], 
			array('readonly'=>!Yii::app()->user->validRWFunction('XE06'), 
				'size'=>'10', 'maxlength'=>'10',)
		); ?>
	</td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('XE06') 
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
	</td>
</tr>
