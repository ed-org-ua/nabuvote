<?php include("header.php"); ?>

<br>
<p class="lead">
  Це сторінка на якій можна перевірити як записано ваш голос
</p>
<p>
  Протягом всього часу голосування протокол голосування доступний у
  <a href="public/hashed_report.txt" target="_blank">закодованому вигляді</a>. <br>
  Щоб перевірити як збережно ваш голос введіть дані в форму нижче. <br>
  Тільки той хто знає два коди перевірки, отримані на e-mail та в SMS повідомелнні,
  може перевірити як записано голос. <br>
  Увага, помилка навіть в одному символі не дозволить знайти голос.
</p>
<br>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-3 control-label">Ваша адреса e-mail</label>
    <div class="col-sm-4">
        <input type="email" class="form-control" id="email_input" name="email_input" maxlength="80"
            placeholder="Введіть e-mail" value="<?= h($email_value); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="email_code_input" class="col-sm-3 control-label">Код отримаий на e-mail</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="email_code_input" name="email_code_input" maxlength="12"
            placeholder="Введіть код з листа" value="<?= h($email_code); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-3 control-label">Ваш номер мобільного</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="mobile_input" name="mobile_input" maxlength="20"
            placeholder="+38 067 000-00-00" value="<?= h($mobile_value); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile_code_input" class="col-sm-3 control-label">Код отриманий в SMS</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="mobile_code_input" name="mobile_code_input" maxlength="12"
            placeholder="Введіть код з SMS" value="<?= h($mobile_code); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-3 control-label">Обрані при голосуванні кандидати</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="vote_keys" name="vote_keys" maxlength="50"
            placeholder="Введіть номери кандидатів через кому: 1,2,3..." value="<?= h($vote_keys); ?>" <?= $form_readonly; ?>>
        <p class="help-block">Порада: щоб не помилитись — скопіюйте ці дані з фінального листа.</p>
    </div>
  </div>
  <br>
  <?php if (!$form_readonly) { ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-3 control-label">&nbsp;</label>
    <div class="col-sm-4">
      <div class="g-recaptcha" data-sitekey="<?= $settings['recaptcha_key']; ?>"></div>
    </div>
  </div>
  <br>
  <?php } else { ?>
  <div class="row">
    <div class="well check col-sm-10">
      <dl>
        <dt>Як буде виглядати закодований запис згідно введених даних:</dt>
          <dd><code><?= h($publine); ?></code></dd>
        <br>
        <dt>Що фактично знайдено в протоколі:</dt>
          <dd><code><?= h($foundline); ?></code></dd>
        <br>
        <dt>Результат:</dt>
          <?php if (strlen($foundline) > 40) { ?>
          <dd class="last"><strong style="color:#383">Голос знайдено</strong></dd>
          <?php } else { ?>
          <dd class="last"><strong style="color:#e33">Голос не знайдено</strong>, − перевірте свої дані.</dd>
          <?php } ?>
      </dl>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <?php if (strlen($foundline) < 40) { ?>
      <p><b>Порада:</b></p>
      <p>Якщо запис в протоколі не знайдено — уважно перевірте всі дані.
        Помилка навіть в одному символі не дозволить знайти закодований
        запис в протоколі.</p>
      <?php } ?>
    </div>
  </div>
  <br>
  <?php } ?>

  <div class="row">
    <a href="index.php" class="button-inverse">&laquo; На початок</a>
    <?php if (!$form_readonly) { ?>
    <button type="submit" class="button-default">Перевірити &raquo;</button>
    <?php } else { ?>
    <a href="check.php" class="button-default">Повторити спробу</a>
    <a href="public/hashed_report.txt" target="_blank">Закодований протокол</a>
    <?php } ?>
  </div>
</form>

<script src='https://www.google.com/recaptcha/api.js?hl=uk'></script>

<?php include("footer.php"); ?>
