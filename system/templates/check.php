<?php include("header.php"); ?>

<br>
<p class="lead">
  Це сторінка на якій можна перевірити як записано ваш голос
</p>
<p>
  Протягом всього часу голосування протокол голосування доступний у
  <a href="public/hashed_report.txt">закодованому вигляді</a>. <br>
  Тільки той хто знає коди перевірки, отримані на e-mail та в SMS повідомелнні,
  може перевірити як записано його голос.
</p>
<br>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">Ваша адреса e-mail</label>
    <div class="col-sm-4">
        <input type="email" class="form-control" id="email_input" name="email_input" maxlength="80"
            placeholder="Введіть e-mail" value="<?= h($email_value); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="email_code_input" class="col-sm-2 control-label">Код отримаий на e-mail</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="email_code_input" name="email_code_input" maxlength="12"
            placeholder="Введіть код з листа" value="<?= h($email_code); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-2 control-label">Ваш номер мобільного</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="mobile_input" name="mobile_input" maxlength="20"
            placeholder="+38 067 000-00-00" value="<?= h($mobile_value); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile_code_input" class="col-sm-2 control-label">Код отриманий в SMS</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="mobile_code_input" name="mobile_code_input" maxlength="12"
            placeholder="Введіть код з SMS" value="<?= h($mobile_code); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <br>
  <div class="form-group">
    <label for="mobile_input" class="col-sm-2 control-label">Обрані вами кандидати</label>
    <div class="col-sm-4">
        <input type="text" class="form-control mobile" id="vote_keys" name="vote_keys" maxlength="50"
            placeholder="Введіть номери кандидатів через кому: 1,2,3..." value="<?= h($vote_keys); ?>" <?= $form_readonly; ?>>
    </div>
  </div>
  <br>
  <?php if (!$form_readonly) { ?>
  <div class="form-group">
    <label for="email_input" class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-4">
      <div class="g-recaptcha" data-sitekey="<?= $settings['recaptcha_key']; ?>"></div>
    </div>
  </div>
  <br>
  <?php } else { ?>
  <div class="row">
    <div class="well col-sm-8">
      <dl>
        <dt>Як має виглядати закодований запис згідно введених даних:</dt>
          <dd><code><?= h($publine); ?></code></dd>
        <dt>Що фактично знайдено в протоколі:</dt>
          <dd class="last"><code><?= h($foundline); ?></code></dd>
      </dl>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <?php if (strlen($foundline) < 40) { ?>
      <p><b>Порада:</b></p>
      <p>Якщо запис в протоколі не знайдено уважно перевірте всі введені дані.
        Помилка навіть в одному символі не дозволить знайти запис в протоколі.</p>
      <?php } ?>
      <p><b>Посилання:</b></p>
      <ul>
        <li><a href="public/hashed_report.txt">Протокол голосування</a> з частково закодованими даними</li>
        <li><a href="public/public_report.txt">Повний протокол голосування</a> з частково знеособленними данними</li>
      </ul>
      <p>Для кодування даних використовується геш-функція ДСТУ ISO/IEC 10118-3:2005 SHA256
        з 100.000 ітерацій.</p>
    </div>
  </div>
  <br>
  <?php } ?>

  <div class="row">
    <a href="index.php" class="btn btn-default">&laquo; На початок</a>
    <?php if (!$form_readonly) { ?>
    <button type="submit" class="btn btn-primary">Перевірити &raquo;</button>
    <?php } ?>
  </div>
</form>

<script src='https://www.google.com/recaptcha/api.js?hl=uk'></script>

<?php include("footer.php"); ?>
