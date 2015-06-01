<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, оберіть ваших кандидатів в Раду громадського контролю.
</p>

<p class="timer_text">
  У вас залишилось <span class="countdown">?? хв.</span>, щоб обрати кандидатів.
  Не хвилюйтесь, якщо не встигнете, можна буде переголосувати.
</p>

<form method="POST" role="form" class="form-horizontal">
  <?= csrf_token_input(); ?>
  <table class="table table-striped candidates_table">
    <thead>
      <tr>
        <th>#</th>
        <th>ПІБ</th>
        <th>ІГС</th>
        <th>Досьє</th>
      </tr>
    </thead>
    <tbody>
    <?= candidates_table(true); ?>
    </tbody>
  </table>
  <br>
  <div>
    <a href="index.php" class="btn btn-default">&laquo; На початок</a>
    <button type="submit" class="btn btn-danger">Проголосувати &raquo;</button>
  </div>
</form>

<script>
  window.max_selected_limit = <?= get_selected_limit(); ?>;
  window.current_session_lifetime = <?= current_session_lifetime(); ?>;
</script>

<?php include("footer.php"); ?>
