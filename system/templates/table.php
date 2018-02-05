<?php include("header.php"); ?>

<br>

<div class="results">
  <h2>Попередні результати голосування</h2>
  <p>
    Попередні результати голосування сформовано автоматично після завершення голосування на основі
    <a href="public_report.txt">відкритого електронного протоколу голосування</a>.
  </p>
  <p>
    Остаточні результати не пізніше ніж через три робочих дні після закінчення інтернет-голосування
    Голова Національного агентства затверджує у своєму наказі.
  </p>
  <p>
    Наказ про затвердження складу Громадської ради разом з оголошенням про результати інтернет-голосування
    із зазначенням кількості голосів, поданих за кожного кандидата, оприлюднюються на веб-сайті,
    на якому проводилось інтернет-голосування, на наступний день після його видання.
  </p>
  <br>
  <div class="table-responsive">
  <table class="table table-striped results_table">
    <thead>
      <tr>
        <th>Місце</th>
        <th>П.І.Б.</th>
        <th>Громадське об’єднання</th>
        <th>Голосів</th>
      </tr>
    </thead>
    <tbody>
    <?= results_table($results); ?>
    </tbody>
  </table>
  </div>
  <br>
</div>

<br>
<div>
  <a href="index.php" class="button-inverse">&laquo; На початок</a>
</div>

<?php include("footer.php"); ?>
