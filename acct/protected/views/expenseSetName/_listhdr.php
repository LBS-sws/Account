<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('name'),'#',$this->createOrderLink('expenseSetName-list','name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('return_value').$this->drawOrderArrow('return_value'),'#',$this->createOrderLink('expenseSetName-list','return_value'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('expenseSetName-list','z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('display').$this->drawOrderArrow('display'),'#',$this->createOrderLink('expenseSetName-list','display'))
			;
		?>
	</th>
</tr>
