<?php

global $config;

$config =
[
	/**
	 * 通知メール（事務局へ）
	 * ---------------------------------------------------------------------------
	 */
	'admin' => [

		// 送信元メールアドレス
		'from' => 'テスト <webmaster@kazaoki.jp>',

		// 送信先メールアドレス
		'to' => [
			'テスト <webmaster@kazaoki.jp>',
		],

		// 件名
		'subject' => '【お問い合わせ】テスト',

		// メールテンプレートファイル
		'template' => __DIR__.'/mail-to-admin.php',
	],
];
