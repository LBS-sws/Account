<?php

return array(
	'Payment Request'=>array(
		'access'=>'XA',
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
	'Transaction'=>array(
		'access'=>'XE',
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
		),
	),
	'Report'=>array(
		'access'=>'XB',
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
		),
	),
	'Import'=>array(
		'access'=>'XF',
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
	'System Setting'=>array(
		'access'=>'XC',
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
