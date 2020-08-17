<?php
ob_start();
// Сервер использует другую кодировку
header('Content-Type: text/html; charset= UTF-8');

$mode = !empty($_GET['mode']) ? $_GET['mode'] : '';
?>
<style>
form{
  display: inline-block;
}
</style>
<h1>Построение диаграмм бубликов</h1>

<img src="ex_3_2.php?mode=<?=$mode?>">

<div>
  <form method='GET'><input type='submit' name='mode' value='RandomizeData'></form>
  <form method='GET'><input type='submit' name='mode' value='AddDataset'></form>
  <form method='GET'><input type='submit' name='mode' value='RemoveDataset'></form>
  <form method='GET'><input type='submit' name='mode' value='AddData'></form>
  <form method='GET'><input type='submit' name='mode' value='RemoveData'></form>
</div>