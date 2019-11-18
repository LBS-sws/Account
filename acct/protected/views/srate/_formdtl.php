<tr>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('name'),  $this->record['name'], array('fw'=>'服务(IA/IB/IC/飘盈香/甲醛)','inv'=>'INV','zyj'=>'皂液机','zy'=>'皂液等其他化学液体','zp'=>'纸品','xdy'=>'洗地易 ','ngd'=>'尿缸垫 '),
            array('disabled'=>$this->model->isReadOnly())
        ); ?>
    </td>
	<td>
		<?php echo TbHtml::dropDownList($this->getFieldName('operator'),  $this->record['operator'], array('LE'=>'<=','GT'=>'>'),
								array('disabled'=>$this->model->isReadOnly())
		); ?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('sales_amount'), $this->record['sales_amount'],  
							array('size'=>10,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
	<td>
		<?php  
			echo TbHtml::numberField($this->getFieldName('rate'), $this->record['rate'],
							array('size'=>5,'min'=>0,
							'readonly'=>($this->model->isReadOnly()),
							)
						);
		?>
	</td>
<!--	<td>-->
<!--		--><?php //
//			echo TbHtml::numberField($this->getFieldName('inv_rate'), $this->record['inv_rate'],
//							array('size'=>5,'min'=>0,
//							'readonly'=>($this->model->isReadOnly()),
//							)
//						);
//		?>
<!--	</td>-->
	<td>
		<?php 
			echo !$this->model->isReadOnly() 
				? TbHtml::Button('-',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'size'=>TbHtml::BUTTON_SIZE_SMALL))
				: '&nbsp;';
		?>
		<?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
		<?php echo CHtml::hiddenField($this->getFieldName('hdr_id'),$this->record['hdr_id']); ?>
	</td>
</tr>
