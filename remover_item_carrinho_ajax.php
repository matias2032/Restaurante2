<?php
session_start();
header("Content-Type: application/json");

//remover_item_carrinho.ajax

if (!isset($_GET['uuid'])) {
    echo json_encode(["ok" => false, "erro" => "UUID não informado"]);
    exit;
}

$uuid = $_GET['uuid'];

include "conexao.php";

// ======================================================
// USUÁRIO LOGADO → REMOVER DO BANCO
// ======================================================

if (!empty($_SESSION['usuario']['id_usuario'])) {
    $id_usuario = (int)$_SESSION['usuario']['id_usuario'];
    $uuid       = $_GET['uuid'];

    // Remove pelo UUID + dono do carrinho (independente do id_carrinho ativo)
    $sql = "DELETE ic FROM item_carrinho ic
            INNER JOIN carrinho c ON ic.id_carrinho = c.id_carrinho
            WHERE ic.uuid = ? AND c.id_usuario = ? AND c.status = 'activo'";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("si", $uuid, $id_usuario);
    $stmt->execute();

    echo json_encode(["ok" => $stmt->affected_rows > 0]);
    exit;
}

// ======================================================
// VISITANTE → REMOVER DO COOKIE
// ======================================================
if (!empty($_COOKIE['carrinho'])) {

    $carrinho = json_decode(urldecode($_COOKIE['carrinho']), true);

    if (!is_array($carrinho)) {
        $carrinho = [];
    }

    // Remove item pelo UUID
    $carrinho = array_filter($carrinho, function($item) use ($uuid) {
        return ($item['uuid'] ?? null) !== $uuid;
    });

    // Regrava cookie atualizado
    setcookie(
        "carrinho",
        urlencode(json_encode(array_values($carrinho))),
        time() + 86400 * 30,
        "/"
    );

    echo json_encode(["ok" => true]);
    exit;
}

echo json_encode(["ok" => false, "erro" => "Carrinho não encontrado"]);
exit;

