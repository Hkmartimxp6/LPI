<?php
include "../basedados/basedados.h";
include "utilizadores.php";

// Inicializar a sessão PHP
session_start();

// Variável para verificar se o utilizador está autenticado
$loggedIn = false;

// Verificar se o utilizador está autenticado
if (!isset($_SESSION["utilizador"])) {
    die("Acesso negado. Sessão não iniciada.");
}

// Atribuir o utilizador da sessão
$utilizador = $_SESSION["utilizador"];

// Verificar se o utilizador é do tipo ADMINISTRADOR
if (!isset($utilizador["tipo_utilizador"]) || $utilizador["tipo_utilizador"] != ADMINISTRADOR) {
    die("Acesso reservado a administradores.");
    header("Location: index.php");
    exit();
}

// Se chegou aqui, o utilizador está autenticado e é um administrador
$loggedIn = true;

// Mensagem de feedback se houver erro ao conectar à base de dados
if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}

// Criar rota 
// Verificar se o formulário de criação de rota foi submetido
if (isset($_POST['criar'])) {
    // Verificar se os campos de origem e destino foram preenchidos
    $origem = $_POST['origem'];
    $destino = $_POST['destino'];
    // Verificar se a origem e o destino são válidos
    if ($origem == $destino) {
        echo "<p style='color:red;'>Erro: A origem e o destino não podem ser iguais.</p>";
    } else {
        // Preparar a consulta para verificar se a rota já existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM rota WHERE id_origem = ? AND id_destino = ?");
        // Fazer o bind dos parâmetros
        $stmt->bind_param("ii", $origem, $destino);
        // Executar a consulta
        $stmt->execute();
        // Obter o resultado
        $stmt->bind_result($existe);
        // Buscar o resultado
        $stmt->fetch();
        // Fechar a declaração
        $stmt->close();

        // Verificar se a rota já existe
        if ($existe > 0) {
            echo "<p style='color:red;'>Erro: Esta rota já existe.</p>";
        } else {
            // Preparar a consulta para inserir a nova rota
            $stmt = $conn->prepare("INSERT INTO rota (id_origem, id_destino, estado) VALUES (?, ?, 1)");
            // Fazer o bind dos parâmetros
            $stmt->bind_param("ii", $origem, $destino);
            // Executar a consulta
            $stmt->execute();
            echo "<p style='color:green;'>Rota criada com sucesso.</p>";
        }
    }
}

// Atualizar rota
// Verificar se o formulário de atualização de rota foi submetido
if (isset($_POST['atualizar'])) {
    // Obter os dados do formulário
    $id_rota = $_POST['id_rota'];
    $origem = $_POST['origem'];
    $destino = $_POST['destino'];
    // Preparar a consulta para atualizar a rota
    $stmt = $conn->prepare("UPDATE rota SET id_origem = ?, id_destino = ? WHERE id_rota = ?");
    // Fazer o bind dos parâmetros
    $stmt->bind_param("iii", $origem, $destino, $id_rota);
    // Executar a consulta
    $stmt->execute();
}

// Anular rota (estado = 0)
// Verificar se o formulário de anulação de rota foi submetido
if (isset($_POST['anular'])) {
    // Obter o ID da rota a anular
    $id_rota = $_POST['id_rota'];
    // Preparar a consulta para anular a rota
    $stmt = $conn->prepare("UPDATE rota SET estado = 0 WHERE id_rota = ?");
    // Fazer o bind dos parâmetros
    $stmt->bind_param("i", $id_rota);
    // Executar a consulta
    $stmt->execute();
}

// Reativar rota (estado = 1)
// Verificar se o formulário de reativação de rota foi submetido
if (isset($_POST['reativar'])) {
    // Obter o ID da rota a reativar
    $id_rota = $_POST['id_rota'];
    // Preparar a consulta para reativar a rota
    $stmt = $conn->prepare("UPDATE rota SET estado = 1 WHERE id_rota = ?");
    // Fazer o bind dos parâmetros
    $stmt->bind_param("i", $id_rota);
    // Executar a consulta
    $stmt->execute();
}

// Obter localidades
$localidades = $conn->query("SELECT * FROM localidade ORDER BY localidade");

// Filtro e ordenação
// Verificar se o filtro e a ordem foram definidos, caso contrário, usar valores padrão
$filtro = $_GET['filtro'] ?? 'todas';
$ordem = $_GET['ordem'] ?? 'asc';

// Preparar a consulta SQL para obter as rotas
$sql = "SELECT r.id_rota, o.localidade AS origem, d.localidade AS destino, r.estado
        FROM rota r
        JOIN localidade o ON r.id_origem = o.id_localidade
        JOIN localidade d ON r.id_destino = d.id_localidade";
// Adicionar filtro se necessário
if ($filtro != 'todas') {
    $sql .= " WHERE o.localidade = '$filtro'";
}
// Adicionar ordenação
$sql .= " ORDER BY o.localidade $ordem";
// Executar a consulta para obter as rotas
$rotas = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Gestão de Rotas</title>
</head>

<body>
    <h2>Gestão de Rotas (Admin)</h2>

    <form method="post">
        <label>Origem:</label>
        <!-- Preencher o select com as localidades de Origem com um ciclo foreach -->
        <select name="origem"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>

        <label>Destino:</label>
        <!-- Preencher o select com as localidades Destino com um ciclo foreach -->
        <select name="destino"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>

        <input type="submit" name="criar" value="Criar Rota">
    </form>

    <h3>Atualizar / Anular / Reativar Rota</h3>
    <form method="post">
        <label>ID da Rota:</label><input type="number" name="id_rota" required>
        <!-- Preencher o select com as localidades de Origem e Destino novos com um ciclo foreach -->
        <label>Nova Origem:</label><select name="origem"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>
        <label>Novo Destino:</label><select name="destino"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>
        <input type="submit" name="atualizar" value="Atualizar">
        <input type="submit" name="anular" value="Anular (Desativar)">
        <input type="submit" name="reativar" value="Reativar">
    </form>

    <h3>Consulta de Rotas</h3>
    <form method="get">
        <label>Filtrar por origem:</label>
        <select name="filtro">
            <option value="todas">Todas</option>
            <?php
            // Reposicionar o ponteiro do resultado para o início
            $localidades->data_seek(0);
            // Preencher o select com as localidades para filtro
            foreach ($localidades as $loc) echo "<option value='{$loc['localidade']}'>{$loc['localidade']}</option>";
            ?>
        </select>
        <label>Ordenar:</label>
        <select name="ordem">
            <option value="asc">Ascendente</option>
            <option value="desc">Descendente</option>
        </select>
        <input type="submit" value="Aplicar">
    </form>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Estado</th>
        </tr>
        <!-- Exibir as rotas obtidas da base de dados -->
        <?php foreach ($rotas as $rota): ?>
            <tr>
                <td><?= $rota['id_rota'] ?></td>
                <td><?= $rota['origem'] ?></td>
                <td><?= $rota['destino'] ?></td>
                <td><?= $rota['estado'] ? "Ativa" : "Inativa" ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>

</html>