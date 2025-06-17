<?php
session_start();

include "../basedados/basedados.h";
include "carteira_funcoes.php";
include "utilizadores.php";

// Verificar se o utilizador é FUNCIONARIO ou ADMINISTRADOR
if (!isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO && 
     $_SESSION['utilizador']['tipo_utilizador'] != ADMINISTRADOR)) {

    die("Acesso reservado apenas a funcionários.");
}
// Variáveis de feedback 
$erro = false;
$mensagem = "";

// ID do cliente selecionado via GET
$id_cliente = isset($_GET['cliente']) ? intval($_GET['cliente']) : null;

// Obter lista de clientes para o dropdown
$clientes_stmt = $conn->query("SELECT id_utilizador, nome_utilizador FROM utilizador WHERE tipo_utilizador = " . CLIENTE);
$clientes = $clientes_stmt->fetch_all(MYSQLI_ASSOC);

// Anular bilhete
if (isset($_GET['anular']) && $id_cliente) {
    
    $id_bilhete = $_GET['anular'];

    // Começa a transação
    $conn->begin_transaction();

    try {
        // Obter id_carteira do cliente
        $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();

        // Verificar se o cliente existe
        if ($res->num_rows === 0) {
            throw new Exception("Cliente não encontrado.");
        }

        // Obter id_carteira do cliente
        $cliente_data = $res->fetch_assoc();
        $id_carteira = $cliente_data['id_carteira'];

        // Obter bilhete e preço
        $stmt = $conn->prepare("SELECT b.id_viagem, v.preco 
                                FROM bilhete b 
                                JOIN viagem v ON b.id_viagem = v.id_viagem 
                                WHERE b.id_bilhete = ? AND b.id_utilizador = ?");
        $stmt->bind_param("ii", $id_bilhete, $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();

        // Verificar se o bilhete existe e pertence ao cliente
        if ($res->num_rows === 0) {
            throw new Exception("Bilhete não encontrado.");
        }

        // Obter dados da viagem e preço
        $viagem = $res->fetch_assoc();
        $preco = $viagem['preco'];

        // Eliminar bilhete
        $stmt = $conn->prepare("DELETE FROM bilhete WHERE id_bilhete = ?");
        $stmt->bind_param("i", $id_bilhete);
        $stmt->execute();

        // Reembolsar cliente
        $res_cliente = adicionarSaldo($conn, $id_carteira, $preco, VENDER_BILHETE);
        if (!$res_cliente['success']) {
            throw new Exception($res_cliente['message']);
        }

        // Retirar à empresa
        $res_empresa = retirarSaldo($conn, 1, $preco, COMPRAR_BILHETE);
        if (!$res_empresa['success']) {
            throw new Exception($res_empresa['message']);
        }

        // Commit da transação de reembolso
        $conn->commit();
        $mensagem = "Bilhete anulado com sucesso e valor reembolsado ao cliente.";
    } catch (Exception $e) {
        $conn->rollback();
        $erro = true;
        $mensagem = "Erro ao anular bilhete: " . $e->getMessage();
    }
}

// Se foi selecionado um cliente, obter os seus bilhetes e saldo
$bilhetes = [];
$saldo_atual = null;

// Verifica se o ID do cliente foi fornecido
if ($id_cliente) {
    // Preparar a consulta para obter a carteira e depois o saldo atual do cliente
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $res = $stmt->get_result();

    // Se conseguiu obter o id_carteira, guarda o saldo atual
    if ($res->num_rows > 0) {
        $id_carteira = $res->fetch_assoc()['id_carteira'];
        $saldo_atual = getSaldoAtual($conn, $id_carteira);
    }

    // Query para obter os bilhetes do cliente selecionado
    $stmt = $conn->prepare("
        SELECT b.id_bilhete, b.data_compra, b.identificador,
               v.data, v.hora, v.hora_chegada, v.preco,
               lo.localidade AS origem, ld.localidade AS destino
        FROM bilhete b
        JOIN viagem v ON b.id_viagem = v.id_viagem
        JOIN rota r ON v.id_rota = r.id_rota
        JOIN localidade lo ON r.id_origem = lo.id_localidade
        JOIN localidade ld ON r.id_destino = ld.id_localidade
        WHERE b.id_utilizador = ?
        ORDER BY b.data_compra DESC
    ");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $bilhetes = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Bilhetes de Clientes</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Gestão de Bilhetes de Clientes</h2>

    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="get" class="mb-3">
        <label for="cliente">Selecionar cliente:</label>
        <select name="cliente" id="cliente" class="form-control" onchange="this.form.submit()">
            <option value="">-- Escolher --</option>
            <!-- Loop pelos clientes para preencher o dropdown -->
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id_utilizador'] ?>" 
                               <?= ($id_cliente == $cliente['id_utilizador']) ? 'selected' : '' ?>>
                               <?= htmlspecialchars($cliente['nome_utilizador']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($id_cliente && $bilhetes): ?>
        <p><strong>Saldo atual do cliente:</strong> <?= number_format($saldo_atual, 2, ',', '.') ?> €</p>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Identificador</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Chegada</th>
                    <th>Preço</th>
                    <th>Comprado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop pelos bilhetes do cliente -->
                <?php while ($row = $bilhetes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['identificador']) ?></td>
                        <td><?= htmlspecialchars($row['origem']) ?></td>
                        <td><?= htmlspecialchars($row['destino']) ?></td>
                        <td><?= htmlspecialchars($row['data']) ?></td>
                        <td><?= htmlspecialchars((new DateTime($row['hora']))->format("H:i")) ?></td>
                        <td><?= htmlspecialchars((new DateTime($row['hora_chegada']))->format("H:i")) ?></td>
                        <td><?= number_format($row['preco'], 2, ',', '.') ?> €</td>
                        <td><?= (new DateTime($row['data_compra']))->format("d/m/Y H:i") ?></td>
                        <td><a href="?cliente= <?= $id_cliente ?>
                                     &anular=<?= $row['id_bilhete'] ?>" 
                               class="btn btn-sm btn-danger">Anular</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <!-- Se não houver bilhetes -->
    <?php elseif ($id_cliente): ?>
        <p>Este cliente não tem bilhetes.</p>
    <?php endif; ?>
    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>
</html>
