<tr>

	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_code').$this->drawOrderArrow('employee_code'),'#',$this->createOrderLink('commission-list','employee_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('employee_name'),'#',$this->createOrderLink('commission-list','employee_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('commission-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('user_name').$this->drawOrderArrow('user_name'),'#',$this->createOrderLink('commission-list','user_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('日期'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('comm_total_amount').$this->drawOrderArrow('comm_total_amount'),'#',$this->createOrderLink('commission-list','comm_total_amount'))
			;
		?>
	</th>


</tr>
