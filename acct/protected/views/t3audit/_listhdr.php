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
		<?php echo TbHtml::link($this->getLabelName('req_user_name').$this->drawOrderArrow('req_user_name'),'#',$this->createOrderLink('request-list','req_user_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('req_dt').$this->drawOrderArrow('req_dt'),'#',$this->createOrderLink('request-list','req_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_user_name').$this->drawOrderArrow('audit_user_name'),'#',$this->createOrderLink('request-list','audit_user_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_dt').$this->drawOrderArrow('audit_dt'),'#',$this->createOrderLink('request-list','audit_dt'))
			;
		?>
	</th>
</tr>
