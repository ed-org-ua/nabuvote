<?php include("header.php"); ?>

<p class="lead">
    Дякуємо, ваш голос збережено!
</p>

<div class="row">
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">Створено електронний документ з наступними реквізитами</div>
      <table class="table">
        <tr><td>Час голосування:</td>
          <td><?= date('Y-m-d H:i:s', $_SESSION['vote_time']); ?></td></tr>
        <tr><td>Ваша IP адреса:</td>
          <td><?= h($_SESSION['ip_addr']); ?></td></tr>
        <tr><td>Адреса e-mail:</td>
          <td><?= h($_SESSION['email_value']); ?></td></tr>
        <tr><td>Номер мобільного:</td>
          <td><?= h($_SESSION['mobile_value']); ?></td></tr>
        <tr><td>Обрані кандидати:</td>
          <td><?= keys_to_candidates($_SESSION['vote_keys']); ?></td></tr>
      </table>
    </div>
  </div>
</div>

<br>
<div>
  <a href="index.php" class="btn btn-default">&laquo; На початок</a>
</div>

<?php include("footer.php"); ?>
