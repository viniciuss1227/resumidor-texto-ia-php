<?php
/*
 * Inicializa as variáveis que usaremos para exibir os resultados.
 * Começamos com elas como 'null' (vazio) para que o HTML lá embaixo 
 * saiba que não deve exibir nada se a página acabou de ser carregada 
 * e o formulár io ainda não foi enviado.
 */
$resumo = null;
$historico_result = null;
$texto_original = ""; // Guarda o texto do usuário para reexibir na textarea

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Configuração básica do banco - Essa parte é parte do banco de dados, vou estar usando o MySQL
    $mysqli = new mysqli("localhost", "root", "", "resume", 3307);
    if ($mysqli->connect_error) {
        die("Erro na conexão");
    }

    // 2. Recebe o texto do formulário - no HTML, na parte do <textarea> "texto"
    // É esse conteúdo que vai ser resumido pela IA
    $texto = $_POST["texto"];

    /* Guardamos o texto original em outra variável - $texto_original.
     * Usaremos isso lá no HTML para que o <textarea> continue preenchido
     * com o que o usuário digitou, mesmo depois da página recarregar.
     */
    $texto_original = $texto; 

    require_once "config.php"; 

    // 3. É essa estrutura que vai chamar a API no APYHub para fazer o resumo do texto
    $curl = curl_init();
    curl_setopt_array($curl, [
        // Nesse trecho setamos qual URL a requisição vai acessar (endpoint da API) 
        CURLOPT_URL => "https://api.apyhub.com/ai/summarize-text", 
        //Nessa parte ele retorna o resultado da requisição como uma string -- OBS: se não houvesse esse returntransfer, o resultado seria impresso diretamente na tela do navegador
        CURLOPT_RETURNTRANSFER => true, 
        // Aque ele ativa o método POST para enviar os dados
        CURLOPT_POST => true, 
        // Esse HTTP header serve para definir os cabeçalhos que serão enviados na requisição pela cURL. ex: O tipo de conteúdo que será enviado, a minha chave API e informações de linguagem, cache entre outras.
        CURLOPT_HTTPHEADER => [ 
            "Content-Type: application/json",
            // Essa é a API que eu gerei no site do APYHub
            "apy-token: $APY_TOKEN" 
        ],
        CURLOPT_POSTFIELDS => json_encode([ //O json_encode transforma o array PHP em JSON para enviar para a API
            "text" => $texto,
            "output_language" => "pt_BR"  
        ])
    ]);

    // 4. Aqui ele executa o pedido ou requisição para a API
    $response = curl_exec($curl);
    curl_close($curl);

    // 5. Converte a resposta JSON em um array PHP para usar os dados facilmente
    $resultado = json_decode($response, true);

    // Verificação de segurança: Checamos se a resposta da API realmente contém o resumo no local esperado.
    if (isset($resultado['data']['summary'])) {
        // 6. Pega o resumo do array retornado pela API
        $resumo = $resultado['data']['summary'];

        // 8. Aqui prepara o comando para salvar o texto e resumo na tabela "historico"
        $stmt = $mysqli->prepare("INSERT INTO historico (texto, resumo) VALUES (?, ?)");
        $stmt->bind_param("ss", $texto, $resumo);
        $stmt->execute();
        $stmt->close();

    } else {
        // Se a API falhar ou não retornar o resumo, definimos uma mensagem de erro
        $resumo = "Erro: Não foi possível gerar o resumo. Verifique o texto ou a conexão com a API.";
    }

    // 9. Ele busca os 5 últimos resumos salvos no banco de dados para exibir como histórico
    $historico_result = $mysqli->query("SELECT texto, resumo, datahora FROM historico ORDER BY id DESC LIMIT 5");

    // 10. Fecha a conexão com o banco de dados
    $mysqli->close();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resumidor de Texto com IA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Resumidor de Texto com IA</h1>
        <p class="descricao">Cole seu texto abaixo e deixe a inteligência artificial gerar um resumo claro e direto para você!</p>
        
        <form action="index.php" method="post">
            <label for="texto">Seu texto:</label><br>
            <textarea name="texto" id="texto" rows="10" cols="60" placeholder="Digite ou cole seu texto aqui..." required><?php echo htmlspecialchars($texto_original); ?></textarea><br><br>
            <button type="submit">✨ Gerar Resumo ✨</button>
        </form>

        <hr> <?php if ($resumo): ?>
            <div class="resultado-principal">
                <h3>Resumo Gerado:</h3>
                <p><?php echo htmlspecialchars($resumo); ?></p>
            </div>
        <?php endif; // Fim do 'if ($resumo)' ?>

        <?php if ($historico_result && $historico_result->num_rows > 0): ?>
            <div class="resultado">
                <h3>Histórico recente:</h3>
                <?php while ($row = $historico_result->fetch_assoc()): ?>
                    <p>
                        <strong>Data:</strong> <?php echo $row['datahora']; ?><br>
                        <strong>Texto:</strong> <?php echo htmlspecialchars($row['texto']); ?><br>
                        <strong>Resumo:</strong> <?php echo htmlspecialchars($row['resumo']); ?>
                    </p>
                    <hr> <?php endwhile; // Fim do 'while' ?>
            </div>
        <?php endif; // Fim do 'if ($historico_result)' ?>

    </div>
</body>
</html>




