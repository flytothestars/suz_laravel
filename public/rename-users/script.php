<?php

$olds = explode("\n", $_POST['old_usernames']);
$news = explode("\n", $_POST['new_usernames']);
if(count($olds) != count($news))
{
	echo json_encode([
		'success' => false,
		'html' => 'Количество строк не совпадает!'
	]);
	exit;
}
foreach($olds as $key => $old)
{
	if($old == '')
	{
		continue;
	}
	$new = addslashes($news[$key]);
	$old = addslashes($old);
	$commands[] = "UPDATE `users` set `email` = CONCAT('" . $new . "', '@almatv.kz'), `username` = '" . $new . "' where `username` = '" . $old . "';";
}

$conn = mysqli_connect('localhost', 'root', 'lmw57Szii', 'suz_db') or die('Не могу соединиться с базой данных!');

if(mysqli_connect_errno())
{
    echo json_encode([
		'success' => false,
		'html' => "Соединение невозможно: " . mysqli_connect_error()
	]);
    exit();
}
foreach($commands as $command)
{
	if(!mysqli_query($conn, $command))
	{
	    echo json_encode([
			'success' => false,
			'html' => "Ошибка: " . mysqli_error($conn)
		]);
	    exit;
	}
}
echo json_encode([
	'success' => true,
	'html' => 'Скрипт успешно выполнен!'
]);