<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, оберіть ваших кандидатів в Раду громадського контролю.
</p>

<p class="lead">
  У вас залишилось <span class="countdown">?? хв.</span>
  Ви можете обрати ще <span class="candidates_left">кандидатів</span>.
</p>

<p class="timer_text">
  Якщо не встигнете, цю сессію голосування буде буде анульовано.
  В такому разі спробу можна повторити ще через 5 хв.
</p>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <table class="table table-striped candidates_table">
    <thead>
      <tr>
        <th>#</th>
        <th>П.І.Б.</th>
        <th>Громадське об’єднання</th>
        <th>Досьє</th>
      </tr>
    </thead>
    <tbody>
    <?= candidates_table(true); ?>
    </tbody>
  </table>
  <br>
  <div>
    <a href="index.php" class="button-inverse">&laquo; На початок</a>
    <button type="submit" class="button-default">Проголосувати &raquo;</button>
  </div>
</form>

<script>
  window.max_selected_limit = <?= get_selected_limit(); ?>;
  window.current_session_lifetime = <?= current_session_lifetime(); ?>;
</script>

<?php include("footer.php"); ?>
