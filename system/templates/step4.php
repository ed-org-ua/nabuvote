<?php include("header.php"); ?>

<p class="lead">
  Будь ласка, оберіть ваших кандидатів в Раду громадського контролю.
</p>

<p class="timer_text">
  У вас залишилось <span class="countdown">?? хв.</span> щоб обрати кандидатів.
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
  (function(){
    var max_selected_limit = <?= get_selected_limit(); ?>;
    setTimeout(function(){
      $('.candidates_table input[type=checkbox]').click(function(event){
        var selected_count = $('.candidates_table input:checked').length;
        if (selected_count > max_selected_limit) {
          event.preventDefault();
          event.stopPropagation();
          setTimeout(function(){
            alert("Ви обрали більше ніж дозволено кандидатів.");
          }, 500);
          return false;
        }
      });
    }, 1000);
    var current_session_lifetime = <?= current_session_lifetime(); ?>;
    if (window.vote_timer)
      clearInterval(window.vote_timer);
    window.vote_timer = setInterval(function(){
      if (current_session_lifetime < 15) {
        $('.timer_text').html('Час сплив. Будь ласка, переголосуйте.');
        clearInterval(window.vote_timer);
        return;
      }
      current_session_lifetime = current_session_lifetime - 1;
      var ts = Math.floor(current_session_lifetime/60) + ' хв.';
      $('.countdown').html(ts);
    }, 1000);
  })();
</script>

<?php include("footer.php"); ?>
