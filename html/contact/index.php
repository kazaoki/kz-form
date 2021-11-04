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
table th ,
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

<h1>入力画面</h1>

<form action="confirm.php" method="post" id="form">
  <input type="hidden" name="csrf_token" value="<?= KZ::csrf_generate() ?>">
  <table border="1">
    <tr>
      <th>お名前*</th>
      <td><input type="text" name="name" value="<?= KZ::h(@$_POST['name']) ?>"></td>
    </tr>
    <tr>
      <th>ご住所*</th>
      <td>
        郵便番号：<input type="text" name="zip" value="<?= KZ::h(@$_POST['zip']) ?>" onKeyUp="AjaxZip3.zip2addr(this,'','pref','address')"><br>
        都道府県：
        <select name="pref">
          <option value="">選択してください</option>
          <?php foreach([
            '北海道',
            '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '東京都', '神奈川県', '埼玉県', '千葉県', '茨城県', '栃木県', '群馬県', '山梨県',
            '新潟県', '長野県', '富山県', '石川県', '福井県',
            '愛知県', '岐阜県', '静岡県', '三重県',
            '大阪府', '兵庫県', '京都府', '滋賀県', '奈良県','和歌山県',
            '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県',
            '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県',
            '沖縄県',
          ] as $pref) { ?>
          <option value="<?= $pref ?>" <?= KZ::selected($pref, @$_POST['pref']) ?>><?= $pref ?></option>
          <?php } ?>
        </select><br>
        住所：<input type="text" name="address"value="<?= KZ::h(@$_POST['address']) ?>">
      </td>
    </tr>
    <tr>
      <th>電話番号</th>
      <td><input type="text" name="tel" value="<?= KZ::h(@$_POST['tel']) ?>"></td>
    </tr>
    <tr>
      <th>メールアドレス*</th>
      <td><input type="text" name="mail" value="<?= KZ::h(@$_POST['mail']) ?>"><br></td>
    </tr>
    <tr>
      <th>お問い合わせ内容*<br>（1000文字以内）</th>
      <td><textarea name="naiyo" maxlength="1000"><?= KZ::h(@$_POST['naiyo']) ?></textarea></td>
    </tr>
  </table>
  <div id="buttons" style="visibility:hidden">
    <button type="submit">入力内容の確認画面へ &raquo;</button>
  </div>
</form>

<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<script src="libs/validon/validon.js"></script>
<script>
  var validon
  document.addEventListener('DOMContentLoaded', function (event) {
    validon = new Validon({
      form: '#form',
      config: 'contact',
      eachfire: true,
      errorgroup: 'td',
      errortag: '<div class="error">$message</div>',
      loadedFunc: function () {
        // Validonがロード完了したらボタン表示
        document.querySelector('#buttons').style.visibility = 'visible'
      },
      // startFunc: function(json){},
      finishFunc: function (json) {
        if (json.isSubmit) {
          var error = document.querySelector('#form .error')
          if (error) {
            var scroll_top = (error.getBoundingClientRect().top + window.pageYOffset) - (window.innerHeight / 2)
            if (-1 !== navigator.userAgent.indexOf('Trident')) {
              window.scroll(0, scroll_top) // for IE11
            } else {
              window.scroll({ top: scroll_top, behavior: 'smooth' }) // for other browser
            }
          }
        }
      }
    })
  })
</script>

</body>
</html>
