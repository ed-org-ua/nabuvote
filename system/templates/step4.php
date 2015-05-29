<?php include("header.php"); ?>

<p class="lead">
    Будь ласка, оберіть ваших кандидатів в Раду громадського контролю.
</p>

<form method="POST" role="form" class="form-horizontal">
  <table class="table table-striped">
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

<?php include("footer.php"); ?>
