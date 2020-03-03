<tr>
	<th></th>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('city_name'),'#',$this->createOrderLink('acctfile-list','city_name'))
			;
		?>
	</th>
<?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year_no').$this->drawOrderArrow('year_no'),'#',$this->createOrderLink('acctfile-list','year_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month_no').$this->drawOrderArrow('month_no'),'#',$this->createOrderLink('acctfile-list','month_no'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('file1countdoc').$this->drawOrderArrow('file1countdoc'),'#',$this->createOrderLink('acctfile-list','file1countdoc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('file2countdoc').$this->drawOrderArrow('file2countdoc'),'#',$this->createOrderLink('acctfile-list','file2countdoc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('file3countdoc').$this->drawOrderArrow('file3countdoc'),'#',$this->createOrderLink('acctfile-list','file3countdoc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('file4countdoc').$this->drawOrderArrow('file4countdoc'),'#',$this->createOrderLink('acctfile-list','file4countdoc'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('acctfile-list','status'))
			;
		?>
	</th>
</tr>
