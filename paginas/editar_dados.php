<?php
session_start();

if (!isset($_SESSION["utilizador"])) {
    header("Location: logout.php");
    exit();
}

include "../basedados/basedados.h";

$nome_utilizador = $_SESSION["utilizador"];

// Inicializar variáveis para feedback
$erro = "";
$sucesso = "";

// Processar atualização dos dados após envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"] ?? "";
    $email = $_POST["email"] ?? "";
    $morada = $_POST["morada"] ?? "";
    $telemovel = $_POST["telemovel"] ?? "";

    // Validações básicas (podes melhorar)
    if (empty($nome) || empty($email)) {
        $erro = "Por favor preencha os campos obrigatórios: Nome e Email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Por favor insira um email válido.";
    } else {
        $sql_update = "UPDATE utilizador SET nome = ?, email = ?, morada = ?, telemovel = ? WHERE nome_utilizador = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssss", $nome, $email, $morada, $telemovel, $nome_utilizador);

        if (mysqli_stmt_execute($stmt_update)) {
            $sucesso = "Dados atualizados com sucesso!";
            // Atualiza variáveis para manter formulário preenchido
            $utilizador = [
                "nome" => $nome,
                "email" => $email,
                "morada" => $morada,
                "telemovel" => $telemovel,
                "nome_utilizador" => $nome_utilizador
            ];
        } else {
            $erro = "Erro ao atualizar os dados: " . mysqli_error($conn);
        }
    }
}

// Se não for POST ou se houve erro, carregar dados atuais para preencher o formulário
if ($_SERVER["REQUEST_METHOD"] !== "POST" || $erro) {
    $sql = "SELECT * FROM utilizador WHERE nome_utilizador = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $nome_utilizador);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!$resultado || mysqli_num_rows($resultado) == 0) {
        die("Erro ao obter os dados do utilizador.");
    }

    $utilizador = mysqli_fetch_assoc($resultado);
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Dados Pessoais</title>
    <style>
        form {
            width: 60%;
            margin: 20px auto;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #333;
            border-radius: 5px;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        .botoes {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        input[type="submit"], .voltar {
            padding: 10px 20px;
            background-color: #007BFF;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 10px;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
        }
        input[type="submit"]:hover, .voltar:hover {
            background-color: #0056b3;
        }
        .voltar {
            background-color: #6c757d;
            display: inline-block;
            line-height: normal;
        }
        .mensagem-erro {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .mensagem-sucesso {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h2>Editar Dados Pessoais</h2>

<?php if ($erro): ?>
    <p class="mensagem-erro"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($sucesso): ?>
    <p class="mensagem-sucesso"><?= htmlspecialchars($sucesso) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label for="nome">Nome Próprio:</label>
    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($utilizador["nome"]) ?>" required>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($utilizador["email"]) ?>" required>

    <label for="morada">Morada:</label>
    <input type="text" name="morada" id="morada" value="<?= htmlspecialchars($utilizador["morada"]) ?>">

    <label for="telemovel">Nº Telemóvel:</label>
    <input type="text" name="telemovel" id="telemovel" value="<?= htmlspecialchars($utilizador["telemovel"]) ?>">

    <div class="botoes">
        <input type="submit" value="Guardar Alterações">
        <a href="pagina_dados_pessoais.php" class="voltar">Cancelar</a>
    </div>
</form>

</body>
</html>
