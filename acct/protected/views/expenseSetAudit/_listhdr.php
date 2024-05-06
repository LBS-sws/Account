<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('expenseSetAudit-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('f.name'),'#',$this->createOrderLink('expenseSetAudit-list','f.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_user_str').$this->drawOrderArrow('a.audit_user_str'),'#',$this->createOrderLink('expenseSetAudit-list','a.audit_user_str'))
			;
		?>
	</th>
</tr>
