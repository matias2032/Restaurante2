<?php
// config/mailer.php - Usando API HTTP do Resend
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Envia email usando a API HTTP do Resend
 * Mais confiável que SMTP em ambientes cloud (Railway, Heroku, etc)
 */
function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $apiKey = $_ENV['RESEND_API_KEY'] ?? '';
    $fromEmail = $_ENV['FROM_EMAIL'] ?? '';
    $fromName = $_ENV['FROM_NAME'] ?? '';
    
    // Validação
    if (empty($apiKey)) {
        error_log("ERRO: RESEND_API_KEY não definida");
        return false;
    }
    
    if (empty($fromEmail)) {
        error_log("ERRO: FROM_EMAIL não definida");
        return false;
    }
    
    $data = [
        'from' => "$fromName <$fromEmail>",
        'to' => [$to],
        'subject' => $subject,
        'html' => $htmlBody
    ];
    
    error_log("Enviando email via Resend API para: $to");
    error_log("Payload: " . json_encode($data));
    
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
    $error = curl_error($ch);
    curl_close($ch);
    
    error_log("Resend API Response (HTTP $httpCode): $response");
    
    if ($error) {
        error_log("Erro CURL ao enviar email: $error");
        return false;
    }
    
    if ($httpCode !== 200) {
        error_log("Erro Resend API (HTTP $httpCode): $response");
        return false;
    }
    
    error_log("Email enviado com sucesso para $to");
    return true;
}

/**
 * COMPATIBILIDADE: Mantém a função antiga para não quebrar código existente
 * Mas agora usa a API HTTP internamente
 */
function getMailer() {
    // Esta função não é mais usada com a API HTTP
    // Mantida apenas para compatibilidade
    throw new Exception("Use sendEmail() em vez de getMailer() com Resend API");
}
