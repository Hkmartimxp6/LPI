<?php

// Iniciar sessão PHP
session_start();

include "../basedados/basedados.h";
include "carteira_funcoes.php";
include "utilizadores.php";

// Verificar se o utilizador está autenticado e é do tipo CLIENTE ou FUNCIONARIO
if (
    !isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != CLIENTE &&
        $_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO)
) {
    die("Acesso reservado a clientes ou funcionários.");
}

// atribuir o id_utilizador da sessão
$id_utilizador = $_SESSION['utilizador']['id_utilizador'];
// Mensagem de feedback
$mensagem = "";
// Variável para indicar erro
$erro = false;

// Obter id_carteira e saldo
$id_carteira = $_SESSION['utilizador']['id_carteira'];
$saldo_atual = getSaldoAtual($conn, $id_carteira);

// Anular bilhete (eliminar da BD) se escolheu anular
if (isset($_GET['anular'])) {
    // Verificar se o id_bilhete é válido
    $id_bilhete = intval($_GET['anular']);

    // Começar transação
    $conn->begin_transaction();
    try {
        // Obter info do bilhete
        // Preparar a query para obter o id_viagem e o preço do bilhete
        $stmt = $conn->prepare("SELECT b.id_viagem, v.preco 
                        FROM bilhete b 
                        JOIN viagem v ON b.id_viagem = v.id_viagem 
                        WHERE b.id_bilhete = ? AND b.id_utilizador = ?");
        // Fazer o bind dos parâmetros
        $stmt->bind_param("ii", $id_bilhete, $id_utilizador);
        // Executar a query
        $stmt->execute();
        // Obter o resultado
        $res = $stmt->get_result();

        // Verificar se o bilhete existe
        if ($res->num_rows === 0) {
            throw new Exception("Bilhete não encontrado.");
        }

        // Obter os dados do bilhete
        $viagem = $res->fetch_assoc();
        // Obter o id_viagem e o preço
        $preco = $viagem['preco'];

        // Remover bilhete
        // Preparar a query para eliminar o bilhete
        $stmt = $conn->prepare("DELETE FROM bilhete WHERE id_bilhete = ?");
        // Fazer o bind do parâmetro
        $stmt->bind_param("i", $id_bilhete);
        // Executar a query
        $stmt->execute();

        // Reembolsar o cliente
        $resultado_cliente = adicionarSaldo($conn, $id_carteira, $preco, VENDER_BILHETE);
        // Verificar se o reembolso foi bem-sucedido
        if (!$resultado_cliente['success']) {
            throw new Exception($resultado_cliente['message']);
        }

        // Descontar à empresa
        $resultado_empresa = retirarSaldo($conn, 1, $preco, COMPRAR_BILHETE);
        // Verificar se o desconto foi bem-sucedido
        if (!$resultado_empresa['success']) {
            throw new Exception("Falha ao subtrair valor da empresa: " . $resultado_empresa['message']);
        }

        // Commit da transação
        $conn->commit();
        // Definir mensagem de sucesso
        $mensagem = "Bilhete anulado e valor reembolsado com sucesso.";
        // Atualizar o saldo atual
        $saldo_atual = getSaldoAtual($conn, $id_carteira); // atualizar saldo
    } catch (Exception $e) {
        // Rollback da transação em caso de erro
        $conn->rollback();
        // Definir mensagem de erro
        $erro = true;
        // Definir mensagem de erro com a mensagem da exceção
        $mensagem = "Erro ao anular bilhete: " . $e->getMessage();
    }
}

// Consulta de bilhetes
// Preparar a consulta SQL para obter os bilhetes do utilizador
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
// Fazer o bind do parâmetro
$stmt->bind_param("i", $id_utilizador);
// Executar a consulta
$stmt->execute();
// Obter o resultado da consulta
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

    <!-- Exibir mensagem de feedback (se for erro danger, se não sucess)-->
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
            <!-- Ciclo while que mostra a informação dos bilhetes -->
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