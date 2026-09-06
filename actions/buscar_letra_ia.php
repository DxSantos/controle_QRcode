<?php
header('Content-Type: application/json');

// Carrega o arquivo .env localizado na raiz do projeto (uma pasta acima do diretório /actions)
$caminhoEnv = __DIR__ . '/../.env';
if (file_exists($caminhoEnv)) {
    $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || strpos($linha, '#') === 0) continue;
        
        list($chave, $valor) = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");
        
        putenv("{$chave}={$valor}");
        $_ENV[$chave] = $valor;
        $_SERVER[$chave] = $valor;
    }
}

// Recebe a requisição JSON enviada pelo AJAX
$data = json_decode(file_get_contents('php://input'), true);
$nomeArquivoOriginal = $data['nome_arquivo'] ?? '';

if (empty($nomeArquivoOriginal)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do arquivo não foi informado.']);
    exit;
}

// 1. Extrai o nome do arquivo sem a extensão
$nomeSemExtensao = pathinfo($nomeArquivoOriginal, PATHINFO_FILENAME);

// 2. Limpa prefixos numéricos e símbolos comuns
$nomeLimpo = preg_replace('/^[0-9_\s-]+/', '', $nomeSemExtensao);

// 3. Obtém a chave da API a partir do .env
$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');

if (empty($apiKey) || $apiKey === 'COLE_SUA_CHAVE_API_AQUI') {
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'A chave da API do Gemini não foi configurada no arquivo .env.'
    ]);
    exit;
}

// Prompt para obter EXCLUSIVAMENTE a letra sem textos adicionais
$prompt = "Forneça APENAS a letra da música ou salmo referente a: '{$nomeLimpo}'. "
        . "NÃO escreva nenhuma introdução, explicação ou comentário. "
        . "NÃO use formatação em Markdown (sem **, sem #, sem asteriscos). "
        . "Se não encontrar a letra exata, responda apenas: LETRA_NAO_ENCONTRADA.";

// Endpoint com o modelo ativo correto da API Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

// Executa a requisição via cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro cURL: ' . $curlError]);
    exit;
}

// Se a chamada falhar no modelo 2.5, executa um fallback transparente
if ($httpCode !== 200) {
    $urlFallback = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey;
    
    $chFb = curl_init($urlFallback);
    curl_setopt($chFb, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chFb, CURLOPT_POST, true);
    curl_setopt($chFb, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($chFb, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($chFb, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($chFb);
    $httpCode = curl_getinfo($chFb, CURLINFO_HTTP_CODE);
    curl_close($chFb);
}

if ($httpCode !== 200) {
    $errRes = json_decode($response, true);
    $msgErro = $errRes['error']['message'] ?? 'Código de erro HTTP: ' . $httpCode;
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro na API do Gemini: ' . $msgErro]);
    exit;
}

$resData = json_decode($response, true);
$textoResultado = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';

// Filtra e remove qualquer marcador de formatação Markdown residual
$textoLimpo = str_replace(['**', '*', '#'], '', $textoResultado);

if (strpos($textoLimpo, 'LETRA_NAO_ENCONTRADA') !== false || empty(trim($textoLimpo))) {
    echo json_encode(['sucesso' => false, 'mensagem' => "Não foi possível encontrar a letra para: '{$nomeLimpo}'"]);
} else {
    echo json_encode(['sucesso' => true, 'letra' => trim($textoLimpo)]);
}