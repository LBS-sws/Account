<tr>
	<th width="1%">
        <?php
        echo TbHtml::checkBox("allot_all",false,array("id"=>"allot_all"));
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('a.code'),'#',$this->createOrderLink('planeAllot-list','a.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('planeAllot-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('entry_time').$this->drawOrderArrow('a.entry_time'),'#',$this->createOrderLink('planeAllot-list','a.entry_time'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('department').$this->drawOrderArrow('b.department'),'#',$this->createOrderLink('planeAllot-list','b.department'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('position').$this->drawOrderArrow('d.position'),'#',$this->createOrderLink('planeAllot-list','d.position'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('staff_leader').$this->drawOrderArrow('a.staff_leader'),'#',$this->createOrderLink('planeAllot-list','a.staff_leader'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('plane').$this->drawOrderArrow('f.job_id'),'#',$this->createOrderLink('planeAllot-list','f.job_id'))
			;
		?>
	</th>
    <th width="1%"></th>
</tr>
