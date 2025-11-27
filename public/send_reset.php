<?php
//public/send_reset.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 30); // Máximo 30 segundos

require_once __DIR__ . '/../src/PasswordReset.php';

echo "<!-- [1] Iniciando send_reset.php -->\n";
flush();

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (!$email) {
    echo "<!-- [2] Email inválido -->\n";
    flashMessage("E-mail inválido!");
    exit;
}

echo "<!-- [3] Email válido: $email -->\n";
flush();

try {
    echo "<!-- [4] Chamando sendPasswordResetLink() -->\n";
    flush();
    
    $msg = sendPasswordResetLink($pdo, $email);
    
    echo "<!-- [5] Link enviado com sucesso -->\n";
    flush();
    
    echo $msg; // Mostra a mensagem de sucesso
    
} catch (Exception $e) {
    echo "<!-- [6] ERRO: " . $e->getMessage() . " -->\n";
    flush();
    
    flashMessage("Erro ao enviar o e-mail: " . $e->getMessage());
}
