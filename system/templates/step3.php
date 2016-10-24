<?php include("header.php"); ?>

<p class="lead">
    Будь ласка, вкажіть ваш номер мобільного (українського оператора).
</p>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-2 control-label">Ваш номер мобільного</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="mobile_input" name="mobile_input" maxlength="20"
            placeholder="+38 067 000-00-00" value="<?= h($mobile_value); ?>" <?= $mobile_readonly; ?>>
    </div>
  </div>
  <?php if ($mobile_value): ?>
  <div class="form-group">
    <label for="mobile_code_input" class="col-sm-2 control-label">Код підтвердження</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="mobile_code_input" name="mobile_code_input" maxlength="12"
            placeholder="Введіть код з SMS" value="">
    </div>
  </div>
  <div class="row">
    <div class="col-sm-offset-2 col-sm-4">
      Якщо SMS з кодом не приходить протягом 5 хвилин натисніть кнопку "На початок"
      і пройдіть перевірку заново.<br><br>
      Код необхідно ввести до <?= session_expires_hhmm(); ?>
    </div>
  </div>
  <?php endif; // $mobile_value ?>
  <br>
  <div>
    <a href="index.php" class="btn btn-default">&laquo; На початок</a>
    <button type="submit" class="btn btn-primary">Продовжити &raquo;</button>
  </div>
</form>

<?php include("footer.php"); ?>
