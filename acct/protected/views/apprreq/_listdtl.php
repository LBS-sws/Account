<?php
	$cls_str = "class='clickable-row' data-href='"
		.$this->getLink('XA05', 'apprreq/edit', 'apprreq/edit', array('index'=>$this->record['id'],'type'=>$this->model->type))
		."'";
?>
<tr>
<?php if (Yii::app()->user->validFunction('CN07')) : ?>
	<td><?php echo TbHtml::checkBox($this->getFieldName('select'),(isset($this->record['select']) && $this->record['select']=='Y'),array('value'=>'Y')); ?></td>
<?php endif ?>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['city_name']; ?>
	</td>
<?php endif ?>
	<td <?php echo $cls_str;?>>
		<?php
			echo TbHtml::hiddenField($this->getFieldName('id'), $this->record['id']);
			echo TbHtml::hiddenField($this->getFieldName('req_dt'), $this->record['req_dt']);
			echo TbHtml::hiddenField($this->getFieldName('ref_no'), $this->record['ref_no']);
			echo TbHtml::hiddenField($this->getFieldName('user_name'), $this->record['user_name']);
			echo TbHtml::hiddenField($this->getFieldName('trans_type_desc'), $this->record['trans_type_desc']);
			echo TbHtml::hiddenField($this->getFieldName('city_name'), $this->record['city_name']);
			echo TbHtml::hiddenField($this->getFieldName('status'), $this->record['status']);
			echo TbHtml::hiddenField($this->getFieldName('type'), $this->record['type']);
			echo TbHtml::hiddenField($this->getFieldName('int_fee'), $this->record['int_fee']);
			echo TbHtml::hiddenField($this->getFieldName('amount'), $this->record['amount']);
			echo TbHtml::hiddenField($this->getFieldName('payee_name'), $this->record['payee_name']);
			echo TbHtml::hiddenField($this->getFieldName('item_desc'), $this->record['item_desc']);
			echo TbHtml::hiddenField($this->getFieldName('pitem_desc'), $this->record['pitem_desc']);
			echo TbHtml::hiddenField($this->getFieldName('payreqcountdoc'), $this->record['payreqcountdoc']);
			echo TbHtml::hiddenField($this->getFieldName('taxcountdoc'), $this->record['taxcountdoc']);
			echo TbHtml::hiddenField($this->getFieldName('acct_type_desc'), $this->record['acct_type_desc']);
		?>
		<?php echo $this->record['req_dt']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['user_name']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['trans_type_desc']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['acct_type_desc']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['payee_name']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['pitem_desc']; ?>
	</td>
	<td width='20%' <?php echo $cls_str;?>>
		<?php echo $this->record['item_desc']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['amount']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['ref_no']; ?>
	</td>
	<td <?php echo $cls_str;?>>
		<?php echo $this->record['int_fee']; ?>
	</td>
	<td>
		<?php 
			echo TbHtml::button($this->record['payreqcountdoc'], 
				array(
					'class'=>'btn-xs',
					'onclick'=>'javascript:showattm('.$this->record['id'].');',
				)
			);
		?>
	</td>
	<td>
		<?php 
			echo TbHtml::button($this->record['taxcountdoc'], 
				array(
					'class'=>'btn-xs',
					'onclick'=>'javascript:showtax('.$this->record['id'].');',
				)
			);
		?>
	</td>
</tr>
