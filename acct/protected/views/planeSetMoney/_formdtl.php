<tr class="tr_show">
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('value_name'),  $this->record['value_name'],
            array('disabled'=>$this->model->isReadOnly(),'prepend'=>'>=','class'=>'value_name')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('value_money'),  $this->record['value_money'],
            array('disabled'=>$this->model->isReadOnly(),'class'=>'value_money')
        ); ?>
    </td>
	<td>
		<?php 
			echo !$this->model->isReadOnly()
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('money_id'),$this->record['money_id']); ?>
	</td>
</tr>
