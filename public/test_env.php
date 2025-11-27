<?php
// public/test_env.php - Teste de carregamento do .env

require_once __DIR__ . '/../config/env_loader.php';

echo "<h2>✅ Teste de Variáveis de Ambiente</h2>";
echo "<pre>";
echo "APP_URL: " . ($_ENV['APP_URL'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "DB_HOST: " . (strpos($_ENV['DB_DSN'], 'host=') !== false ? '✅ OK' : '❌ ERRO') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_USER: " . ($_ENV['SMTP_USER'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "SMTP_PASS: " . (isset($_ENV['SMTP_PASS']) ? '✅ DEFINIDA (' . strlen($_ENV['SMTP_PASS']) . ' caracteres)' : '❌ NÃO DEFINIDA') . "\n";
echo "FROM_EMAIL: " . ($_ENV['FROM_EMAIL'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "FROM_NAME: " . ($_ENV['FROM_NAME'] ?? '❌ NÃO DEFINIDA') . "\n";
echo "</pre>";

echo "<p style='color:green;font-weight:bold;'>✅ Arquivo .env carregado com sucesso!</p>";
echo "<p><a href='test_smtp.php'>→ Próximo passo: Testar SMTP</a></p>";
