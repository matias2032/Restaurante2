<?php
// public/test_smtp.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(30);

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../config/mailer.php';

echo "<h2>🔍 Diagnóstico SMTP Completo</h2>";

// 1. Verificar variáveis de ambiente
echo "<h3>1️⃣ Variáveis de Ambiente</h3><pre>";
echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_USER: " . ($_ENV['SMTP_USER'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_PASS: " . (isset($_ENV['SMTP_PASS']) ? '✅ OK (' . strlen($_ENV['SMTP_PASS']) . ' chars)' : '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_SECURE: " . ($_ENV['SMTP_SECURE'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "FROM_EMAIL: " . ($_ENV['FROM_EMAIL'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "FROM_NAME: " . ($_ENV['FROM_NAME'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "</pre>";

// 2. Verificar conectividade
echo "<h3>2️⃣ Teste de Conectividade</h3><pre>";
$host = $_ENV['SMTP_HOST'];
$port = $_ENV['SMTP_PORT'];

$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection) {
    echo "✅ Conexão com $host:$port bem-sucedida\n";
    fclose($connection);
} else {
    echo "❌ Erro ao conectar: $errstr ($errno)\n";
}
echo "</pre>";

// 3. Tentar enviar email
echo "<h3>3️⃣ Teste de Envio de Email</h3>";

try {
    $mail = getMailer();
    $mail->SMTPDebug = 2; // Mostra logs detalhados
    $mail->Debugoutput = function($str, $level) {
        echo htmlspecialchars($str) . "<br>";
    };
    
    $mail->addAddress($_ENV['FROM_EMAIL']); // Envia para seu próprio email
    $mail->Subject = "Teste SMTP - " . date('Y-m-d H:i:s');
    $mail->Body = "<h2>✅ Email de teste</h2><p>Se você recebeu este email, o SMTP está funcionando!</p>";
    
    echo "<pre style='background:#f0f0f0;padding:10px;'>";
    
    if ($mail->send()) {
        echo "</pre>";
        echo "<h3 style='color:green'>✅ EMAIL ENVIADO COM SUCESSO!</h3>";
        echo "<p>Verifique sua caixa de entrada: " . $_ENV['SMTP_USER'] . "</p>";
    } else {
        echo "</pre>";
        echo "<h3 style='color:red'>❌ Falha no envio</h3>";
        echo "<p>Erro: " . $mail->ErrorInfo . "</p>";
    }
    
} catch (Exception $e) {
    echo "</pre>";
    echo "<h3 style='color:red'>❌ EXCEÇÃO CAPTURADA</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr><p><strong>⚠️ DELETE este arquivo após os testes!</strong></p>";
