<tr class="<?php echo $this->record['style'];?>">
	<td>
        <?php
        if(empty($this->record['plane_id'])){
            echo TbHtml::checkBox("allot[{$this->record['id']}]",false,array("class"=>"allot_check","data-id"=>$this->record['id']));
        }
        ?>
    </td>
	<td><?php echo $this->record['code']; ?></td>
	<td class="name"><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['entry_time']; ?></td>
	<td><?php echo $this->record['department']; ?></td>
	<td><?php echo $this->record['position']; ?></td>
	<td><?php echo $this->record['staff_leader']; ?></td>
	<td><?php echo $this->record['plane']; ?></td>
    <td>
        <?php
        if(empty($this->record['plane_id'])){
            echo TbHtml::button(Yii::t("plane","Allot"),array("class"=>"allot_btn","data-id"=>$this->record['id']));
        }
        ?>
    </td>
</tr>
