<?php
session_start();

include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Verifica se o utilizador é FUNCIONARIO ou ADMINISTRADOR
if (!isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO && 
     $_SESSION['utilizador']['tipo_utilizador'] != ADMINISTRADOR)) {

    die("Acesso reservado apenas a funcionários.");
}

// Mensagens de feedback
$mensagem = "";
// Variável para indicar se ocorreu um erro
$erro = false;

// Obter lista de clientes (tipo 3 = cliente)
$resultado_clientes = $conn->query("SELECT id_utilizador, nome_utilizador FROM utilizador WHERE tipo_utilizador = 3");

// Resultado da query para obter rotas
$resultado_rotas = $conn->query("
    SELECT r.id_rota, l1.localidade AS origem, l2.localidade AS destino
    FROM rota r
    INNER JOIN localidade l1 ON r.id_origem = l1.id_localidade
    INNER JOIN localidade l2 ON r.id_destino = l2.id_localidade
");

// Verifica se foi selecionada uma rota
$id_rota = isset($_GET['rota']) ? (int)$_GET['rota'] : null;
// resultado das viagens para a rota selecionada
$resultado_viagens = null;

// Se uma rota foi selecionada, obter as viagens associadas com uma query preparada
if ($id_rota) {
    // Prepara a query para obter as viagens da rota selecionada
    $stmt_viagens = $conn->prepare("
        SELECT v.id_viagem, v.data, v.hora, l1.localidade AS origem, l2.localidade AS destino, v.preco
        FROM viagem v
        INNER JOIN rota r ON v.id_rota = r.id_rota
        INNER JOIN localidade l1 ON r.id_origem = l1.id_localidade
        INNER JOIN localidade l2 ON r.id_destino = l2.id_localidade
        WHERE v.id_rota = ?
        ORDER BY v.data ASC
    ");
    // Faz o bind do parâmetro da rota
    $stmt_viagens->bind_param("i", $id_rota);
    // Executa a query
    $stmt_viagens->execute();
    // Obtém o resultado
    $resultado_viagens = $stmt_viagens->get_result();
}

// Processamento da compra
// Verifica se o formulário foi submetido e se os campos necessários estão preenchidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente']) && isset($_POST['viagem'])) {
    // Verifica se o ID do cliente e da viagem foram enviados
    $id_cliente = $_POST['cliente'];
    $id_viagem = $_POST['viagem'];

    // Obtém o id_carteira do cliente
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    // Faz o bind do parâmetro do ID do cliente
    $stmt->bind_param("i", $id_cliente);
    // Executa a query
    $stmt->execute();
    // Obtém o resultado
    $res = $stmt->get_result();

    // Verifica se o cliente foi encontrado com o numero de linhas retornadas
    if ($res->num_rows === 0) {
        
        $erro = true;
        $mensagem = "Cliente não encontrado.";
    } else {
        $id_carteira_cliente = $res->fetch_assoc()["id_carteira"];
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("SELECT preco, id_autocarro FROM viagem WHERE id_viagem = ?");
            $stmt->bind_param("i", $id_viagem);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                throw new Exception("Viagem não encontrada.");
            }

            $dados_viagem = $res->fetch_assoc();
            $preco = $dados_viagem['preco'];

            $stmt = $conn->prepare("SELECT COUNT(*) AS ocupados FROM bilhete WHERE id_viagem = ?");
            $stmt->bind_param("i", $id_viagem);
            $stmt->execute();
            $ocupados = $stmt->get_result()->fetch_assoc()["ocupados"];

            $stmt = $conn->prepare("SELECT lugares FROM autocarro WHERE id_autocarro = ?");
            $stmt->bind_param("i", $dados_viagem['id_autocarro']);
            $stmt->execute();
            $lugares = $stmt->get_result()->fetch_assoc()["lugares"];

            if ($ocupados >= $lugares) {
                throw new Exception("Não há lugares disponíveis para esta viagem.");
            }

            $resultado_retirar = retirarSaldo($conn, $id_carteira_cliente, $preco, COMPRAR_BILHETE);
            if (!$resultado_retirar['success']) {
                throw new Exception($resultado_retirar['message']);
            }

            $resultado_adicionar = adicionarSaldo($conn, 1, $preco, VENDER_BILHETE);
            if (!$resultado_adicionar['success']) {
                throw new Exception($resultado_adicionar['message']);
            }

            $identificador = uniqid("BILHETE_");
            $stmt = $conn->prepare("INSERT INTO bilhete (id_utilizador, id_viagem, data_compra, identificador) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("iis", $id_cliente, $id_viagem, $identificador);
            $stmt->execute();

            $conn->commit();
            $mensagem = "Bilhete comprado com sucesso para o cliente.";
        } catch (Exception $e) {
            $conn->rollback();
            $erro = true;
            $mensagem = "Erro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Compra para Cliente</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-5">
    <h2>Compra de Bilhete para Cliente</h2>

    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <!-- Selecionar a rota -->
    <form method="get" class="mb-3">
        <label for="rota" class="form-label">Selecionar Rota:</label>
        <select name="rota" id="rota" class="form-select" onchange="this.form.submit()">
            <option value="">-- Escolher Rota --</option>
            <?php while ($rota = $resultado_rotas->fetch_assoc()): ?>
                <option value="<?= $rota['id_rota'] ?>" <?= ($id_rota == $rota['id_rota']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rota['origem'] . " → " . $rota['destino']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- Só mostrar o formulário de compra se uma rota estiver selecionada -->
    <?php if ($id_rota && $resultado_viagens && $resultado_viagens->num_rows > 0): ?>
        <form method="POST" class="mt-4">
            <input type="hidden" name="rota" value="<?= $id_rota ?>">

            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente:</label>
                <select name="cliente" id="cliente" class="form-select" required>
                    <option value="">-- Escolher Cliente --</option>
                    <?php while ($cliente = $resultado_clientes->fetch_assoc()): ?>
                        <option value="<?= $cliente['id_utilizador'] ?>">
                            <?= htmlspecialchars($cliente['nome_utilizador']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="viagem" class="form-label">Viagem:</label>
                <select name="viagem" id="viagem" class="form-select" required>
                    <option value="">-- Escolher Viagem --</option>
                    <?php while ($v = $resultado_viagens->fetch_assoc()): ?>
                        <option value="<?= $v['id_viagem'] ?>">
                            <?= htmlspecialchars($v['origem']) ?> → <?= htmlspecialchars($v['destino']) ?> |
                            <?= $v['data'] ?> <?= (new DateTime($v['hora']))->format('H:i') ?> -
                            <?= number_format($v['preco'], 2, ',', '.') ?> €
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Comprar Bilhete</button>
        </form>
    <?php elseif ($id_rota): ?>
        <div class="alert alert-warning">Não existem viagens disponíveis para esta rota.</div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>
</html>