<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('id').$this->drawOrderArrow('id'),'#',$this->createOrderLink('queue-list','id'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rpt_desc').$this->drawOrderArrow('rpt_desc'),'#',$this->createOrderLink('queue-list','rpt_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rpt_type').$this->drawOrderArrow('rpt_type'),'#',$this->createOrderLink('queue-list','rpt_type'))
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
