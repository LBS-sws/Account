<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_type_desc').$this->drawOrderArrow('acct_type_desc'),'#',$this->createOrderLink('code-list','acct_type_desc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('rpt_cat').$this->drawOrderArrow('rpt_cat'),'#',$this->createOrderLink('code-list','rpt_cat'))
			;
		?>
	</th>
</tr>
