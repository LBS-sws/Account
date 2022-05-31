<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('set_name').$this->drawOrderArrow('set_name'),'#',$this->createOrderLink('planeSetJob-list','set_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_date').$this->drawOrderArrow('start_date'),'#',$this->createOrderLink('planeSetJob-list','start_date'))
			;
		?>
	</th>
</tr>
