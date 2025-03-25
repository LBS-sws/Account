<tr class="tr_show">
    <td>
        <?php echo TbHtml::textArea($this->getFieldName('takeTxt'),  $this->record['takeTxt'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'takeTxt')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('takeAmt'),  $this->record['takeAmt'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'takeAmt nullInput')
        ); ?>
    </td>
	<td>
		<?php 
			echo !$this->model->isReadOnly()
				? TbHtml::Button('-',array('class'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('planeId'),$this->record['planeId']); ?>
	</td>
</tr>
