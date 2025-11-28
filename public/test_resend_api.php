<?php
// public/test_resend_api.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../config/mailer.php';

echo "<h2>🧪 Teste Resend API HTTP</h2>";

// 1. Verificar variáveis
echo "<h3>1️⃣ Variáveis de Ambiente</h3><pre>";
echo "RESEND_API_KEY: " . (isset($_ENV['RESEND_API_KEY']) ? '✅ OK (' . strlen($_ENV['RESEND_API_KEY']) . ' chars)' : '❌ NÃO DEFINIDA') . "\n";
echo "FROM_EMAIL: " . ($_ENV['FROM_EMAIL'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "FROM_NAME: " . ($_ENV['FROM_NAME'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "</pre>";

// 2. Teste de conectividade HTTP
echo "<h3>2️⃣ Teste de Conectividade</h3><pre>";
$ch = curl_init('https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode > 0) {
    echo "✅ Conexão com api.resend.com bem-sucedida (HTTP $httpCode)\n";
} else {
    echo "❌ Falha ao conectar com api.resend.com\n";
}
echo "</pre>";

// 3. Enviar email de teste
echo "<h3>3️⃣ Teste de Envio de Email</h3>";

// Captura os logs
ob_start();

try {
    $testEmail = $_ENV['FROM_EMAIL']; // Envia para você mesmo
    
    echo "<p>Enviando email de teste para: <strong>$testEmail</strong></p>";
    
    // Faz uma chamada direta para ver o erro
    $apiKey = $_ENV['RESEND_API_KEY'];
    $data = [
        'from' => $_ENV['FROM_NAME'] . ' <' . $_ENV['FROM_EMAIL'] . '>',
        'to' => [$testEmail],
        'subject' => "Teste Resend API - " . date('Y-m-d H:i:s'),
        'html' => "<h2>✅ Teste bem-sucedido!</h2><p>Se você recebeu este email, a API do Resend está funcionando!</p>"
    ];
    
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "<h4>📊 Detalhes da Requisição:</h4>";
    echo "<pre style='background:#f5f5f5;padding:10px;border-radius:5px;'>";
    echo "HTTP Code: $httpCode\n";
    echo "CURL Error: " . ($curlError ?: "Nenhum") . "\n";
    echo "Response: " . htmlspecialchars($response) . "\n";
    echo "</pre>";
    
    if ($httpCode === 200) {
        echo "<div style='background:#d4edda;color:#155724;padding:20px;border-radius:8px;'>";
        echo "<h3>✅ EMAIL ENVIADO COM SUCESSO!</h3>";
        echo "<p>Verifique sua caixa de entrada: <strong>$testEmail</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:8px;'>";
        echo "<h3>❌ Falha no envio (HTTP $httpCode)</h3>";
        
        $responseData = json_decode($response, true);
        if ($responseData) {
            echo "<p><strong>Erro:</strong> " . htmlspecialchars($responseData['message'] ?? 'Erro desconhecido') . "</p>";
            if (isset($responseData['name'])) {
                echo "<p><strong>Tipo:</strong> " . htmlspecialchars($responseData['name']) . "</p>";
            }
        }
        
        echo "<h4>Possíveis causas:</h4>";
        echo "<ul>";
        echo "<li>Email não verificado no Resend</li>";
        echo "<li>API Key inválida ou sem permissões</li>";
        echo "<li>Formato do email incorreto</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:8px;'>";
    echo "<h3>❌ EXCEÇÃO CAPTURADA</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}

echo "<hr><p><strong>⚠️ DELETE este arquivo após os testes!</strong></p>";
echo "<p><a href='forgot_password.php'>→ Testar fluxo completo de reset de senha</a></p>";
