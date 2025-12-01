<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <?php
    Yii::app()->bootstrap->bootstrapPath = Yii::app()->basePath.'/../../AdminLTE/plugins/bootstrap';
    Yii::app()->bootstrap->adminLtePath = Yii::app()->basePath.'/../../AdminLTE';
    Yii::app()->bootstrap->register();
    ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="language" content="<?php echo Yii::app()->language; ?>" />
    <title>请选择登录的账号</title>
    <style type="text/css">
        .bs-example-modal {
            position: fixed;
            top:0px;
            left: 0px;
            right: 0px;
            bottom: 0px;
            background-color: #f5f5f5;
        }
        .bs-example-modal .modal {
            position: relative;
            top: 17%;
            right: auto;
            bottom: auto;
            left: auto;
            z-index: 1;
            display: block;
            background-color: #f5f5f5;
        }
        .bs-example-modal .modal-dialog {
            left: auto;
            margin-right: auto;
            margin-left: auto;
        }
    </style>
</head>

<body>
<div class="bs-example bs-example-modal">
    <div class="modal fade in" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" >
                <div class="modal-header">
                    <h4 class="modal-title">请选择登录的账号</h4>
                </div>
                <div class="modal-body">
                    <ul class="list-unstyled">
                        <?php
                        if(!empty($selectUser)){
                            foreach ($selectUser as $row){
                                $url = $selectUrl."&user_id=".$row["username"];
                                echo "<li><h4>".TbHtml::link($row["disp_name"]." - ".$row["city_name"],$url)."</h4></li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>
</body>
</html>
