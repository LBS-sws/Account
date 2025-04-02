<tr class="tr_show">
    <td>
        <?php echo TbHtml::textArea($this->getFieldName('moneyTxt'),  $this->record['moneyTxt'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'moneyTxt')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('moneyAmt'),  $this->record['moneyAmt'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'moneyAmt nullInput')
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
