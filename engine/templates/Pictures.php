<?
	$ErrPictures = array();
?>
<div class='bigpictwrapper' align='center'>
<div class='medpictwrapper'>
<? if (count($Pictures) > 0 ){ ?>
	<? foreach ($Pictures as $Picture){  ?>
		<?	if ($Picture->ERROR === 0 ) { ?>
			<div class='littlepictwrap'>
				<a class='prevs' href='<?= $Picture->FLINK ?>'><img src='<?= $Picture->CACHE?>' alt='Кликните, чтобы получить ссылки'></a>
			</div>
		<? } else { ?>
			<? $ErrPictures[] = $Picture; ?>
		<? } ?>
	<? } ?>
	
<? } else { ?>
<? } ?>
</div>	
<? if (count($ErrPictures) > 0){ ?>
	При загрузке следующих картинок произошли ошибки:<br>
	<? foreach ($ErrPictures as $Picture){ ?>
		<?= $Picture->ORIGNAME ?>: <?= $Picture->ERROR ?><br>
	<? } ?>
<? } ?>
</div>	
