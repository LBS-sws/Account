<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('code-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('start_dt').$this->drawOrderArrow('start_dt'),'#',$this->createOrderLink('code-list','start_dt'))
			;
		?>
	</th>
</tr>
