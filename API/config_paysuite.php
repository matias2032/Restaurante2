<?php
// Caminho: Restaurante/API/config_paysuite.php

// Configurações da PaySuite
define('PAYSUITE_BASE_URL', 'https://paysuite.tech/api/v1');
define('PAYSUITE_API_TOKEN', '854|S31DEHpO17EaKxOZCwsKi0WVM5aYj26yjRPz12tD3d273e6f'); // substitua pelo seu token real
define('PAYSUITE_WEBHOOK_SECRET', 'whsec_5eadb165b2a66eab4c64d0ab7f47e9d4c5efd3a787d4d2f2'); // opcional, para validação
// CALLBACK URL (URL de Notificação Postback)
// O PaySuite vai enviar a confirmação de pagamento para este endereço.
define('CALLBACK_URL', 'https://web-production-51ac.up.railway.app/API/paysuite_callback.php');


// RETURN URL (URL de Retorno do Utilizador)
// O cliente é redirecionado para esta página após a conclusão do pagamento.
define('RETURN_URL', 'https://web-production-51ac.up.railway.app/finalizar_pedido.php');


// Funções auxiliares
function paysuite_log($msg) {
    $file = __DIR__ . '/logs/paysuite.log';
    file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL, FILE_APPEND);
}
?>
