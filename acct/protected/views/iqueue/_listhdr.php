<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('id'),'#',$this->createOrderLink('queue-list','id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('import_type').$this->drawOrderArrow('import_type'),'#',$this->createOrderLink('queue-list','import_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('req_dt').$this->drawOrderArrow('req_dt'),'#',$this->createOrderLink('queue-list','req_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('fin_dt').$this->drawOrderArrow('fin_dt'),'#',$this->createOrderLink('queue-list','fin_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('queue-list','status'))
			;
		?>
	</th>
</tr>
