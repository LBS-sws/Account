<?php
return array(
    'ops.YA03' => array(
        'validation'=>'isSalesSummaryApproved',
        'system'=>'ops',
        'function'=>'YA03',
        'message'=>Yii::t('block','Please complete Operation System - Sales Summary Report Approval before using other functions.'),
    ),
    'ops.YA01' => array(
        'validation'=>'isSalesSummarySubmitted',
        'system'=>'ops',
        'function'=>'YA01',
        'message'=>Yii::t('block','Please complete Operation System - Sales Summary Report Submission before using other functions.'),
    ),
    'sp.GA01' => array(
        'validation'=>'isCreditApproved',
        'system'=>'sp',
        'function'=>'GA01',
        'message'=>Yii::t('block','Please complete Academic Credit System - Credit Request Approval before using other functions.'),
    ),
    'sp.GA04' => array(
        'validation'=>'isCreditConfirmed',
        'system'=>'sp',
        'function'=>'GA04',
        'message'=>Yii::t('block','Please complete Academic Credit System - Credit Request Confirmation before using other functions.'),
    ),
    'hr.RE02' => array(
        'validation'=>'validateReviewLongTime',
        'system'=>'hr',
        'function'=>'RE02',
        'message'=>Yii::t('block','Please complete Personnel System - Appraisial before using other functions.'),
    ),

    'quiz.EM02' => array( //新用戶三個月後限制用戶行為（函數內判斷了地區是否適用）
        'validation'=>'validateNewStaff',
        'system'=>'quiz',
        'function'=>array('EM02','EM01','SC04'),
        'message'=>Yii::t('block','validateNewStaff'),
    ),
    'quiz.EM02.Year' => array( //技術員每年需要測試一次（函數內判斷了地區是否適用）
        'validation'=>'EveryYearForExamination',
        'system'=>'quiz',
        'function'=>array('EM02','EM01','SC04'),
        'message'=>Yii::t('block','EveryYearForExamination'),
    ),
    'quiz.EM03' => array( //QC達標限制（函數內判斷了地區是否適用）
        'validation'=>'validateExamination',
        'system'=>'quiz',
        'function'=>array('EM02','EM01','SC04'),
        'message'=>Yii::t('block','validateExamination'),
    ),
    'quiz.EM03.hint' => array( //QC達標提醒（函數內判斷了地區是否適用）
        'validation'=>'validateExaminationHint',
        'system'=>'quiz',
        'function'=>'',
        'message'=>Yii::t('block','validateExamination'),
    ),
);
?>