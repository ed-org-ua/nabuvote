<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, вкажіть адресу вашої електронної пошти.
</p>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">Ваша адреса e-mail</label>
    <div class="col-sm-4">
        <input type="email" class="form-control" id="email_input" name="email_input" maxlength="80"
            placeholder="Введіть e-mail" value="<?= h($email_value); ?>" <?= $email_readonly; ?>>
    </div>
  </div>
  <?php if ($email_value): ?>
  <div class="form-group">
    <label for="email_code_input" class="col-sm-2 control-label">Код підтвердження</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="email_code_input" name="email_code_input" maxlength="10"
            placeholder="Введіть код з листа" value="<?= h($email_code); ?>">
    </div>
  </div>
  <div class="row">
    <div class="col-sm-offset-2 col-sm-4">
      Якщо лист з кодом не приходить протягом 5 хвилин перевірте папку "Спам",
      або натисніть кнопку "На початок" і пройдіть перевірку заново.
    </div>
  </div>
  <?php endif; // $email_value ?>
  <br>
  <div>
    <a href="index.php" class="btn btn-default">&laquo; На початок</a>
    <button type="submit" class="btn btn-primary">Продовжити &raquo;</button>
  </div>
</form>

<?php include("footer.php"); ?>
