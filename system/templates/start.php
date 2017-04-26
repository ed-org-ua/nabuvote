<?php include("header.php"); ?>

<h2 style="color:#f33">Голосування розпочнеться 27 квітня 2017 року</h2>

<p class="lead">
  Список учасників установчих зборів для формування складу Громадської ради
</p>

<div class="row candidates">
  <?php $c_list = get_candidates(); foreach ($c_list as $c): ?>
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-3 photo">
        <img src="../candidates/photo/<?= $c['photo'] ?>"
          alt="<?= h($c['name']) ?>">
        <!-- <label><input type="checkbox"> Обрати</label> -->
      </div>
      <div class="col-md-8">
        <dl class="dl-horizontal sm">
          <dt>№</dt><dd><?= $c['id'] ?></dd>
          <dt>П.І.Б.</dt><dd><?= h($c['name']) ?></dd>
          <dt>Організація</dt><dd><?= h($c['ngo_name']) ?></dd>
          <dt>Посилання</dt>
          <dd>
            <?php if($c['social']): ?>
              <a href="<?= $c['social'] ?>" target="_blank">Персональна сторінка</a><br>
            <?php endif; ?>
            <?php if($c['ngo_social']): ?>
              <a href="<?= $c['ngo_social'] ?>" target="_blank">Сторінка організації</a><br>
            <?php endif; ?>
            <?php if($c['ngo_web']): ?>
              <a href="<?= $c['ngo_web'] ?>" target="_blank">Сайт організації</a><br>
            <?php endif; ?>
          </dd>
          <dt>Документи</dt>
          <dd>
            <?php
              $keys = array(
                'mot' => 'Мотиваційний лист',
                'bio' => 'Біографія',
                'res' => 'Результати діяльності',
                'tax' => 'Звіт про використання коштів');
              foreach ($keys as $key => $val) {
                if($c['docs'][$key]) {
                  echo "<a href=\"../candidates/$c[id].html\">$val</a><br>\n";
                } else {
                  echo "$val - немає<br>\n";
                }
              }
            ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!--
<div class="well text-center">
  <a href="step1.php" class="btn btn-lg btn-primary">Розпочати голосування</a>
</div>
-->

<script>
// check and force SSL
setTimeout(function() {
  var href = window.location.href,
  if (href.indexOf('http://gromrada.moz.gov.ua') === 0) {
    $.ajax({
      url: 'https://gromrada.moz.gov.ua/robots.txt',
      success: function(){
        window.location = window.location.href.replace(/^http:/, 'https:');
      },
    });
  }
}, 500);
</script>

<?php include("footer.php"); ?>
