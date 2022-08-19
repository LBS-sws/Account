<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('good_name').$this->drawOrderArrow('good_name'),'#',$this->createOrderLink('consultSet-list','good_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_index').$this->drawOrderArrow('z_index'),'#',$this->createOrderLink('consultSet-list','z_index'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('z_display').$this->drawOrderArrow('z_display'),'#',$this->createOrderLink('consultSet-list','z_display'))
			;
		?>
	</th>
</tr>
