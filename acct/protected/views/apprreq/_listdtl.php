<?php
	$cls_str = "class='clickable-row' data-href='"
		.$this->getLink('XA05', 'apprreq/edit', 'apprreq/edit', array('index'=>$this->record['id'],'type'=>$this->record['type']))
		."'";
?>
<tr>
<?php if (Yii::app()->user->validFunction('CN07')) : ?>
	<td><?php echo TbHtml::checkBox($this->getFieldName('select'),($this->record['select']!='N'),array('value'=>'Y')); ?></td>
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
