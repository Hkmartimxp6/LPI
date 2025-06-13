<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

$loggedIn = false;

if (!isset($_SESSION["utilizador"])) {
    die("Acesso negado. Sessão não iniciada.");
}

$utilizador = $_SESSION["utilizador"];
if (!isset($utilizador["tipo_utilizador"]) || $utilizador["tipo_utilizador"] != ADMINISTRADOR) {
    die("Acesso reservado a administradores.");
    header("Location: index.php");
    exit();
}

$loggedIn = true;

if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}

// Criar rota
if (isset($_POST['criar'])) {
    $origem = $_POST['origem'];
    $destino = $_POST['destino'];

    if ($origem == $destino) {
        echo "<p style='color:red;'>Erro: A origem e o destino não podem ser iguais.</p>";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM rota WHERE id_origem = ? AND id_destino = ?");
        $stmt->bind_param("ii", $origem, $destino);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();

        if ($existe > 0) {
            echo "<p style='color:red;'>Erro: Esta rota já existe.</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO rota (id_origem, id_destino, estado) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $origem, $destino);
            $stmt->execute();
            echo "<p style='color:green;'>Rota criada com sucesso.</p>";
        }
    }
}


// Atualizar rota
if (isset($_POST['atualizar'])) {
    $id_rota = $_POST['id_rota'];
    $origem = $_POST['origem'];
    $destino = $_POST['destino'];
    $stmt = $conn->prepare("UPDATE rota SET id_origem = ?, id_destino = ? WHERE id_rota = ?");
    $stmt->bind_param("iii", $origem, $destino, $id_rota);
    $stmt->execute();
}

// Anular rota (estado = 0)
if (isset($_POST['anular'])) {
    $id_rota = $_POST['id_rota'];
    $stmt = $conn->prepare("UPDATE rota SET estado = 0 WHERE id_rota = ?");
    $stmt->bind_param("i", $id_rota);
    $stmt->execute();
}

// Reativar rota (estado = 1)
if (isset($_POST['reativar'])) {
    $id_rota = $_POST['id_rota'];
    $stmt = $conn->prepare("UPDATE rota SET estado = 1 WHERE id_rota = ?");
    $stmt->bind_param("i", $id_rota);
    $stmt->execute();
}

// Obter localidades
$localidades = $conn->query("SELECT * FROM localidade ORDER BY localidade");

// Filtro e ordenação
$filtro = $_GET['filtro'] ?? 'todas';
$ordem = $_GET['ordem'] ?? 'asc';
$sql = "SELECT r.id_rota, o.localidade AS origem, d.localidade AS destino, r.estado
        FROM rota r
        JOIN localidade o ON r.id_origem = o.id_localidade
        JOIN localidade d ON r.id_destino = d.id_localidade";
if ($filtro != 'todas') {
    $sql .= " WHERE o.localidade = '$filtro'";
}
$sql .= " ORDER BY o.localidade $ordem";
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
        <select name="origem"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>

        <label>Destino:</label>
        <select name="destino"><?php foreach ($localidades as $loc) echo "<option value='{$loc['id_localidade']}'>{$loc['localidade']}</option>"; ?></select>

        <input type="submit" name="criar" value="Criar Rota">
    </form>

    <h3>Atualizar / Anular / Reativar Rota</h3>
    <form method="post">
        <label>ID da Rota:</label><input type="number" name="id_rota" required>
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
            $localidades->data_seek(0); // reset pointer
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