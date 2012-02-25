<div align='center' class='bigpictwrapper'>
	<? if ($Picture->NOTFOUND == 0) { ?>
<?			// GET SIZE 
			if ($Picture->WIDTH > 1024)
				$width = 1024;
			else
				$width = $Picture->WIDTH;
?>
				
		<img src='<?= $Picture->DIRECT?>' width='<?= $width ?>'>
		<center>
		<div id='pictlinks'>
			<div class='row'>
			<p class='left'><a href="<?= $Picture->FLINK ?>">Ссылка на картинку</a>:</p><p class='right'><input class='link' type='text' readonly value='<?= $Picture->FLINK ?>'></p>
			</div>
			<div class='row'>
			<p class='left'><a href="<?= $Picture->DIRECT ?>">Прямая ссылка</a>:</p><p class='right'><input class='link' type='text' readonly value='<?= $Picture->DIRECT ?>'></p>
			</div>
			<div class='row'>
			<p class='left'><a href="<?= $Picture->CACHE ?>">Прямая ссылка на миниатюру</a>:</p><p class='right'><input class='link' type='text' readonly value='<?= $Picture->CACHE ?>'></p>
			</div>
			<div class='row'>
			<p class='left'>HTML-код (превью по клику):</p><p class='right'><input class='link' type='text' readonly value='<?= htmlentities($Picture->HTML) ?>'></p>
			</div>
			<div class='row'>
			<p class='left'>HTML-код:</p><p class='right'><input class='link' type='text' readonly value='<?= htmlentities($Picture->EHTML) ?>'></p>
			</div>
		</div>
		</center>
	<? } else { ?>
		<h4>Упс! Картинка не найдена.</h4>
	<? }?>
</div>
