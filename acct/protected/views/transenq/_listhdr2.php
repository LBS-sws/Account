<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_dt').$this->drawOrderArrow('trans_dt'),'#',$this->createOrderLink('enquiry-list','trans_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_desc').$this->drawOrderArrow('trans_type_desc'),'#',$this->createOrderLink('enquiry-list','trans_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('pay_subject').$this->drawOrderArrow('pay_subject'),'#',$this->createOrderLink('enquiry-list','pay_subject'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amount_in').$this->drawOrderArrow('amount_in'),'#',$this->createOrderLink('enquiry-list','amount_in'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amount_out').$this->drawOrderArrow('amount_out'),'#',$this->createOrderLink('enquiry-list','amount_out'))
			;
		?>
	</th>
</tr>
