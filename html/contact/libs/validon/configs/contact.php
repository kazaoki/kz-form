<?php

global $_VALIDON;
global $_VALIDON_ENV;
mb_language('Japanese');
mb_internal_encoding('utf-8');

/**
 * Validon設定
 */
// 定義がない場合の警告（true:する false:しない）
$_VALIDON_ENV['NOTICE'] = false;
// 自動トリム機能
$_VALIDON_ENV['TRIM'] = true;
// 値ごとの共通事前バリデート
// $_VALIDON_ENV['BEFORE'] = function($key, &$value, &$params, &$errors, &$changes){ error_log('<<< BEFORE >>>'); };
// 値ごとの共通事後バリデート
// $_VALIDON_ENV['AFTER'] = function($key, &$value, &$params, &$errors, &$changes){ error_log('<<< AFTER >>>'); };

/**
 * お名前
 */
$_VALIDON['name'] = function(&$value, &$params, &$errors, &$changes)
{
    // 条件
    if(!strlen($value)) return 'お名前を入力してください。';
    if(mb_strlen($value) > 32) return '32文字以内で入力してください。';
};

/**
 * 郵便番号
 */
$_VALIDON['zip'] = function(&$value, &$params, &$errors, &$changes)
{
    // 全角英数字を半角に変換
    $value = mb_convert_kana($value, 'rnas');

    // 条件
    if(!strlen($value)) return '郵便番号を入力してください。';
    if(!preg_match('/^[\d\-]{7,}$/', $value)) return '正しい郵便番号を入力してください。';
};

/**
 * 都道府県
 */
$_VALIDON['pref'] = function(&$value, &$params, &$errors, &$changes)
{
    // 条件
    if(!strlen($value)) return '都道府県を選択してください。';
};

/**
 * 住所
 */
$_VALIDON['address'] = function(&$value, &$params, &$errors, &$changes)
{
    // 条件
    if(!strlen($value)) return '住所を入力してください。';
};

/**
 * 電話番号
 */
$_VALIDON['tel'] = function(&$value, &$params, &$errors, &$changes)
{
    // 全角英数字を半角に変換
    $value = mb_convert_kana($value, 'rnas');

    // 条件
    if(strlen($value)) {
        if(!preg_match('/^[\d\-\+]{10,}$/', $value)) return '正しい電話番号を入力してください。';
    }
};

/**
 * メールアドレス
 */
$_VALIDON['mail'] = function(&$value, &$params, &$errors, &$changes)
{
    // 条件
    if(!strlen($value)) return 'メールアドレスを入力してください。';
};

/**
 * お問い合わせ内容
 */
$_VALIDON['naiyo'] = function(&$value, &$params, &$errors, &$changes)
{
    // 条件
    if(!strlen($value)) return 'お問い合わせ内容を入力してください。';
    if(mb_strlen($value)>1000) return '1000文字以内で入力してください（現在'.mb_strlen($value).'文字）　※改行も１文字とします。';
};
