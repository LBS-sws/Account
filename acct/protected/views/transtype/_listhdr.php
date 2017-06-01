<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_code').$this->drawOrderArrow('trans_type_code'),'#',$this->createOrderLink('code-list','trans_type_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_type_desc').$this->drawOrderArrow('trans_type_desc'),'#',$this->createOrderLink('code-list','trans_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('trans_cat').$this->drawOrderArrow('trans_cat'),'#',$this->createOrderLink('code-list','trans_cat'))
			;
		?>
	</th>
</tr>
