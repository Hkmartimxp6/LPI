<?php
session_start();
include "../basedados/basedados.h";

if (!isset($_SESSION['utilizador']) || $_SESSION['utilizador']['tipo_utilizador'] != 1) {
    die("Acesso restrito.");
}

// --- Criar utilizador ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar'])) {

    $stmt = $conn->prepare("INSERT INTO utilizador (nome_utilizador, password, tipo_utilizador, id_carteira, email) 
                            VALUES (?, md5(?), 3, ?, ?)");

    $stmt->bind_param("ssis", $_POST['user'], $_POST['pass'], $_POST['id_carteira'], $_POST['email']);

    $stmt->execute();

    $stmt->close();
}

// --- Validar / Anular ---
if (isset($_GET['anular'])) {
    $stmt = $conn->prepare("UPDATE utilizador SET tipo_utilizador = 5 WHERE id_utilizador = ?");
    $stmt->bind_param("i", $_GET['anular']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['validar'])) {
    $stmt = $conn->prepare("UPDATE utilizador SET tipo_utilizador = 3 WHERE id_utilizador = ?");
    $stmt->bind_param("i", $_GET['validar']);
    $stmt->execute();
    $stmt->close();
}

// --- Filtros e ordenação ---
$filtro_nome = isset($_GET['filtro']) ? "%" . $_GET['filtro'] . "%" : "%";

$filtro_tipo = isset($_GET['tipo']) && is_numeric($_GET['tipo']) ? intval($_GET['tipo']) : null;

$ordem = isset($_GET['ordem']) &&
    in_array($_GET['ordem'], ['nome_utilizador', 'tipo_utilizador']) ? $_GET['ordem'] : 'nome_utilizador';

$sentido = isset($_GET['sentido']) && $_GET['sentido'] === 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT * FROM utilizador WHERE nome_utilizador LIKE ?";
$params = [$filtro_nome];
$types = "s";

if ($filtro_tipo !== null) {
    $sql .= " AND tipo_utilizador = ?";
    $params[] = $filtro_tipo;
    $types .= "i";
}

$sql .= " ORDER BY $ordem $sentido";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>Gestão de Utilizadores</h2>

<!-- Criar novo -->
<form method="post">
    <h3>Criar novo utilizador</h3>
    Utilizador: <input type="text" name="user" required>
    Password: <input type="password" name="pass" required>
    Email: <input type="email" name="email" required>
    ID Carteira: <input type="number" name="id_carteira" required>
    <input type="submit" name="criar" value="Criar">
</form>

<hr>

<!-- Filtros e ordenação -->
<form method="get">
    Pesquisar nome: <input type="text" name="filtro" value="<?= htmlspecialchars($_GET['filtro'] ?? '') ?>">
    Tipo:
    <select name="tipo">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['tipo'] ?? '') == 1 ? 'selected' : '' ?>>Admin</option>
        <option value="2" <?= ($_GET['tipo'] ?? '') == 2 ? 'selected' : '' ?>>Funcionário</option>
        <option value="3" <?= ($_GET['tipo'] ?? '') == 3 ? 'selected' : '' ?>>Cliente</option>
        <option value="4" <?= ($_GET['tipo'] ?? '') == 4 ? 'selected' : '' ?>>Cliente não válido</option>
        <option value="5" <?= ($_GET['tipo'] ?? '') == 5 ? 'selected' : '' ?>>Apagado</option>
    </select>
    Ordenar por:
    <select name="ordem">
        <option value="nome_utilizador" <?= ($_GET['ordem'] ?? '') === 'nome_utilizador' ? 'selected' : '' ?>>Nome</option>
        <option value="tipo_utilizador" <?= ($_GET['ordem'] ?? '') === 'tipo_utilizador' ? 'selected' : '' ?>>Tipo</option>
    </select>
    <select name="sentido">
        <option value="asc" <?= ($_GET['sentido'] ?? '') === 'desc' ? '' : 'selected' ?>>Asc</option>
        <option value="desc" <?= ($_GET['sentido'] ?? '') === 'desc' ? 'selected' : '' ?>>Desc</option>
    </select>
    <input type="submit" value="Aplicar">
</form>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Utilizador</th>
        <th>Email</th>
        <th>Tipo</th>
        <th>Ações</th>
    </tr>
    <?php
    while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_utilizador'] ?></td>
            <td><?= htmlspecialchars($row['nome_utilizador']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['tipo_utilizador'] ?></td>
            <td>
                <a href="?anular=<?= $row['id_utilizador'] ?>">Anular</a> |
                <a href="?validar=<?= $row['id_utilizador'] ?>">Validar</a>
            </td>
        </tr>
    <?php
    endwhile;
    ?>
</table>

<div style="text-align: center; margin-top: 30px;">
    <a href="pagina_utilizador.php">Voltar</a>
</div>

<?php
$stmt->close();
$conn->close();
?>