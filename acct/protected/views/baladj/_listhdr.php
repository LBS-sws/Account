<tr>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('request-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_year').$this->drawOrderArrow('audit_year'),'#',$this->createOrderLink('request-list','audit_year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_month').$this->drawOrderArrow('audit_month'),'#',$this->createOrderLink('request-list','audit_month'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('acct_name').$this->drawOrderArrow('acct_name'),'#',$this->createOrderLink('request-list','acct_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bal_month_end').$this->drawOrderArrow('bal_month_end'),'#',$this->createOrderLink('request-list','bal_month_end'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('bal_t3').$this->drawOrderArrow('bal_t3'),'#',$this->createOrderLink('request-list','bal_t3'))
			;
		?>
	</th>
</tr>
