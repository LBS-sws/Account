<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_code').$this->drawOrderArrow('employee_code'),'#',$this->createOrderLink('salestable-list','employee_code'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('employee_name'),'#',$this->createOrderLink('salestable-list','employee_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('salestable-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('time').$this->drawOrderArrow('time'),'#',$this->createOrderLink('salestable-list','time'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('user_name').$this->drawOrderArrow('user_name'),'#',$this->createOrderLink('salestable-list','user_name'))
			;
		?>
	</th>
</tr>
