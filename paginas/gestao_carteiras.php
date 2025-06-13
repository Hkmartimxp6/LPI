<?php
session_start();
include "../basedados/basedados.h";
include "utilizadores.php"; // Assuming this defines FUNCIONARIO and ADMINISTRADOR
include "carteira_funcoes.php"; // Assuming this defines ADICIONAR_SALDO, RETIRAR_SALDO, getSaldoAtual, adicionarSaldo, retirarSaldo

// Verificar permissões (funcionário ou admin)
if (
    !isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO &&
        $_SESSION['utilizador']['tipo_utilizador'] != ADMINISTRADOR)
) {
    die("Acesso reservado a funcionários e administradores.");
}

$mensagem = "";
$erro = false;
$saldo_atual_cliente = null;
$id_cliente_selecionado = null; // To keep track of the selected client across requests

// Listar clientes
$resultado_clientes = $conn->query("SELECT id_utilizador, nome_utilizador
                                     FROM utilizador
                                     WHERE tipo_utilizador = " . CLIENTE);

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form is for managing balance (add/retire)
    if (isset($_POST['cliente'], $_POST['valor'], $_POST['tipo_movimento'])) {
        $id_cliente_selecionado = intval($_POST['cliente']); // The client from the transaction form
        $valor = $_POST['valor'];
        $tipo_movimento = $_POST['tipo_movimento'];

        // Obter id_carteira do cliente
        $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
        $stmt->bind_param("i", $id_cliente_selecionado);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $erro = true;
            $mensagem = "Cliente não encontrado.";
        } else {
            $id_carteira = $res->fetch_assoc()["id_carteira"];

            if ($tipo_movimento === "adicionar") {
                $resultado = adicionarSaldo($conn, $id_carteira, $valor, ADICIONAR_SALDO);
            } elseif ($tipo_movimento === "retirar") {
                $resultado = retirarSaldo($conn, $id_carteira, $valor, RETIRAR_SALDO);
            } else {
                $resultado = ['success' => false, 'message' => "Tipo de movimento inválido."];
            }

            $erro = !$resultado['success'];
            $mensagem = $resultado['message'];
        }
    } elseif (isset($_POST['selecionar_cliente'])) {
        // This block handles the submission of the client selection form
        $id_cliente_selecionado = intval($_POST['selecionar_cliente']);
    }
}

// --- Fetch and Display Current Balance if a Client is Selected ---
if ($id_cliente_selecionado) {
    $stmt_saldo = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    if ($stmt_saldo) {
        $stmt_saldo->bind_param("i", $id_cliente_selecionado);
        $stmt_saldo->execute();
        $res_saldo = $stmt_saldo->get_result();

        if ($res_saldo->num_rows > 0) {
            $id_carteira_cliente = $res_saldo->fetch_assoc()["id_carteira"];
            $saldo_atual_cliente = getSaldoAtual($conn, $id_carteira_cliente);
        }
        $stmt_saldo->close();
    } else {
        $erro = true;
        $mensagem = "Erro ao preparar a consulta para obter o ID da carteira: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Saldos</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-5">
    <h2>Gestão de Saldos de Clientes</h2>

    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="selecionar_cliente" class="form-label">Cliente:</label>
            <select name="selecionar_cliente" id="selecionar_cliente" class="form-select" onchange="this.form.submit();" required>
                <option value="">-- Selecionar Cliente --</option>
                <?php
                // Rewind the result set for re-iteration if needed
                if ($resultado_clientes->num_rows > 0) {
                    $resultado_clientes->data_seek(0);
                }
                while ($cli = $resultado_clientes->fetch_assoc()): ?>
                    <option value="<?= $cli['id_utilizador'] ?>" <?= ($id_cliente_selecionado == $cli['id_utilizador']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cli['nome_utilizador']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>

    <?php if ($saldo_atual_cliente !== null): ?>
        <div class="mb-3">
            <p>Saldo Atual do Cliente Selecionado: <strong><?= number_format($saldo_atual_cliente, 2, ',', '.') ?> €</strong></p>
        </div>
    <?php endif; ?>

    <?php if ($id_cliente_selecionado): ?>
        <form method="POST" class="mt-4">
            <input type="hidden" name="cliente" value="<?= $id_cliente_selecionado ?>">

            <div class="mb-3">
                <label for="valor" class="form-label">Valor (€):</label>
                <input type="number" name="valor" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de movimento:</label><br>
                <input type="radio" name="tipo_movimento" value="adicionar" required> Adicionar saldo
                <input type="radio" name="tipo_movimento" value="retirar" required class="ms-3"> Retirar saldo
            </div>

            <button type="submit" class="btn btn-primary">Confirmar</button>
        </form>
    <?php else: ?>
        <p class="mt-4 alert alert-info">Por favor, selecione um cliente para gerir o saldo.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>

</html>