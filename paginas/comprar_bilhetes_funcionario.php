<?php
// Inicia a sessão PHP
session_start();

include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Verifica se o utilizador é um funcionário e está autenticado
if (!isset($_SESSION['utilizador']) || $_SESSION['utilizador']['tipo_utilizador'] != 2) {
    die("Acesso restrito a funcionários.");
}

// Mensagem de feedback
$mensagem = "";
// Erro de compra
$erro = false;

// Fazer a query para obter os clientes (tipo_utilizador = 3 - Cliente)
$resultado_clientes = $conn->query("SELECT id_utilizador, nome_utilizador FROM utilizador WHERE tipo_utilizador = 3");

// Fazer a query para obter as viagens disponíveis
$resultado_viagens = $conn->query("
    SELECT v.id_viagem, v.data, v.hora, l1.localidade AS origem, l2.localidade AS destino, v.preco
    FROM viagem v
    INNER JOIN rota r ON v.id_rota = r.id_rota
    INNER JOIN localidade l1 ON r.id_origem = l1.id_localidade
    INNER JOIN localidade l2 ON r.id_destino = l2.id_localidade
    ORDER BY v.data ASC
");

// Verifica o método do form e se os campos necessários estão definidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente']) && isset($_POST['viagem'])) {
    // Obter o id do cliente
    $id_cliente = $_POST['cliente'];
    // e o id da viagem
    $id_viagem = $_POST['viagem'];

    // Obter carteira do cliente
    // Preparar a query para obter o id_carteira do cliente
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    // Fazer o bind do parâmetro
    $stmt->bind_param("i", $id_cliente);
    // Executar a query
    $stmt->execute();
    // Obter o resultado
    $res = $stmt->get_result();
    // Verificar se o cliente tem carteira associada
    if ($res->num_rows === 0) {
        // Se não encontrar o cliente, ativa a flag de erro
        $erro = true;
        // Define a mensagem de erro
        $mensagem = "Cliente não encontrado.";
    } else {
        // Obter o id_carteira do cliente
        $id_carteira_cliente = $res->fetch_assoc()["id_carteira"];

        // Iniciar transação para garantir que apenas é feita a compra depois do commit
        $conn->begin_transaction();
        try {
            // Obter dados da viagem
            // Preparar a query para obter os dados da viagem
            $stmt = $conn->prepare("SELECT preco, id_autocarro FROM viagem WHERE id_viagem = ?");
            // Fazer o bind do parâmetro
            $stmt->bind_param("i", $id_viagem);
            // Executar a query
            $stmt->execute();
            // Obter o resultado
            $res = $stmt->get_result();
            // Verificar se foram encontrados os dados da viagem
            if ($res->num_rows === 0) {
                throw new Exception("Viagem não encontrada.");
            }
            // Obter os dados da viagem
            $dados_viagem = $res->fetch_assoc();
            // Associar o valor da viagem
            $preco = $dados_viagem['preco'];

            // Verificar lugares ocupados
            // Preparar a query para contar os lugares ocupados
            $stmt = $conn->prepare("SELECT COUNT(*) AS ocupados FROM bilhete WHERE id_viagem = ?");
            // Fazer o bind do parâmetro
            $stmt->bind_param("i", $id_viagem);
            // Executar a query
            $stmt->execute();
            // Obter o número de lugares ocupados
            $ocupados = $stmt->get_result()->fetch_assoc()["ocupados"];

            // Preparar a query para obter o número total de lugares do autocarro
            $stmt = $conn->prepare("SELECT lugares FROM autocarro WHERE id_autocarro = ?");
            // Fazer o bind do parâmetro
            $stmt->bind_param("i", $dados_viagem['id_autocarro']);
            // Executar a query
            $stmt->execute();
            // Obter o número total de lugares
            $lugares = $stmt->get_result()->fetch_assoc()["lugares"];

            // Verificar se há lugares disponíveis
            if ($ocupados >= $lugares) {
                throw new Exception("Não há lugares disponíveis para esta viagem.");
            }

            // Retirar saldo do cliente
            $resultado_retirar = retirarSaldo($conn, $id_carteira_cliente, $preco, COMPRAR_BILHETE);
            // Verificar se a retirada foi bem-sucedida
            if (!$resultado_retirar['success']) {
                throw new Exception($resultado_retirar['message']);
            }

            // Adicionar à empresa (carteira 1)
            $resultado_adicionar = adicionarSaldo($conn, 1, $preco, VENDER_BILHETE);

            // Verificar se a adição foi bem-sucedida
            if (!$resultado_adicionar['success']){ 
                throw new Exception($resultado_adicionar['message']);
            }

            // Inserir bilhete
            // Gera um identificador único para o bilhete (do tipo BILHETE_1a3b5c7d9...)
            $identificador = uniqid("BILHETE_");
            // Preparar a query para inserir o bilhete
            $stmt = $conn->prepare("INSERT INTO bilhete (id_utilizador, id_viagem, data_compra, identificador) VALUES (?, ?, NOW(), ?)");
            // Fazer o bind dos parâmetros
            $stmt->bind_param("iis", $id_cliente, $id_viagem, $identificador);
            // Executar a query para inserir o bilhete
            $stmt->execute();

            // Fazer o commit da transação
            $conn->commit();
            // Definir a mensagem de sucesso
            $mensagem = "Bilhete comprado com sucesso para o cliente.";
        } catch (Exception $e) {
            // Se ocorrer um erro, desfaz a transação
            $conn->rollback();
            // Define a mensagem de erro
            $erro = true;
            // A mensagem de erro é a mensagem da exceção
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

    <!-- Exibir mensagem de feedback se esta existir (danger se for erro, sucess se não for) -->
    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="cliente" class="form-label">Cliente:</label>
            <select name="cliente" id="cliente" class="form-select" required>
                <option value="">-- Escolher Cliente --</option>
                <!-- Preencher o select com os clientes obtidos -->
                <?php while ($cliente = $resultado_clientes->fetch_assoc()): ?>
                    <option value="<?= $cliente['id_utilizador'] ?>"><?= htmlspecialchars($cliente['nome_utilizador']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="viagem" class="form-label">Viagem:</label>
            <select name="viagem" id="viagem" class="form-select" required>
                <option value="">-- Escolher Viagem --</option>
                <!-- Preencher o select com as viagens obtidas -->
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