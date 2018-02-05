<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, оберіть ваших кандидатів в Громадську раду.
</p>

<p class="lead">
  У вас залишилось <span class="countdown">?? хв.</span>
  Ви можете обрати <span class="candidates_left">кандидатів</span>.
</p>

<p class="timer_text">
  Якщо не встигнете, цю сессію голосування буде буде анульовано.
  В такому разі спробу можна повторити ще через 5 хв.
</p>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <div class="table-responsive">
  <table class="table table-striped candidates_table">
    <thead>
      <tr>
        <th>№, П.І.Б.</th>
        <th>Громадське об’єднання</th>
        <th>Досьє</th>
      </tr>
    </thead>
    <tbody>
    <?= candidates_table(true); ?>
    </tbody>
  </table>
  </div>
  <br>
  <h4>Обрані кандидати</h4>
  <div id="selected_candidates">
    <i>Ще не обрано жодного кандидата</i>
  </div>
  <br><br>
  <div>
    <a href="index.php" class="button-inverse">&laquo; На початок</a>
    <button type="submit" class="button-default">Проголосувати</button>
  </div>
</form>

<script>
  window.max_selected_limit = <?= get_selected_limit(); ?>;
  window.current_session_lifetime = <?= current_session_lifetime(); ?>;
</script>

<?php include("footer.php"); ?>
