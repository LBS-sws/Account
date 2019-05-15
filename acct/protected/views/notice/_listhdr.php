<tr>
	<th>
		<?php echo TbHtml::link($this->getLabelName('note_dt').$this->drawOrderArrow('note_dt'),'#',$this->createOrderLink('notice-list','note_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('note_type').$this->drawOrderArrow('note_type'),'#',$this->createOrderLink('notice-list','note_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('subject').$this->drawOrderArrow('subject'),'#',$this->createOrderLink('notice-list','subject'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('notice-list','status'))
			;
		?>
	</th>
</tr>
