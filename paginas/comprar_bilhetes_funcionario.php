<?php
session_start();
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

if (!isset($_SESSION['utilizador']) || $_SESSION['utilizador']['tipo_utilizador'] != 2) {
    die("Acesso restrito a funcionários.");
}

$mensagem = "";
$erro = false;

// Listar clientes
$resultado_clientes = $conn->query("SELECT id_utilizador, nome_utilizador FROM utilizador WHERE tipo_utilizador = 3");

// Listar viagens disponíveis
$resultado_viagens = $conn->query("
    SELECT v.id_viagem, v.data, v.hora, l1.localidade AS origem, l2.localidade AS destino, v.preco
    FROM viagem v
    INNER JOIN rota r ON v.id_rota = r.id_rota
    INNER JOIN localidade l1 ON r.id_origem = l1.id_localidade
    INNER JOIN localidade l2 ON r.id_destino = l2.id_localidade
    ORDER BY v.data ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente']) && isset($_POST['viagem'])) {
    $id_cliente = intval($_POST['cliente']);
    $id_viagem = intval($_POST['viagem']);

    // Obter carteira do cliente
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $erro = true;
        $mensagem = "Cliente não encontrado.";
    } else {
        $id_carteira_cliente = $res->fetch_assoc()["id_carteira"];

        $conn->begin_transaction();
        try {
            // Obter dados da viagem
            $stmt = $conn->prepare("SELECT preco, id_autocarro FROM viagem WHERE id_viagem = ?");
            $stmt->bind_param("i", $id_viagem);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) throw new Exception("Viagem não encontrada.");
            $dados_viagem = $res->fetch_assoc();
            $preco = $dados_viagem['preco'];

            // Verificar lugares ocupados
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

            // Retirar saldo do cliente
            $resultado_retirar = retirarSaldo($conn, $id_carteira_cliente, $preco, COMPRAR_BILHETE);
            if (!$resultado_retirar['success']) throw new Exception($resultado_retirar['message']);

            // Adicionar à empresa (carteira 1)
            $resultado_adicionar = adicionarSaldo($conn, 1, $preco, VENDER_BILHETE);
            if (!$resultado_adicionar['success']) throw new Exception($resultado_adicionar['message']);

            // Inserir bilhete
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

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="cliente" class="form-label">Cliente:</label>
            <select name="cliente" id="cliente" class="form-select" required>
                <option value="">-- Escolher Cliente --</option>
                <?php while ($cli = $resultado_clientes->fetch_assoc()): ?>
                    <option value="<?= $cli['id_utilizador'] ?>"><?= htmlspecialchars($cli['nome_utilizador']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="viagem" class="form-label">Viagem:</label>
            <select name="viagem" id="viagem" class="form-select" required>
                <option value="">-- Escolher Viagem --</option>
                <?php while ($v = $resultado_viagens->fetch_assoc()): ?>
                    <option value="<?= $v['id_viagem'] ?>">
                        <?= htmlspecialchars($v['origem']) ?> → <?= htmlspecialchars($v['destino']) ?> | <?= $v['data'] ?> <?= (new DateTime($v['hora']))->format('H:i') ?> - <?= number_format($v['preco'], 2, ',', '.') ?> €
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Comprar Bilhete</button>
    </form>
    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>

</html>