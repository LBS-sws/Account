<tr>
	<th>  <input name="Fruit"  type="checkbox"  id="all"></th>

	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_no').$this->drawOrderArrow('invoice_no'),'#',$this->createOrderLink('Invoice-list','invoice_no'))
			;
		?>
	</th>

	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_dt').$this->drawOrderArrow('invoice_dt'),'#',$this->createOrderLink('Invoice-list','invoice_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('customer_code').$this->drawOrderArrow('customer_code'),'#',$this->createOrderLink('Invoice-list','customer_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_to_name').$this->drawOrderArrow('invoice_to_name'),'#',$this->createOrderLink('Invoice-list','invoice_to_name'))
			;
		?>
	</th>

</tr>
