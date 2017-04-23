<?php include("header.php"); ?>

<p class="lead">
  Список учасників установчих зборів для формування складу Громадської ради при Мінприроди
</p>

<div class="row candidates">
  <?php $c_list = get_candidates(); foreach ($c_list as $c): ?>
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-3 photo">
        <img src="<?= $c['photo'] ? $c['photo'] : 'candidate/nophoto.jpg' ?>"
          alt="<?= h($c['name']) ?>">
        <!-- <label><input type="checkbox"> Обрати</label> -->
      </div>
      <div class="col-md-8">
        <dl class="dl-horizontal sm">
          <dt>№</dt><dd><?= $c['id'] ?></dd>
          <dt>П.І.Б.</dt><dd><?= h($c['name']) ?></dd>
          <dt>Організація</dt><dd><?= h($c['org']) ?></dd>
          <dt>Посилання</dt>
          <dd>
            <?php if($c['social']): ?>
              <a href="<?= $c['social'] ?>" target="_blank">Персональна сторінка</a><br>
            <?php endif; ?>
            <?php if($c['site']): ?>
              <a href="<?= $c['site'] ?>" target="_blank">Сайт організації</a><br>
            <?php endif; ?>
            <?php if (!$c['social'] && !$c['site']) echo "-"; ?>
          </dd>
          <dt>Документи</dt>
          <dd>
            <?php foreach ($c['docs'] as $d): ?>
              <a href="<?= $c['link'] ?>"><?= h($d) ?></a><br>
            <?php endforeach; ?>
          </dd>
        </dl>        
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="well text-center">
  <a href="step1.php" class="btn btn-lg btn-primary">Розпочати голосування</a>
</div>

<?php include("footer.php"); ?>
