<?php require __DIR__.'/libs/kz-form.php' ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>test form</title>
<style>
table {
  border-collapse: collapse;
  margin-bottom: 20px;
}
table th,
table td {
  border: solid 1px #ccc;
  padding: 10px;
}
button {
  background: #eee;
  border: none;
  padding: 10px 20px;
  color: blue;
}
.error {
  color: red;
}
</style>
</head>
<body>

<h1>確認画面</h1>

<form action="finish.php" method="post" id="form">
  <?= KZ::hiddens() ?>
  <table>
    <tr>
      <th>お名前*</th>
      <td><?= KZ::h($_POST['name']) ?></td>
    </tr>
    <tr>
      <th>ご住所* </th>
      <td>
        〒<?= KZ::h($_POST['zip']) ?><br>
        <?= KZ::h($_POST['pref']) ?><br>
        <?= KZ::h($_POST['address']) ?>
      </td>
    </tr>
    <tr>
      <th>電話番号</th>
      <td><?= KZ::h($_POST['tel']) ?></td>
    </tr>
    <tr>
      <th>メールアドレス* </th>
      <td><?= KZ::h($_POST['mail']) ?></td>
    </tr>
    <tr>
      <th>お問い合わせ内容*<br>（1000文字以内）</th>
      <td><?= nl2br(KZ::h($_POST['naiyo'])) ?></td>
    </tr>
  </table>
  <div id="buttons" style="visibility:hidden">
    <button type="button" onclick="validon.back('./')">&laquo; 入力画面に戻る</button>
    <button type="submit">送信 &raquo;</button>
  </div>
</form>

<script src="libs/validon/validon.js"></script>
<script>
  var validon
  document.addEventListener('DOMContentLoaded', function (event) {
    validon = new Validon({
      form: '#form',
      config: 'contact',
      loadedFunc: function () {
        // Validonがロード完了したらボタン表示
        document.querySelector('#buttons').style.visibility = 'visible'
      },
    })
  })
</script>

</body>
</html>
