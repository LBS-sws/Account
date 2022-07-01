<tr>
	<th width="1%"></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('sellTable-list','b.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('sellTable-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('e.name'),'#',$this->createOrderLink('sellTable-list','e.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('dept_name').$this->drawOrderArrow('c.name'),'#',$this->createOrderLink('sellTable-list','c.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('time'),'#')
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('examine').$this->drawOrderArrow('f.examine'),'#',$this->createOrderLink('sellTable-list','f.examine'))
        ;
        ?>
    </th>
</tr>
