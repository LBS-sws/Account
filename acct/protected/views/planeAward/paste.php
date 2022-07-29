<?php
$this->pageTitle=Yii::app()->name . ' - Transaction In Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'planeAward-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('plane','plane paste'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('planeAward/index')));
		?>
        <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
            'submit'=>Yii::app()->createUrl('planeAward/pasteSave'),'disabled'=>"disabled","id"=>"saveBtn"));
        ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<div class="form-group">
				<div class="col-lg-6 col-lg-offset-2">
                    <p>格式：员工编号 原机制应发工资<br/>例如：<br/>400001 2222<br/>400002 3333</p>
				</div>
			</div>
			<div class="form-group">
				<?php echo TbHtml::label("excel复制文本","",array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-6">
                    <?php
                    echo TbHtml::textArea("excel","",array("rows"=>8,"id"=>"excel"))
                    ?>
				</div>
			</div>

            <div class="form-group">
                <?php echo TbHtml::label('奖金年份','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php
                    echo $form->dropDownList($model, 'plane_year', PlaneAllotList::getYearList(),array('id'=>'plane_year'));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo TbHtml::label('奖金月份','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php
                    echo $form->dropDownList($model, 'plane_month', PlaneAllotList::getMonthList(),array('id'=>'plane_month'));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-6 col-lg-offset-2">
                    <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed" >
                        <thead>
                        <tr>
                            <th width="1%">
                                <input type="checkbox" name="checkbox" id="allBox"/>
                            </th>
                            <th width="33%">员工编号</th>
                            <th width="33%">员工姓名</th>
                            <th width="33%">原机制应发工资</th>
                        </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<script>
    function validateSaveBtn(){
        if($("#excel").val()!=""&&$("#citem_desc").val()!=""&&$(".checkOne:checked").length>0){
            $("#saveBtn").removeClass("disabled").prop("disabled",false);
        }else{
            $("#saveBtn").addClass("disabled").prop("disabled",true);
        }
    }

    function fun_ajax_payer(list) {
        $.post("<?php echo Yii::app()->createUrl('planeAward/ajaxPaste'); ?>",
            {list:list,year:$("#plane_year").val(),month:$("#plane_month").val()},
            function(data){
                $("#tbody").html(data["html"]);
            },
            "json");
    }

    $(function ($) {
        $("#excel,#plane_year,#plane_month").change(function () {
            var text = $("#excel").val();
            var list = text.split("\n");
            var data = [];
            var arr;
            $.each(list,function (key, row) {
                row = row.split("\t");
                if(row.length!=2){
                    //row = row.join(" ");
                    row = row[0];
                    row = row.split(" ");
                }
                if(row.length==2){
                    arr={"code":row[0],"money":row[1]};
                    data.push(arr);
                }
            });
            fun_ajax_payer(data);
            validateSaveBtn();
        });

        $("#allBox").click(function () {
            if($(this).prop("checked")){
                $(".checkOne").prop("checked",true);
            }else{
                $(".checkOne").prop("checked",false);
            }
            validateSaveBtn();
        });
        $("#tbody").delegate(".checkOne","change",function () {
            validateSaveBtn();
        });
    })
</script>

<?php

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


