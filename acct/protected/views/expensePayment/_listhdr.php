<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('exp_code').$this->drawOrderArrow('a.exp_code'),'#',$this->createOrderLink('expensePayment-list','a.exp_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('g.name'),'#',$this->createOrderLink('expensePayment-list','g.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('apply_date').$this->drawOrderArrow('a.apply_date'),'#',$this->createOrderLink('expensePayment-list','a.apply_date'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('employee').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('expensePayment-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('department').$this->drawOrderArrow('f.name'),'#',$this->createOrderLink('expensePayment-list','f.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('amt_money').$this->drawOrderArrow('a.amt_money'),'#',$this->createOrderLink('expensePayment-list','a.amt_money'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('a.status_type'),'#',$this->createOrderLink('expensePayment-list','a.status_type'))
			;
		?>
	</th>
</tr>
