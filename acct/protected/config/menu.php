<?php

return array(
	'Payment Request'=>array(
		'access'=>'XA',
		'icon'=>'fa-money',
		'items'=>array(
			'Payment Request'=>array(
				'access'=>'XA04',
				'url'=>'/payreq/index',
			),
			'Request Confirmation'=>array(
				'access'=>'XA08',
				'url'=>'/confreq/index',
			),
			'Request Approval'=>array(
				'access'=>'XA05',
				'url'=>'/apprreq/index',
			),
			'Reimbursement'=>array(
				'access'=>'XA06',
				'url'=>'/realize/index',
			),
			'Reimbursement Approval'=>array(
				'access'=>'XA07',
				'url'=>'/signreq/index',
			),
		),
	),
	'Consult Fee'=>array( //咨詢費
		'access'=>'CF',
		'icon'=>'fa-briefcase',
		'items'=>array(
            'Consult Fee Apply'=>array(
                'access'=>'CF01',
                'url'=>'/consultApply/index',
            ),
            'Consult Fee Audit'=>array(
                'access'=>'CF02',
                'url'=>'/consultAudit/index',
            ),
            'Consult Fee Search'=>array(
                'access'=>'CF04',
                'url'=>'/consultSearch/index',
            ),
            'Consult Fee Set'=>array(
                'access'=>'CF03',
                'url'=>'/consultSet/index',
            ),
        )
    ),
	'Daily expense'=>array( //日常费用报销
		'access'=>'DE',
		'icon'=>'fa-glass',
		'items'=>array(
            'Expense Apply'=>array(//报销申请
                'access'=>'DE01',
                'url'=>'/expenseApply/index',
            ),
            'Expense Confirm'=>array(//报销确认
                'access'=>'DE02',
                'url'=>'/expenseConfirm/index',
            ),
            'Expense Audit'=>array(//报销审核
                'access'=>'DE03',
                'url'=>'/expenseAudit/index',
            ),
            'Expense Search'=>array(//报销查询
                'access'=>'DE04',
                'url'=>'/expenseSearch/index',
            ),
            'Expense Set Form'=>array(//费用归属设置
                'access'=>'DE05',
                'url'=>'/expenseSetName/index',
            ),
            'Expense Set Audit'=>array(//指定审核人
                'access'=>'DE06',
                'url'=>'/expenseSetAudit/index',
            ),
        )
    ),
	'Transaction'=>array(
		'access'=>'XE',
		'icon'=>'fa-exchange',
		'items'=>array(
			'Transaction(In)'=>array(
				'access'=>'XE01',
				'url'=>'/transin/index',
			),
			'Transaction(Out)'=>array(
				'access'=>'XE03',
				'url'=>'/transout/index',
			),
			'Transaction Enquiry'=>array(
				'access'=>'XE02',
				'url'=>'/transenq/index',
			),
			'Cash In Check'=>array(
				'access'=>'XE04',
				'url'=>'/cashinaudit/index',
			),
			'T3 Balance Checking'=>array(
				'access'=>'XE05',
				'url'=>'/t3audit/index',
			),
//			'Balance Adjustment'=>array(
//				'access'=>'XE06',
//				'url'=>'/baladj/index',
//			),
			'Bank Balance'=>array(
				'access'=>'XE07',
				'url'=>'/acctfile/index',
			),
		),
	),
	'Report'=>array(
		'access'=>'XB',
		'icon'=>'fa-file-text-o',
		'items'=>array(
			'Reimbursement Form'=>array(
				'access'=>'XB02',
				'url'=>'/report/reimburse',
			),
			'Transaction List'=>array(
				'access'=>'XB03',
				'url'=>'/report/translist',
			),
			'Operation Daily Report'=>array(
				'access'=>'XB04',
				'url'=>'#',
				'hidden'=>true,
			),
			'Report Manager'=>array(
				'access'=>'XB01',
				'url'=>'/queue/index',
			),
			'Daily Receipt Overview Report'=>array(
				'access'=>'XB05',
				'url'=>'#',
				'hidden'=>true,
			),
			'Daily Request Approval Summary'=>array(
				'access'=>'XB06',
				'url'=>'#',
				'hidden'=>true,
			),
			'Daily Reimbursement Approval Summary'=>array(
				'access'=>'XB07',
				'url'=>'#',
				'hidden'=>true,
			),
		),
	),
	'Generate Invoice'=>array(
		'access'=>'XI',
		'icon'=>'fa-bolt',
		'items'=>array(
			'Invoice'=>array(
				'access'=>'XI01',
				'url'=>'/invoice/index',
			)
		),
	),
	'Import'=>array(
		'access'=>'XF',
		'icon'=>'fa-bolt',
		'items'=>array(
			'Import'=>array(
				'access'=>'XF02',
				'url'=>'/import/index',
			),
			'Import Manager'=>array(
				'access'=>'XF01',
				'url'=>'/iqueue/index',
			),
		),
	),
    'Salary calculation'=>array(
        'access'=>'XS',
        'icon'=>'fa-money',
        'items'=>array(
            'Sales Commission'=>array(
                'access'=>'XS01',
                'url'=>'/sellCompute/index',
            ),
            'Sales Commission history'=>array(
                'access'=>'XS02',
                'url'=>'/sellSearch/index',
            ),
            'ID Sales Commission'=>array(
                'access'=>'XS10',
                'url'=>'/IDCommission/index?type=1',
            ),
            'ID Sales Commission history'=>array(
                'access'=>'XS11',
                'url'=>'/IDCommission/index',
            ),
            'ID Sales Commission ladder'=>array(
                'access'=>'XS09',
                'url'=>'/IDLadder/index',
            ),
            'Sales Commission ladder'=>array(
                'access'=>'XS03',
                'url'=>'/srate/index',
            ),
            'Product royalty ladder'=>array(
                'access'=>'XS08',
                'url'=>'/productsrate/index',
            ),
            'Prize Vault'=>array(
                'access'=>'XS04',
                'url'=>'/bonus/index',
            ),
            'Payroll File'=>array(
                'access'=>'XS05',
                'url'=>'/payroll/index',
            ),
            'Payroll File Approval'=>array(
                'access'=>'XS06',
                'url'=>'/payrollappr/index',
            ),
            'Sales commission table'=>array(
                'access'=>'XS07',
                'url'=>'/sellTable/index',
            ),
        ),
    ),
	'Plane System'=>array( //技术部直升机机制
		'access'=>'PS',
		'icon'=>'fa-plane',
		'items'=>array(
            'Plane Award'=>array(//技术部直升机奖励制度
                'access'=>'PS01',
                'url'=>'/planeAward/index'
            ),
            'Plane Allot'=>array(//参与技术部直升机
                'access'=>'PS02',
                'url'=>'/planeAllot/index'
            ),
            'Plane Set Money'=>array(//做单金额及对应的奖励
                'access'=>'PS03',
                'url'=>'/planeSetMoney/index'
            ),
            'Plane Set Job'=>array(//职务级别及对应的奖励
                'access'=>'PS04',
                'url'=>'/planeSetJob/index'
            ),
            'Plane Set Year'=>array(//年资及对应的奖金
                'access'=>'PS05',
                'url'=>'/planeSetYear/index'
            ),
            'Plane Set Other'=>array(//直升机外另计的项目
                'access'=>'PS06',
                'url'=>'/planeSetOther/index'
            ),
        )
    ),
	'System Setting'=>array(
		'access'=>'XC',
		'icon'=>'fa-gear',
		'items'=>array(
			'Account Type'=>array(
				'access'=>'XC01',
				'url'=>'/accttype/index',
				'tag'=>'@',
			),
			'Transaction Type'=>array(
				'access'=>'XC03',
				'url'=>'/transtype/index',
				'tag'=>'@',
			),
			'Accounting Item'=>array(
				'access'=>'XC06',
				'url'=>'/acctitem/index',
				'tag'=>'@',
			),
			'Default Account'=>array(
				'access'=>'XC05',
				'url'=>'/transtypedef/index',
			),
			'Account'=>array(
				'access'=>'XC02',
				'url'=>'/account/index',
			),
			'Approver'=>array(
				'access'=>'XC04',
				'url'=>'/approver/index',
			),
			'Delegation'=>array(
				'access'=>'XC07',
				'url'=>'/delegate/index',
			),
            'Invoice Email'=>array(//发票邮箱
                'access'=>'XC09',
                'url'=>'/invoiceEmail/index',
            ),
			'Notification Option'=>array(
				'access'=>'XC08',
				'url'=>'/site/notifyopt',
			),
		),
	),
//	'Security'=>array(
//		'access'=>'XD',
//		'items'=>array(
//			'User'=>array(
//				'access'=>'XD01',
//				'url'=>'/user/index',
//				'tag'=>'@',
//			),
//			'Group'=>array(
//				'access'=>'XD02',
//				'url'=>'/group/index',
//				'tag'=>'@',
//			),
//		),
//	),
);
