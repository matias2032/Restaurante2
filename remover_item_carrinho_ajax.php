<?php
session_start();
header("Content-Type: application/json");

// remover_item_carrinho_ajax.php → VERSÃO CORRIGIDA E DEFINITIVA

if (empty($_GET['uuid']) || !is_string($_GET['uuid']) || strlen($_GET['uuid']) > 36) {
    echo json_encode(["ok" => false, "erro" => "UUID inválido"]);
    exit;
}

$uuid = $_GET['uuid'];
include "conexao.php";

// ======================================================
// 1. USUÁRIO LOGADO → REMOVE DO BANCO (CORRIGIDO)
// ======================================================
if (!empty($_SESSION['usuario']['id_usuario'])) {
    $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

    // Query segura: remove apenas itens do usuário logado, independente do id_carrinho atual
    $sql = "DELETE ic FROM item_carrinho ic
            INNER JOIN carrinho c ON ic.id_carrinho = c.id_carrinho
            WHERE ic.uuid = ? AND c.id_usuario = ? AND c.status = 'activo'";

    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        echo json_encode(["ok" => false, "erro" => "Erro no prepare"]);
        exit;
    }

    $stmt->bind_param("si", $uuid, $id_usuario);
    $stmt->execute();

    $sucesso = $stmt->affected_rows > 0;
    $stmt->close();

    echo json_encode(["ok" => $sucesso]);
    exit;
}

// ======================================================
// 2. VISITANTE → REMOVE DO COOKIE
// ======================================================
if (!empty($_COOKIE['carrinho'])) {
    $carrinho = json_decode(urldecode($_COOKIE['carrinho']), true);

    // Garante que sempre seja array (evita JSON malformado)
    if (!is_array($carrinho)) {
        $carrinho = [];
    }

    // Remove o item com o UUID informado
    $carrinho = array_filter($carrinho, function($item) use ($uuid) {
        return ($item['uuid'] ?? '') !== $uuid;
    });

    // Reindexa o array e regrava o cookie
    $carrinho = array_values($carrinho);

    setcookie(
        "carrinho",
        !empty($carrinho) ? urlencode(json_encode($carrinho)) : '',
        [
            'expires'  => !empty($carrinho) ? time() + 86400 * 30 : time() - 3600,
            'path'     => '/',
            'secure'   => true,     // só HTTPS (Railway usa HTTPS)
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

    echo json_encode(["ok" => true]);
    exit;
}

// ======================================================
// NENHUM CARRINHO ENCONTRADO
// ======================================================
echo json_encode(["ok" => false, "erro" => "Carrinho não encontrado"]);
exit;
