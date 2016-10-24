<?php include("header.php"); ?>

<br>
<p class="lead">
  Для того щоб знайти і перевірити ваш голос введіть наступні дані
</p>
<br>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">Ваша адреса e-mail</label>
    <div class="col-sm-4">
        <input type="email" class="form-control" id="email_input" name="email_input" maxlength="80"
            placeholder="Введіть e-mail" value="<?= h($email_value); ?>" <?= $email_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="email_code_input" class="col-sm-2 control-label">Код отримаий на e-mail</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="email_code_input" name="email_code_input" maxlength="12"
            placeholder="Введіть код з листа" value="<?= h($email_code); ?>">
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-2 control-label">Ваш номер мобільного</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="mobile_input" name="mobile_input" maxlength="20"
            placeholder="+38 067 000-00-00" value="<?= h($mobile_value); ?>" <?= $mobile_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile_code_input" class="col-sm-2 control-label">Код отриманий в SMS</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="mobile_code_input" name="mobile_code_input" maxlength="12"
            placeholder="Введіть код з SMS" value="">
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-2 control-label">Обрані вами кандидати</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="vote_keys" name="vote_keys" maxlength="50"
            placeholder="Введіть номери кандидатів через кому: 1,2,3..." value="<?= h($vote_keys); ?>" <?= $mobile_readonly; ?>>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-4">
      <div class="g-recaptcha" data-sitekey="<?= $settings['recaptcha_key']; ?>"></div>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-4">
      <button type="submit" class="btn btn-primary">Перевірити</button>
      <a href="index.php" class="btn btn-default">Відмінити</a>
    </div>
  </div>
</form>

<script src='https://www.google.com/recaptcha/api.js?hl=uk'></script>

<?php include("footer.php"); ?>
