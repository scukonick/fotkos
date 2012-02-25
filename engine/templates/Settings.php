<?
if (! isset($result)){
	$result = 0;
}
?>
<div align='center' class='bigpictwrapper'>
	<? if ($user->getLogin()) { ?>
		<div class='settings'>
			<span class='big'>Привет, <?= $user->getLogin() ?>!</span><br>
			Ваша <a href="<?= $user->getHTTPLink() ?>">ссылка для входа</a>.
		</div>
	<? } else { ?>
		<div class='settings'>
			<span class='big'>Хочу зарегистрироваться!</span><br>
			Вы уже зарегистрированы! Сохраните <a href="<?= $user->getHTTPLink() ?>">эту ссылку</a> в своём браузере. Перейдя по ней, вы будете автоматически авторизованы.
		</div>
	<? } ?>
		<div id='settingleft' class='settings'>
	<? if ($user->getLogin()) { ?>
		<span class='big'>Хочу поменять пароль!</span><br>
		<form id='setpass' method='POST' action='/settings/'>
				<? if ($result) { ?>
					<cpan class='alert'><?= $result ?></cpan><br>
				<? } ?>
				Введите новый пароль: <input type='password' name='passw' id='passw'> <input type='submit' value='Ввести'>
				<input type='hidden' name='action' value='setpass'>
		</form>
	<? } else { ?>
			<span class='big'>Хочу логин с паролем!</span><br>
			В дополнение к регистрации вы можете добавить свой логин и пароль.<br>
			<form name='setpasslogin' method='POST' action='/settings/'>
				<? if ($result) { ?>
					<cpan class='alert'><?= $result ?></cpan><br>
				<? } ?>
				Введите логин: <input type='text' name='login' id='login'><br>
				Введите пароль: <input type='password' name='passw' id='passw'><br>
				<input type='hidden' name='action' value='setpasslogin'>
				<input type='submit' value='Ввести'>
			</form>
		</div>
	<? } ?>
	<? if (! isset($user->BYPASS)) { ?>
	<div id='settingsright' class='settings'>
	<form name='loginbylogin' method='POST' action='/settings/'>
				<span class='big'>Пустите меня!</span><br>
				Войдите под логином/паролем.<br>
				Введите логин: <input type='text' name='login' id='login'><br>
				Введите пароль: <input type='password' name='passw' id='passw'><br>
				<input type='hidden' name='action' value='loginbylogin'>
				<input type='submit' value='Ввести'>
	</form>
	</div>
	<? } ?>
</div>
