<?php
session_start();
include "../basedados/basedados.h";
include "carteira_funcoes.php";
include "utilizadores.php";

if (
    !isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != CLIENTE &&
     $_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO)
) {
    die("Acesso reservado a clientes ou funcionários.");
}


$id_utilizador = $_SESSION['utilizador']['id_utilizador'];
$mensagem = "";
$erro = false;

// Obter id_carteira e saldo
$id_carteira = $_SESSION['utilizador']['id_carteira'];
$saldo_atual = getSaldoAtual($conn, $id_carteira);

// Anular bilhete (eliminar da BD)
if (isset($_GET['anular'])) {
    $id_bilhete = intval($_GET['anular']);

    $conn->begin_transaction();
    try {
        // Obter info do bilhete
        $stmt = $conn->prepare("SELECT b.id_viagem, v.preco 
                        FROM bilhete b 
                        JOIN viagem v ON b.id_viagem = v.id_viagem 
                        WHERE b.id_bilhete = ? AND b.id_utilizador = ?");
        $stmt->bind_param("ii", $id_bilhete, $id_utilizador);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("Bilhete não encontrado.");
        }

        $viagem = $res->fetch_assoc();
        $preco = $viagem['preco'];


        // Remover bilhete
        $stmt = $conn->prepare("DELETE FROM bilhete WHERE id_bilhete = ?");
        $stmt->bind_param("i", $id_bilhete);
        $stmt->execute();

        // Reembolsar o cliente
        $resultado_cliente = adicionarSaldo($conn, $id_carteira, $preco, VENDER_BILHETE);
        if (!$resultado_cliente['success']) {
            throw new Exception($resultado_cliente['message']);
        }

        // Descontar à empresa
        $resultado_empresa = retirarSaldo($conn, 1, $preco, COMPRAR_BILHETE);
        if (!$resultado_empresa['success']) {
            throw new Exception("Falha ao subtrair valor da empresa: " . $resultado_empresa['message']);
        }

        $conn->commit();
        $mensagem = "Bilhete anulado e valor reembolsado com sucesso.";
        $saldo_atual = getSaldoAtual($conn, $id_carteira); // atualizar saldo
    } catch (Exception $e) {
        $conn->rollback();
        $erro = true;
        $mensagem = "Erro ao anular bilhete: " . $e->getMessage();
    }
}

// Consulta de bilhetes
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
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$bilhetes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Meus Bilhetes</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-4">
    <h2>Meus Bilhetes</h2>

    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <p><strong>Saldo atual:</strong> <?= number_format($saldo_atual, 2, ',', '.') ?> €</p>

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
                    <td><a href="?anular=<?= $row['id_bilhete'] ?>" class="btn btn-sm btn-danger">Anular</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>

</html>