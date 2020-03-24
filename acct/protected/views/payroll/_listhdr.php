<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('payroll-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year_no').$this->drawOrderArrow('year_no'),'#',$this->createOrderLink('payroll-list','year_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month_no').$this->drawOrderArrow('month_no'),'#',$this->createOrderLink('payroll-list','month_no'))
			;
		?>
	</th>
<!--
	<th>
		<?php echo TbHtml::link($this->getLabelName('file1countdoc').$this->drawOrderArrow('file1countdoc'),'#',$this->createOrderLink('payroll-list','file1countdoc'))
			;
		?>
	</th>
-->
	<th>
		<?php echo TbHtml::link($this->getLabelName('wfstatusdesc').$this->drawOrderArrow('wfstatusdesc'),'#',$this->createOrderLink('payroll-list','status'))
			;
		?>
	</th>
</tr>
