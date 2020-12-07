<tr>
    <td>
        <?php echo TbHtml::textField($this->getFieldName('date'), $this->record['date'],
            array('readonly'=>!Yii::app()->user->validRWFunction('E01'),
                'size'=>'5', 'maxlength'=>'10','class'=>'date',
                'prepend'=>'<i class="fa fa-calendar"></i>',
            )); ?>
    </td>
    <td>
        <?php echo TbHtml::textField($this->getFieldName('customer'), $this->record['customer'],
            array('readonly'=>!Yii::app()->user->validRWFunction('E01'),
                'size'=>'5', 'maxlength'=>'20',)
        ); ?>
    </td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('type'),  $this->record['type'], array('ia'=>'IA','ib'=>'IB','ic'=>'IC'),
								array('disabled'=>!Yii::app()->user->validRWFunction('E01'))
		); ?>
	</td>

    <td>
        <?php echo TbHtml::textField($this->getFieldName('information'), $this->record['information'],
            array('readonly'=>!Yii::app()->user->validRWFunction('E01'),
                'size'=>'5', 'maxlength'=>'1000',)
        ); ?>
    </td>

    <td>
        <?php echo TbHtml::textField($this->getFieldName('commission'), $this->record['commission'],
            array('readonly'=>!Yii::app()->user->validRWFunction('E01'),
                'size'=>'5', 'maxlength'=>'10',)
        ); ?>
    </td>
	<td>
		<?php 
			echo Yii::app()->user->validRWFunction('E01')
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
        <?php echo CHtml::hiddenField($this->getFieldName('hdr_id'),$this->record['hdr_id']); ?>
	</td>
</tr>
