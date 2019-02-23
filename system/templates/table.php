<?php include("header.php"); ?>

<br>

<div class="results">
  <h2>Попередні результати голосування</h2>
  <p>
    Попередні результати голосування сформовано автоматично після завершення голосування на основі
    <a href="public_report.txt">відкритого електронного протоколу голосування</a>.
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
  <a href=".." class="button-inverse">&laquo; На початок</a>
</div>

<?php include("footer.php"); ?>
