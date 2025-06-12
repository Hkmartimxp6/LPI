<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

if (!isset($_GET['id_viagem'])) {
    die("ID da viagem não fornecido.");
}

if (!isset($_SESSION["utilizador"])) {
    header("Location: index.php?mensagem=É necessário iniciar sessão para comprar bilhetes.");
    exit();
}

$id_viagem = intval($_GET['id_viagem']);
$id_utilizador = $_SESSION["utilizador"]["id_utilizador"] ?? null;
$mensagem = "";
$erro = false;

$conn->begin_transaction();

try {
    // Obter preço e dados da viagem
    $stmt = $conn->prepare("SELECT v.preco, v.id_autocarro, a.lugares 
                            FROM viagem v 
                            INNER JOIN autocarro a ON v.id_autocarro = a.id_autocarro 
                            WHERE v.id_viagem = ?");
    $stmt->bind_param("i", $id_viagem);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Viagem não encontrada.");
    }

    $viagemData = $res->fetch_assoc();
    $preco = $viagemData["preco"];
    $lugares_disponiveis = $viagemData["lugares"];

    // Verificar lugares já ocupados
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bilhete WHERE id_viagem = ?");
    $stmt->bind_param("i", $id_viagem);
    $stmt->execute();
    $ocupados = $stmt->get_result()->fetch_assoc()["total"];

    if ($ocupados >= $lugares_disponiveis) {
        throw new Exception("A viagem está lotada.");
    }

    // Obter id_carteira do utilizador
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Utilizador não tem carteira associada.");
    }

    $id_carteira = $res->fetch_assoc()["id_carteira"];

    // Obter saldo da carteira
    $stmt = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ?");
    $stmt->bind_param("i", $id_carteira);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Carteira não encontrada.");
    }

    $saldo = $res->fetch_assoc()["saldo"];

    if ($saldo < $preco) {
        throw new Exception("Saldo insuficiente.");
    }

    // Deduzir saldo
    $stmt = $conn->prepare("UPDATE carteira SET saldo = saldo - ? WHERE id_carteira = ?");
    $stmt->bind_param("di", $preco, $id_carteira);
    $stmt->execute();

    // Inserir registo em carteira_log
    $id_operacao = 2; // Compra de bilhete
    $montante = -1 * $preco;
    $stmt = $conn->prepare("INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("iid", $id_carteira, $id_operacao, $montante);
    $stmt->execute();

    // Inserir bilhete
    $identificador = uniqid("BILHETE_");
    $stmt = $conn->prepare("INSERT INTO bilhete (id_utilizador, id_viagem, data_compra, identificador) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("iis", $id_utilizador, $id_viagem, $identificador);
    $stmt->execute();

    $conn->commit();
    $mensagem = "Bilhete comprado com sucesso!";
} catch (Exception $e) {
    $conn->rollback();
    $mensagem = "Erro ao comprar bilhete: " . $e->getMessage();
    $erro = true;
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
        .container { margin-top: 60px; }
        .card { border-radius: 10px; }
        .btn-comprar {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
        }
        .btn-comprar:hover { background-color: #45a049; }
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
</body>
</html>

