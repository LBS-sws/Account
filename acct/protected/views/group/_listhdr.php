<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('temp_name').$this->drawOrderArrow('temp_name'),'#',$this->createOrderLink('template-list','temp_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('system_name').$this->drawOrderArrow('system_name'),'#',$this->createOrderLink('template-list','system_name'))
			;
		?>
	</th>
</tr>
