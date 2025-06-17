<?php
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Inicia a sessão PHP
session_start();

// Verifica se o ID da viagem foi fornecido
if (!isset($_GET['id_viagem'])) {
    die("ID da viagem não fornecido.");
}

// Redireciona se o utilizador não estiver logado
if (!isset($_SESSION["utilizador"])) {
    header("Location: index.php");
    exit();
}

// Associar o ID da viagem
$id_viagem = $_GET['id_viagem'];
// Associar o ID do utilizador que está na sessão 
$id_utilizador = $_SESSION["utilizador"]["id_utilizador"] ?? null;
// Mensagem de feedback para o utilizador
$mensagem = "";
// Erro de compra
$erro = false;

// Obter id_carteira do utilizador (necessário para ver saldo)
// Preparar a query para obter o id_carteira do utilizador
$stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
// Fazer o bind do parâmetro
$stmt->bind_param("i", $id_utilizador);
// Executar a query
$stmt->execute();
// Obter o resultado
$res = $stmt->get_result();

// Verificar se o utilizador tem carteira associada
if ($res->num_rows === 0) {
    die("Utilizador não tem carteira associada.");
}

// Obter o id_carteira do utilizador
$id_carteira = $res->fetch_assoc()["id_carteira"];

// Verificar se o utilizador está a tentar comprar um bilhete (clicar no botão de compra)
if (isset($_POST['confirmar_compra'])) {
    // Iniciar transação para garantir que apenas é feita a compra depois do commit
    $conn->begin_transaction();

    try {
        // Obter dados da viagem
        $stmt = $conn->prepare("SELECT v.preco, v.id_autocarro, a.lugares 
                                FROM viagem v 
                                INNER JOIN autocarro a ON v.id_autocarro = a.id_autocarro 
                                WHERE v.id_viagem = ?");
        // Fazer o bind do parâmetro
        $stmt->bind_param("i", $id_viagem);
        // Executar a query
        $stmt->execute();
        // Obter o resultado
        $resultado = $stmt->get_result();

        // Verificar se foram encontrados os dados da viagem
        if ($resultado->num_rows === 0) {
            throw new Exception("Viagem não encontrada.");
        }

        // Obter os dados da viagem
        $dados_viagem = $resultado->fetch_assoc();
        // Associar o valor da viagem
        $valor_viagem = $dados_viagem["preco"];
        // e o número de lugares
        $lugares_totais = $dados_viagem["lugares"];

        // Verificar lugares ocupados
        // Preparar a query para contar os lugares ocupados
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bilhete WHERE id_viagem = ?");
        // Fazer o bind do parâmetro
        $stmt->bind_param("i", $id_viagem);
        // Executar a query
        $stmt->execute();
        // Obter o número de lugares ocupados
        $lugares_ocupados = $stmt->get_result()->fetch_assoc()["total"];

        // Verificar se a viagem está cheia
        if ($lugares_ocupados >= $lugares_totais) {
            throw new Exception("A viagem está cheia.");
        }

        // Retirar saldo ao cliente (carteira do utilizador)
        $resultado_retirar = retirarSaldo($conn, $id_carteira, $valor_viagem, COMPRAR_BILHETE);
        // Verificar se a retirada foi bem-sucedida
        if (!$resultado_retirar['success']) {
            throw new Exception($resultado_retirar['message']);
        }

        // Adicionar saldo à empresa (carteira 1)
        $resultado_adicionar = adicionarSaldo($conn, 1, $valor_viagem, VENDER_BILHETE);
        // Verificar se a adição foi bem-sucedida
        if (!$resultado_adicionar['success']) {
            throw new Exception($resultado_adicionar['message']);
        }

        // Inserir bilhete
        // Gera um identificador único para o bilhete (do tipo BILHETE_1a3b5c7d9...)
        $identificador = uniqid("BILHETE_");
        // Preparar a query para inserir o bilhete
        $stmt = $conn->prepare("INSERT INTO bilhete (id_utilizador, id_viagem, data_compra, identificador) VALUES (?, ?, NOW(), ?)");
        // Fazer o bind dos parâmetros
        $stmt->bind_param("iis", $id_utilizador, $id_viagem, $identificador);
        // Executar a query para inserir o bilhete
        $stmt->execute();

        // Fazer o commit da transação
        $conn->commit();
        // Definir a mensagem de sucesso
        $mensagem = "Bilhete comprado com sucesso!";
    } catch (Exception $e) {
        // Se ocorrer um erro, desfazer a transação
        $conn->rollback();
        // Definir a mensagem de erro
        $erro = true;
        // A mensagem de erro é a mensagem da exceção
        $mensagem = "Erro ao comprar bilhete: " . $e->getMessage();
    }
}
// Buscar dados da viagem para exibir
// Preparar a query para ir buscar os dados da viagem
$stmt = $conn->prepare("
    SELECT 
        v.id_viagem,
        v.data,
        v.hora,
        v.hora_chegada,
        v.preco,
        origem_loc.localidade AS origem,
        destino_loc.localidade AS destino
    FROM viagem v
    INNER JOIN rota r ON v.id_rota = r.id_rota
    INNER JOIN localidade origem_loc ON r.id_origem = origem_loc.id_localidade
    INNER JOIN localidade destino_loc ON r.id_destino = destino_loc.id_localidade
    WHERE v.id_viagem = ?
");
// Fazer o bind do parâmetro
$stmt->bind_param("i", $id_viagem);
// Executar a query
$stmt->execute();
// Obter o resultado
$result = $stmt->get_result();

// Verificar se a viagem foi encontrada
if ($result->num_rows === 0) {
    die("Viagem não encontrada.");
}

// Obter os dados da viagem
$viagem = $result->fetch_assoc();

// Buscar saldo atual
// Preparar a query para obter o saldo atual da carteira do utilizador
$stmt = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ?");
// Fazer o bind do parâmetro
$stmt->bind_param("i", $id_carteira);
// Executar a query para obter o saldo atual
$stmt->execute();
// Obter o resultado
$res = $stmt->get_result();
// Verificar se o saldo foi encontrado e obter o valor
$saldo_atual = $res->fetch_assoc()["saldo"] ?? 0;

// Fechar a conexão com a base de dados
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Bilhete</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <style>
        .container {
            margin-top: 60px;
        }

        .card {
            border-radius: 10px;
        }

        .btn-comprar {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
        }

        .btn-comprar:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body class="main-layout">
    <div class="container">
        <h1>Confirmação da Viagem</h1>

        <!-- Se existe mensagem, então apresenta ao utilizador (se for erro é danger se não é sucess) -->
        <?php if ($mensagem): ?>
            <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Resumo da Viagem</h4>
            </div>
            <!-- Apresenta os dados da viagem formatados -->
            <div class="card-body">
                <p><strong>Origem:</strong> <?= htmlspecialchars($viagem['origem']) ?></p>
                <p><strong>Destino:</strong> <?= htmlspecialchars($viagem['destino']) ?></p>
                <p><strong>Data:</strong> <?= htmlspecialchars($viagem['data']) ?></p>
                <p><strong>Hora de partida:</strong> <?= (new DateTime($viagem['hora']))->format("H:i") ?></p>
                <p><strong>Hora de chegada:</strong> <?= (new DateTime($viagem['hora_chegada']))->format("H:i") ?></p>
                <p><strong>Preço:</strong> <?= number_format($viagem['preco'], 2, ',', '.') ?> €</p>
                <p><strong>Saldo atual:</strong> <?= number_format($saldo_atual, 2, ',', '.') ?> €</p>

                <!-- Se não existe mensagem, então apresenta ao utilizador o botão -->
                <?php if (!$erro): ?>
                    <form method="POST">
                        <button type="submit" name="confirmar_compra" class="btn btn-comprar">Comprar Bilhete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px;">
        <a href="viagens.php">Voltar</a>
    </div>
</body>
</html>