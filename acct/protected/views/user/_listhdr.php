<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('username').$this->drawOrderArrow('username'),'#',$this->createOrderLink('user-list','username'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('disp_name').$this->drawOrderArrow('disp_name'),'#',$this->createOrderLink('user-list','disp_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('user-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('user-list','status'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('logon_time').$this->drawOrderArrow('logon_time'),'#',$this->createOrderLink('user-list','logon_time'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('logoff_time').$this->drawOrderArrow('logoff_time'),'#',$this->createOrderLink('user-list','logoff_time'))
			;
		?>
	</th>
</tr>
