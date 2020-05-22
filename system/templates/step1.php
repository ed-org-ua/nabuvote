<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, пройдіть тест і підтвердіть вашу згоду з правилами конкурсу.
</p>

<form method="POST" role="form" class="form-captcha">
  <div class="g-recaptcha" data-sitekey="<?= $settings['recaptcha_key']; ?>"></div>
  <br>
  <div class="checkbox">
    <input type="checkbox" id="ukr_citizen" name="ukr_citizen">
    <label for="ukr_citizen">
      Підтверджую, що голосую в цьому конкурсі вперше і що я є громадянином,
      який проживає на території України.
    </label>
  </div>
  <div class="checkbox">
    <input type="checkbox" id="personal_data" name="personal_data">
    <label for="personal_data">
      Погоджуюсь на збір та обробку персональних данних з метою запобігання
      повторного голосування.
    </label>
  </div>
  <div class="checkbox">
    <input type="checkbox" id="rules_agree" name="rules_agree">
    <label for="rules_agree">
      Підтверджую, що ознайомився і погоджуюсь з <a href="https://arma.gov.ua/public-council">
      Порядком організації та проведення конкурсу</a>, та <br>
      <br>
      <b>Попереджений</b>, що спроби автоматичних або автоматизованих запитів до системи
      голосування можуть призвести до наслідків, передбачених розділом
      XVI Кримінального кодексу України (злочини у сфері використання електронно-обчислювальних
      машин (комп’ютерів), систем та комп’ютерних мереж і мереж електрозв’язку).
    </label>
  </div>
  <br>
  <div>
    <a href="index.php" class="button-inverse">&laquo; На початок</a>
    <button type="submit" class="button-default">Продовжити &raquo;</button>
  </div>
</form>

<script src='https://www.google.com/recaptcha/api.js?hl=uk'></script>

<?php include("footer.php"); ?>
