<?php
include "../basedados/basedados.h";

// Inicia a sessão
session_start();

// Verifica se o utilizador está autenticado
if (!isset($_SESSION["utilizador"])) {
    header("Location: logout.php");
    exit();
}

// Obtém os dados do utilizador da sessão
$utilizador = $_SESSION["utilizador"];
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <title>Dados Pessoais</title>
    <style>
        /* Estilos para a tabela e botões */
        table {
            border-collapse: collapse;
            margin: 20px auto;
            width: 60%;
        }

        td,
        th {
            padding: 10px;
            border: 1px solid #333;
            text-align: left;
        }

        h2 {
            text-align: center;
        }

        .botoes {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007BFF;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 10px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <h2>Dados Pessoais do Utilizador</h2>

    <!-- Tabela com os dados pessoais do utilizador -->
    <table>
        <tr>
            <th>Campo</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Nome de Utilizador</td>
            <td><?= htmlspecialchars($utilizador["nome_utilizador"]) ?></td>
        </tr>
        <tr>
            <td>Nome Próprio</td>
            <td><?= htmlspecialchars($utilizador["nome"]) ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><?= htmlspecialchars($utilizador["email"]) ?></td>
        </tr>
        <tr>
            <td>Morada</td>
            <td><?= htmlspecialchars($utilizador["morada"]) ?></td>
        </tr>
        <tr>
            <td>Nº Telemóvel</td>
            <td><?= htmlspecialchars($utilizador["telemovel"]) ?></td>
        </tr>
    </table>

    <div class="botoes">
        <!-- Botão para editar dados -->
        <form action="editar_dados.php" method="post">
            <input type="submit" value="Editar Dados">
        </form>
        <!-- Botão para voltar à página do utilizador -->
        <form action="pagina_utilizador.php">
            <input type="submit" value="Voltar">
        </form>
    </div>

</body>

</html>