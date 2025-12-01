<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('salesGroupSet-list','b.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('salesGroupSet-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_date').$this->drawOrderArrow('a.start_date'),'#',$this->createOrderLink('salesGroupSet-list','a.start_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('end_date').$this->drawOrderArrow('a.end_date'),'#',$this->createOrderLink('salesGroupSet-list','a.end_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('group_staff_name').$this->drawOrderArrow('a.group_staff_name'),'#',$this->createOrderLink('salesGroupSet-list','a.group_staff_name'))
			;
		?>
	</th>
</tr>
