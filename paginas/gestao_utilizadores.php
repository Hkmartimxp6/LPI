<?php
session_start();
include "../basedados/basedados.h";
include "utilizadores.php";

// Verificar se o utilizador está autenticado e é um administrador
if (!isset($_SESSION['utilizador']) || $_SESSION['utilizador']['tipo_utilizador'] != ADMINISTRADOR) {
    // Se não for um administrador, redirecionar ou mostrar mensagem de acesso restrito
    header("Location: index.php");
    exit;
}

// --- Criar um utilizador ---

// Verificar se o formulário foi submetido como POST e se o botão "criar" foi pressionado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar'])) {

    // Pass com MD5
    $password_md5 = md5($_POST['pass']);

    // Início da transação
    $conn->begin_transaction();
    try {
        // Obter o último ID da carteira
        $res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
        if (!$res) {
            throw new Exception("Erro ao obter último ID da carteira: " . $conn->error);
        }

        // ir buscar o último ID da carteira
        $row = $res->fetch_assoc();
        // Calcular o novo ID da carteira (max_id + 1)
        $novo_id_carteira = ($row['max_id'] ?? 0) + 1;

        // Criar nova carteira com saldo 0
        $stmt_carteira = $conn->prepare("INSERT INTO carteira (id_carteira, saldo) VALUES (?, 0)");
        // Verificar se a preparação da query foi bem-sucedida
        if (!$stmt_carteira) {
            throw new Exception("Erro ao preparar inserção da carteira: " . $conn->error);
        }

        // Fazer o bind do parâmetro
        $stmt_carteira->bind_param("i", $novo_id_carteira);
        // Executar a query
        $stmt_carteira->execute();
        // Fechar o statement
        $stmt_carteira->close();

        // Inserir novo utilizador com a nova carteira
        $stmt_utilizador = $conn->prepare("INSERT INTO utilizador (nome_utilizador, password, tipo_utilizador, id_carteira) 
                                           VALUES (?, ?, 3, ?)");
        // Verificar se a preparação da query foi bem-sucedida
        if (!$stmt_utilizador) {
            throw new Exception("Erro ao preparar inserção do utilizador: " . $conn->error);
        }
        // Fazer o bind dos parâmetros
        $stmt_utilizador->bind_param("ssi", $_POST['user'], $password_md5, $novo_id_carteira);
        // Executar a query
        $stmt_utilizador->execute();
        // Fechar o statement
        $stmt_utilizador->close();

        // Commit da transação
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao criar utilizador: " . $e->getMessage());
    }
}

// --- Validar / Anular ---

// Verificar se o formulário foi submetido como POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se o botão "anular" foi pressionado
    if (isset($_POST['anular'])) {

        // Preparar a query para anular/desativar o utilizador
        $stmt = $conn->prepare("UPDATE utilizador SET tipo_utilizador = 5 WHERE id_utilizador = ?");
        // Fazer o bind do parâmetro
        $stmt->bind_param("i", $_POST['anular']);

        // Executar a query
        $stmt->execute();

        // Fecha o statement
        $stmt->close();
    }
    // Verificar se o botão "validar" foi pressionado
    if (isset($_POST['validar'])) {

        // Preparar a query para validar o utilizador
        $stmt = $conn->prepare("UPDATE utilizador SET tipo_utilizador = 3 WHERE id_utilizador = ?");

        // Fazer o bind do parâmetro
        $stmt->bind_param("i", $_POST['validar']);

        // Executar a query
        $stmt->execute();

        // Fecha o statement
        $stmt->close();
    }
}

// --- Filtros e ordenação ---

// Filtrar pelo nome, se existe no formulário GET então adiciona o filtro
// com o valor %nome%, se não existir, usa o filtro padrão %
$filtro_nome = isset($_GET['filtro']) ? "%" . $_GET['filtro'] . "%" : "%";

// Filtrar pelo tipo, se existe no formulário GET então adiciona o filtro
// com o valor numérico do tipo, se não existir, usa o filtro padrão null
$filtro_tipo = isset($_GET['tipo']) && is_numeric($_GET['tipo']) ? intval($_GET['tipo']) : null;

// Ordem da filtragem, se existe no formulário GET e está nas opções, então usa o valor
// se não existir, usa o valor padrão 'nome_utilizador'
$ordem = isset($_GET['ordem']) && in_array($_GET['ordem'], ['nome_utilizador', 'tipo_utilizador']) ? $_GET['ordem'] : 'nome_utilizador';

// Sentido da ordenação, se existe no formulário GET e é 'desc', então usa 'DESC'
// se não existir, ou for 'asc', usa 'ASC'
$sentido = isset($_GET['sentido']) && $_GET['sentido'] === 'desc' ? 'DESC' : 'ASC';

// Query para obter os utilizadores
$sql = "SELECT * FROM utilizador WHERE nome_utilizador LIKE ?";

// Adiciona o nome nos parâmetros
$params = [$filtro_nome];

// Que é do tipo string
$types = "s";

// Se o filtro de tipo não for nulo, adiciona o filtro de tipo
if ($filtro_tipo !== null) {

    // Adiciona o filtro de tipo na query
    $sql .= " AND tipo_utilizador = ?";

    // Adiciona o tipo de utilizador nos parâmetros
    $params[] = $filtro_tipo;

    // Que é do tipo inteiro
    $types .= "i";
}

// Adiciona a ordenação à query
$sql .= " ORDER BY $ordem $sentido";

// Prepara a query
$stmt = $conn->prepare($sql);

// Faz o bind dos parâmetros
$stmt->bind_param($types, ...$params);

// Executa a query
$stmt->execute();

// Obtém o resultado e armazena na variável $resultado
$resultado = $stmt->get_result();
?>

<h2>Gestão de Utilizadores</h2>

<!-- Criar um novo Utilizador -->
<form method="post">
    <h3>Criar novo utilizador</h3>
    Utilizador: <input type="text" name="user" required>
    Password: <input type="password" name="pass" required>
    <input type="submit" name="criar" value="Criar">
</form>

<hr>

<!-- Filtros e ordenação -->
<form method="get">
    <!-- Form com o filtro de pesquisa -->
    Pesquisar nome: <input type="text" name="filtro" value="<?= htmlspecialchars($_GET['filtro'] ?? '') ?>">
    Tipo:
    <!-- Opções do tipo de utilizador  -->
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
        <!-- Ordema pelo nome  -->
        <option value="nome_utilizador" <?= ($_GET['ordem'] ?? '') === 'nome_utilizador' ? 'selected' : '' ?>>Nome</option>
        <!-- Ordema pelo tipo  -->
        <option value="tipo_utilizador" <?= ($_GET['ordem'] ?? '') === 'tipo_utilizador' ? 'selected' : '' ?>>Tipo</option>
    </select>
    <select name="sentido">
        <!-- Ordena de maneira ascendente -->
        <option value="asc" <?= ($_GET['sentido'] ?? '') === 'desc' ? '' : 'selected' ?>>Asc</option>
        <!-- Ordena de maneira descendente -->
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
    <!-- Ciclo while para obter a informação dos utilizadores -->
    <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_utilizador'] ?></td>
            <td><?= htmlspecialchars($row['nome_utilizador']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['tipo_utilizador'] ?></td>
            <td>
                <!-- Anular -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="anular" value="<?= $row['id_utilizador'] ?>">
                    <button type="submit" onclick="return confirm('Anular este utilizador?')">Anular</button>
                </form>
                <!-- Validar -->
                <form method="post" style="display:inline; margin-left: 5px;">
                    <input type="hidden" name="validar" value="<?= $row['id_utilizador'] ?>">
                    <button type="submit" onclick="return confirm('Validar este utilizador?')">Validar</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<div style="text-align: center; margin-top: 30px;">
    <a href="pagina_utilizador.php">Voltar</a>
</div>

<?php
$stmt->close();
$conn->close();
?>