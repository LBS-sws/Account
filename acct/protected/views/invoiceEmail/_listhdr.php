<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_dt').$this->drawOrderArrow('a.start_dt'),'#',$this->createOrderLink('invoiceEmail-list','a.start_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('email_text').$this->drawOrderArrow('a.email_text'),'#',$this->createOrderLink('invoiceEmail-list','a.email_text'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('remarks').$this->drawOrderArrow('a.remarks'),'#',$this->createOrderLink('invoiceEmail-list','a.remarks'))
			;
		?>
	</th>
</tr>
