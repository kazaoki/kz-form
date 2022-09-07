<?php

/**
 * kz-form - カザオキ汎用メールフォーム（ケイジーフォーム）
 *
 * Version: 1.1.1
 * Last update: 2022-09-07
 *
 * バリデータライブラリ：Validon [https://github.com/kazaoki/validon]
 * メール送信ライブラリ：jp_send_mail() [https://kantaro-cgi.com/blog/php/php-jp_send_mail.html]
 */

// セッション開始
@session_start();

/**
 * メール送信処理
 */
function send($config)
{
	require __DIR__.'/jp_send_mail.php';

	if('POST'===$_SERVER['REQUEST_METHOD']){

		// 最終バリデート
		$result_set = validon($_POST);
		if(@count(@$result_set['errors'])) die ('mail send failed! (invalid data)');

		// CSRFチェック
		KZ::csrf_check();

		// メール送信
		foreach($config['mails'] as $mail) {

			// ウェイト（大量連続送信防止）
			if(isset($config['wait_interval_sec'])) sleep($config['wait_interval_sec']);

			// メールテンプレートロード
			if($mail['template'] && is_file($mail['template'])) {
				$mail['body'] = file_get_contents($mail['template']);
			}
			if(!isset($mail['phpable'])) $mail['phpable'] = true;

			// メール送信処理
			$result = jp_send_mail($mail);
			if(!$result) die ('mail send failed!');
		}

		// 完了画面へ移動
		header('Location: '.$_SERVER['SCRIPT_NAME']);
		exit;
	}
}

/**
 * KZクラスの静的メソッド
 */
class KZ
{
	/**
	 * アクセスしてきたIPを返す（プロクシやフォワード先のちゃんとしたクライアントIP）
	 *
	 * @return string
	 */
	public static function access_ip()
	{
		return
		@$_SERVER['REMOTE_ADDR']
			? $_SERVER['REMOTE_ADDR']
			: (
				@$_SERVER['HTTP_X_FORWARDED_FOR']
					? $_SERVER['HTTP_X_FORWARDED_FOR']
					: @$_SERVER['HTTP_X_ORIGINAL_FORWARDED_FOR']
			)
		;
	}

	/**
	 * HTMLエスケープ
	 *
	 * @param mixed $raw_string
	 * @return string
	 */
	public static function h($raw_string)
	{
		return htmlspecialchars($raw_string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * CSRFトークンを返す
	 *
	 * @return string $token
	 * @see https://qiita.com/mpyw/items/8f8989f8575159ce95fc
	 */
	public static function csrf_generate()
	{
		if (session_status() === PHP_SESSION_NONE) {
			throw new \BadMethodCallException('Session is not active.');
		}
		return hash('sha256', session_id());
	}

	/**
	 * 引数からCSRFトークンを検証する（エラーの場合、false返しまたはthrow）
	 *
	 * @param string $token
	 * @param boolean $throw
	 * @return boolean
	 */
	public static function csrf_validate($token, $throw = false)
	{
		$success = self::csrf_generate() === $token;
		if(!$success && $throw) {
			throw new \RuntimeException('CSRF validation failed.', 400);
		}
		return $success;
	}

	/**
	 * INPUT値からCSRFトークンを検証する（エラーの場合、400エラーで終了）
	 *
	 * @param string $input_name
	 * @return void
	 */
	public static function csrf_check($input_name='csrf_token')
	{
		if(!self::csrf_validate(filter_input(INPUT_POST, $input_name))) {
			header('Content-Type: text/plain; charset=UTF-8', true, 400);
			die('CSRF validation failed.');
		}
	}

	/**
	 * hiddenを一度に返す（特定用途キー指定可能）
	 *
	 * @param array|null $keys [任意、未指定の場合は全データ]
	 * @param array|null $param
	 * @return string
	 */
	public static function hiddens($keys=null, $param=null)
	{
		$html = '';

		// パラメータセット
		if(null===$param) $param = array_merge($_GET, $_POST);

		// 指定キーのみ
		if(is_array($keys) && count($keys)) {
			$param = array();
			foreach($keys as $key) {
				$param[$key] = @$_POST[$key] ? @$_POST[$key] : @$_GET[$key];
			}
		}

		// 再帰関数定義
		$f = function($vars, $prefix) use(&$f, &$html) {
			foreach($vars as $key=>$value) {
				if(is_array($value)) {
					$f($value, strlen($prefix) ? $prefix.'['.$key.']' : $key);
				} else {
					$html .= '<input type="hidden" name="'.$prefix.($prefix ? '['.self::h($key).']' : self::h($key)).'" value="'.self::h($value).'">'."\n";
				}
			}
		};

		// 再帰実行
		$f($param, '');

		return $html;
	}

	/**
	 * 指定キーを除外してhiddenを一度に返す
	 *
	 * @param array|null $exclude_keys [必須]
	 * @param array|null $param
	 * @return string
	 */
	public static function hiddens_exclude($exclude_keys, $param=null)
	{
		$html = '';

		// パラメータセット
		if(null===$param) $param = array_merge($_GET, $_POST);

		// キー指定除外
		foreach($exclude_keys as $key) {
			unset($param[$key]);
		}

		// 再帰関数定義
		$f = function($vars, $prefix) use(&$f, &$html) {
			foreach($vars as $key=>$value) {
				if(is_array($value)) {
					$f($value, strlen($prefix) ? $prefix.'['.$key.']' : $key);
				} else {
					$html .= '<input type="hidden" name="'.$prefix.($prefix ? '['.static::h($key).']' : static::h($key)).'" value="'.static::h($value).'">'."\n";
				}
			}
		};

		// 再帰実行
		$f($param, '');

		return $html;
	}

	/**
	 * BASE64で入ってきたPOSTデータを元に、画像ファイルを保存/更新/削除する
	 * -------------------------------------------------------------------------------------------------
	 */
	public static function base64_submit($filebase, $uploads_path, $base64=null, $delete=null)
	{
		if(!($base64||$delete)) return;

		// 新たなファイルが指定されているか
		if($base64) {
			// 新たにファイルがあれば既存のは削除
			foreach (glob($uploads_path.'/'.$filebase.'.*') as $filepath) {
				unlink($filepath);
			}

			// 新たなファイルを保存
			$tmpfile = tempnam('/tmp', 'slime-');
			file_put_contents($tmpfile, base64_decode(preg_replace('/^.*?base64\,/', '', $base64)));
			$ext = self::get_ext_from_file($tmpfile);
			chmod($tmpfile, 0644);
			$outfile = $uploads_path.'/'.$filebase.'.'.$ext;
			rename($tmpfile, $outfile);
			return $outfile;
		}

		// 削除指定の場合削除。
		else if($delete) {
			self::delete_by_glob($filebase.'.*', $uploads_path);
		}
	}

	/**
	 * 指定画像ファイルを指定のサイズ以内にリサイズ（サイズ内に収まっていれば何もしない。
	 * -------------------------------------------------------------------------------------------------
	 */
	public static function resize(string $file, int $max_width, int $max_height)
	{
		// ファイル情報読み出し
		list($org_width, $org_height, $type) = getimagesize($file);

		// 画像データロード
		switch ($type) {
			case IMAGETYPE_JPEG: $original = @imagecreatefromjpeg($file); break;
			case IMAGETYPE_PNG:  $original = @imagecreatefrompng($file);  break;
			case IMAGETYPE_GIF:  $original = @imagecreatefromgif($file);  break;
			default: return false;
		}
		if(!$original) return false;

		// サイズ内に収まっていれば何もしない。
		if($org_width <= $max_width && $org_height <= $max_height) return true;

		// リサイズ
		if(($org_width / $org_height) <= ($max_width / $max_height)) {
			$new_width = $max_height * ($org_width / $org_height);
			$new_height = $max_height;
		} else {
			$new_width = $max_width;
			$new_height = $max_width * ($org_height / $org_width);
		}
		$canvas = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($canvas, $original, 0, 0, 0, 0, $new_width, $new_height, $org_width, $org_height);

		// 保存
		switch ($type) {
			case IMAGETYPE_JPEG: imagejpeg($canvas, $file);   break;
			case IMAGETYPE_PNG:  imagepng($canvas, $file, 9); break;
			case IMAGETYPE_GIF:  imagegif($canvas, $file);    break;
			default: return false;
		}

		// メモリ解放
		@imagedestroy($original);
		@imagedestroy($canvas);

		return true;
	}

	/**
	 * 引数が正しければ checked を返す
	 * -------------------------------------------------------------------------------------------------
	 * ex. <input type="checkbox" name="sw" value="1"<?= KZ::checked($sw) ?>> // 第一引数を評価する
	 * ex. <input type="checkbox" name="type" value="AAA"<?= KZ::checked($types, 'AAA') ?>> // 第二引数と比較
	 */
	public static function checked()
	{
		$args = func_get_args();
		return 1===@count($args)
			? ($args[0] ? ' checked' : '')
			: (
				is_array($args[0])
					? (in_array($args[1], $args[0]) ? ' checked' : '')
					: (strval($args[0])===strval($args[1]) ? ' checked' : '')
			)
		;
	}

	/**
	 * 引数が正しければ selected を返す
	 * -------------------------------------------------------------------------------------------------
	 * ex. <option value="hoge"<?= KZ::selected($list, 'AAA') ?>>
	 */
	public static function selected($list, $need)
	{
		return is_array($list)
			? (in_array($need, $list) ? ' selected' : '')
			: (strval($need)===strval($list) ? ' selected' : '')
		;
	}

	/**
	 * アクセス元のIPを返す
	 * -------------------------------------------------------------------------------------------------
	 * ex. <?= KZ::get_remote_ip() ?>
	 */
	public static function get_remote_ip()
	{
		return
			@$_SERVER['HTTP_X_FORWARDED_FOR'] ?:
			@$_SERVER['HTTP_X_REAL_IP'] ?:
			@$_SERVER['HTTP_X_SAKURA_FORWARDED_FOR'] ?:
			@$_SERVER['HTTP_X_CLIENT_IP'] ?:
			@$_SERVER['HTTP_CF_CONNECTING_IP'] ?:
			@$_SERVER['REMOTE_ADDR']
		;
	}
}
