<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, пройдіть тест і підтвердіть вашу згоду з правилами конкурсу.
</p>

<script src='https://www.google.com/recaptcha/api.js'></script>

<form method="POST" role="form">
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
  <br>
  <div>
    <a href="index.php" class="btn btn-default">&laquo; На початок</a>
    <button type="submit" class="btn btn-primary">Продовжити &raquo;</button>
  </div>
</form>

<?php include("footer.php"); ?>
