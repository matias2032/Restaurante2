<?php
$host = "turntable.proxy.rlwy.net";
$port = "56276";
$usuario = "root";
$password = "nnttbKnlsepXHfAwsDOuVOlkkGvngAkN";
$bd="railway";

$conexao= new mysqli($host,$port, $usuario, $password,$bd);

if ($conexao->connect_error) {
    die("Erro ao conectar à base de dados: " . $conexao->connect_error);
}




?>
