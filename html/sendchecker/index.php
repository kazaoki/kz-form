<?php
@session_start();

// 初期値
if(!strlen(@$_SESSION['message'])) {
  $_SESSION['message'] = '送信テストです。
万が一受信された際は、お手数ですが破棄をお願いいたします。
test1
test2
test3
てすと テスト ﾃｽﾄ';
}

// POSTのときのみ処理
if ('POST'===$_SERVER['REQUEST_METHOD']) {
  // ライブラリロード
  require './jp_send_mail.php';

  @$_SESSION['from']    = $_POST['from'];
  @$_SESSION['to']      = $_POST['to'];
  @$_SESSION['subject'] = $_POST['subject'];
  @$_SESSION['message'] = $_POST['message'];

  // メール送信
  $_SESSION['is_success'] = jp_send_mail(
    [
      'to'            => $_POST['to'],
      'from'          => $_POST['from'],
      'subject'       => $_POST['subject'],
      'body'          => $_POST['message']
      // 'cc'            => ,
      // 'bcc'           => ,
      // 'reply'         => ,
      // 'f'             => ,
      // 'encoding'      => ,
      // 'headers'       => ,
      // 'files'         => ,
      // 'phpable'       => ,
      // 'startline'     => ,
      // 'force_hankana' => ,
      // 'wrap'          => ,
    ]
  );
  if($_SESSION['is_success']) {
    $_SESSION['result'] =
      'sendmail にてメール送信を実行しました。@'.date('Y-m-d H:i:s').'<br>'.
      '<div style="color:blue"><code>'.@$_SESSION['from'].'</code> → <code>'.@$_SESSION['to'].'</code></div>'
    ;
  } else {
    $_SESSION['result'] =
      'sendmail にてメール送信を実行しましたがエラーになりました。正しいメールアドレスか確認して下さい。@'.date('Y-m-d H:i:s').'<br>'.
      '<div style="color:blue"><code>'.@$_SESSION['from'].'</code> → <code>'.@$_SESSION['to'].'</code></div>'
    ;
  }
  header('Location: ./');
  exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>sendchecker</title>
<style>
.result {
  border: solid 1px #009688;
  padding: 10px;
  background: #0096881f;
  margin-bottom: 30px;
  &.result-OK {
    border-color: #009688;
    background: #0096881f;
  }
  &.result-NG {
    border-color: #ff5722;
    background: #ff57222b;
  }
}
</style>
</head>
<body>

<h1>メール送信テスト(sendchecker)</h1>

<?php if(isset($_SESSION['result'])) { ?>
<div class="result result-<?= $_SESSION['is_success'] ? 'OK':  'NG' ?>"><em>結果：</em><?= $_SESSION['result'] ?></div>
<?php unset($_SESSION['result']) ?>
<?php } ?>

<div>
  ※送信元のサーバIPは <em><?= gethostbyname(gethostname()) ?></em> です。
</div>
<br>

<form action="./" method="post" id="form" onsubmit="return(confirm('メールを送信します。'))">
  <div>
    ■ 送り元（From）：<input type="email" name="from" required placeholder="メールアドレス" value="<?= @$_SESSION['from'] ?>">
    <br><br>
    ■ 送り先（To）：<input type="email" name="to" required placeholder="メールアドレス" value="<?= @$_SESSION['to'] ?>">
    <br><br>
    ■ 件名（Subject）：<input type="text" name="subject" required placeholder="件名" value="<?= @$_SESSION['subject'] ?>">
  </div>
  <br>
  <div style="display:flex;align-items:flex-start;">
    ■ 送信内容：
    <textarea name="message" cols="50" rows="10" required><?= @$_SESSION['message'] ?></textarea>
  </div>
  <button>送信する</button>
</form>

</body>
</html>
