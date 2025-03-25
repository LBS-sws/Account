<tr>
	<th width="1%"></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('a.code'),'#',$this->createOrderLink('planeAudit-list','a.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('planeAudit-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('planeAudit-list','a.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plane_date').$this->drawOrderArrow('f.plane_date'),'#',$this->createOrderLink('planeAudit-list','f.plane_date'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plane_sum').$this->drawOrderArrow('f.plane_sum'),'#',$this->createOrderLink('planeAudit-list','f.plane_sum'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('old_pay_wage').$this->drawOrderArrow('f.old_pay_wage'),'#',$this->createOrderLink('planeAudit-list','f.old_pay_wage'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('difference').$this->drawOrderArrow('difference'),'#',$this->createOrderLink('planeAudit-list','difference'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plane_status').$this->drawOrderArrow('plane_status'),'#',$this->createOrderLink('planeAudit-list','plane_status'))
        ;
        ?>
    </th>
</tr>
