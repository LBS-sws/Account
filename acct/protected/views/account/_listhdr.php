<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_type_desc').$this->drawOrderArrow('acct_type_desc'),'#',$this->createOrderLink('account-list','acct_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_no').$this->drawOrderArrow('acct_no'),'#',$this->createOrderLink('account-list','acct_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_name').$this->drawOrderArrow('acct_name'),'#',$this->createOrderLink('account-list','acct_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bank_name').$this->drawOrderArrow('bank_name'),'#',$this->createOrderLink('account-list','bank_name'))
			;
		?>
	</th>
</tr>
