<?php
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

session_start();

if (!isset($_GET['id_viagem'])) {
    die("ID da viagem não fornecido.");
}

if (!isset($_SESSION["utilizador"])) {
    header("Location: index.php?mensagem=É necessário iniciar sessão para comprar bilhetes.");
    exit();
}

$id_viagem = $_GET['id_viagem'];
$id_utilizador = $_SESSION["utilizador"]["id_utilizador"] ?? null;
$mensagem = "";
$erro = false;

// Obter id_carteira do utilizador (necessário para ver saldo)
$stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Utilizador não tem carteira associada.");
}

$id_carteira = $res->fetch_assoc()["id_carteira"];


if (isset($_POST['confirmar_compra'])) {
    $conn->begin_transaction();

    try {
        // Obter dados da viagem
        $stmt = $conn->prepare("SELECT v.preco, v.id_autocarro, a.lugares 
                                FROM viagem v 
                                INNER JOIN autocarro a ON v.id_autocarro = a.id_autocarro 
                                WHERE v.id_viagem = ?");
        $stmt->bind_param("i", $id_viagem);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            throw new Exception("Viagem não encontrada.");
        }

        $dados_viagem = $resultado->fetch_assoc();
        $valor_viagem = $dados_viagem["preco"];
        $lugares_totais = $dados_viagem["lugares"];

        // Verificar lugares ocupados
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bilhete WHERE id_viagem = ?");
        $stmt->bind_param("i", $id_viagem);
        $stmt->execute();
        $lugares_ocupados = $stmt->get_result()->fetch_assoc()["total"];

        if ($lugares_ocupados >= $lugares_totais) {
            throw new Exception("A viagem está cheia.");
        }

        // Retirar saldo ao cliente (carteira do utilizador)
        $resultado_retirar = retirarSaldo($conn, $id_carteira, $valor_viagem, COMPRAR_BILHETE);
        if (!$resultado_retirar['success']) {
            throw new Exception($resultado_retirar['message']);
        }

        // Adicionar saldo à empresa (carteira 1)
        $resultado_adicionar = adicionarSaldo($conn, 1, $valor_viagem, VENDER_BILHETE);
        if (!$resultado_adicionar['success']) {
            throw new Exception($resultado_adicionar['message']);
        }

        // Inserir bilhete
        $identificador = uniqid("BILHETE_");
        $stmt = $conn->prepare("INSERT INTO bilhete (id_utilizador, id_viagem, data_compra, identificador) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("iis", $id_utilizador, $id_viagem, $identificador);
        $stmt->execute();

        $conn->commit();
        $mensagem = "Bilhete comprado com sucesso!";
    } catch (Exception $e) {
        $conn->rollback();
        $erro = true;
        $mensagem = "Erro ao comprar bilhete: " . $e->getMessage();
    }
}



// Buscar dados da viagem para exibir
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
$stmt->bind_param("i", $id_viagem);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Viagem não encontrada.");
}

$viagem = $result->fetch_assoc();

// Buscar saldo atual
$stmt = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ?");
$stmt->bind_param("i", $id_carteira);
$stmt->execute();
$res = $stmt->get_result();
$saldo_atual = $res->fetch_assoc()["saldo"] ?? 0;

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

        <?php if ($mensagem): ?>
            <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Resumo da Viagem</h4>
            </div>
            <div class="card-body">
                <p><strong>Origem:</strong> <?= htmlspecialchars($viagem['origem']) ?></p>
                <p><strong>Destino:</strong> <?= htmlspecialchars($viagem['destino']) ?></p>
                <p><strong>Data:</strong> <?= htmlspecialchars($viagem['data']) ?></p>
                <p><strong>Hora de partida:</strong> <?= (new DateTime($viagem['hora']))->format("H:i") ?></p>
                <p><strong>Hora de chegada:</strong> <?= (new DateTime($viagem['hora_chegada']))->format("H:i") ?></p>
                <p><strong>Preço:</strong> <?= number_format($viagem['preco'], 2, ',', '.') ?> €</p>
                <p><strong>Saldo atual:</strong> <?= number_format($saldo_atual, 2, ',', '.') ?> €</p>

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