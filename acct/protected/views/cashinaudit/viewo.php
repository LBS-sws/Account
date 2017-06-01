<?php $labels = $model->attributeLabels(); ?>
<tr>
	<td width=30%><?php echo TbHtml::label($labels['trans_type_code'],false); ?></td>
	<td><?php 
			$list = General::getTransTypeList('OUT',true);
//			echo TbHtml::textField('trans_type_code', $list[$model->trans_type_code], array('readonly'=>true));
			echo $list[$model->trans_type_code];
		?>
	</td>
</tr>

<tr>
	<td><?php echo TbHtml::label($labels['acct_id'],false); ?></td>
	<td>
		<?php 
			$list = General::getAccountList();
//			echo TbHtml::textField('acct_id', $list[$model->acct_id], array('readonly'=>true));
			echo $list[$model->acct_id];
		?>
	</td>
</tr>


<tr>
	<td><?php echo TbHtml::label($labels['payer_name'],false); ?></td>
	<td>
		<?php 
			$list = array(
						''=>'',
						'C'=>Yii::t('trans','Client'),
						'S'=>Yii::t('trans','Supplier'),
						'A'=>Yii::t('trans','Company A/C'),
						'F'=>Yii::t('trans','Staff'),
						'O'=>Yii::t('trans','Others')
					);
//			echo TbHtml::textField('payer_name', '('.$list[$model->payer_type].')'.$model->payer_name, array('readonly'=>true));
			echo ($model->payer_type=='C'&&$model->payer_name=='') ? '' : '('.$list[$model->payer_type].')'.$model->payer_name;
		?>
	</td>
</tr>


<tr>
	<td><?php echo TbHtml::label($labels['cheque_no'],false); ?></td>
	<td>
		<?php echo $model->cheque_no;?>
	</td>
</tr>

<tr>
	<td><?php echo TbHtml::label($labels['invoice_no'],false); ?></td>
	<td>
		<?php echo $model->invoice_no;?>
	</td>
</tr>

<tr>
	<td><?php echo TbHtml::label($labels['amount'],false); ?></td>
	<td>
		<?php echo $model->amount;?>
	</td>
</tr>

<tr>
	<td><?php echo TbHtml::label($labels['trans_desc'],false); ?></td>
	<td>
		<?php 
			echo $model->trans_desc; 
//			echo TbHtml::textArea('trans_desc', $model->trans_desc, 
//				array('rows'=>2,'cols'=>50,'maxlength'=>1000,'readonly'=>true)
//			); 
		?>
	</td>
</tr>
