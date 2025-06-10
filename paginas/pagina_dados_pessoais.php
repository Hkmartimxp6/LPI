<?php
include "../basedados/basedados.h";
include "utilizadores.php"; // Certifique-se de que este ficheiro define CLIENTE_NAO_VALIDO se for uma constante

session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['utilizador'])) {
    header("Location: login.php");
    exit();
}

// Obter dados do utilizador da base de dados
$username = $_SESSION['utilizador'];
$stmt = $conn->prepare("SELECT nome, email, morada, telemovel, nome_utilizador FROM utilizador WHERE nome_utilizador = ?"); // Adicionado nome_utilizador para o h2
if ($stmt === false) {
    die("Erro na preparação da query SELECT: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close(); // Fechar o statement após usar

// Processar formulário de atualização de dados
$mensagem = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    // Escapar strings para segurança (embora prepared statements já ajudem)
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telemovel_post = mysqli_real_escape_string($conn, $_POST['telemovel']);
    $morada = mysqli_real_escape_string($conn, $_POST['morada']);

    // Atualizar na base de dados com prepared statement
    $stmt_update = $conn->prepare("UPDATE utilizador SET
                                    nome = ?,
                                    email = ?,
                                    telemovel = ?,
                                    morada = ?
                                    WHERE nome_utilizador = ?");
    if ($stmt_update === false) {
        die("Erro na preparação da query UPDATE: " . $conn->error);
    }
    $stmt_update->bind_param("sssss", $nome, $email, $telemovel_post, $morada, $username);

    if ($stmt_update->execute()) {
        $mensagem = "Dados atualizados com sucesso!";
        // Atualizar dados locais para exibir no formulário após a atualização
        $userData['nome'] = $nome;
        $userData['email'] = $email;
        $userData['telemovel'] = $telemovel_post;
        $userData['morada'] = $morada;
    } else {
        $mensagem = "Erro ao atualizar os dados: " . $stmt_update->error;
    }
    $stmt_update->close();
}
$conn->close(); // Fechar a conexão no final
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil - FelixBus</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> </head>
<body class="main-layout">

    <div class="profile-container">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php if (!empty($mensagem)): ?>
                    <div class="alerts">
                        <div class="alert-success">
                            <?php echo $mensagem; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="text-center">
                                <h2>Bem-vindo, <?php echo $userData['nome_utilizador'] ?? ''; ?></h2> </div>
                        </div>

                        <div class="profile-body">
                            <div class="tab-buttons">
                                <button class="tab-button active" onclick="openTab('dados-pessoais')">Dados Pessoais</button>
                                <button class="tab-button" onclick="openTab('viagens')">Minhas Viagens</button>
                                <button class="tab-button" onclick="openTab('alterarPassword')">Alterar Password</button>
                            </div>

                            <div id="dados-pessoais" class="tab-content active">
                                <h3 class="section-title">Dados Pessoais</h3>

                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Nome</label>
                                                <div class="input-overlay-container">
                                                    <input type="text" class="form-control" name="nome" value="<?php echo $userData['nome'] ?? ''; ?>">
                                                    <i class="fas fa-pencil-alt input-overlay-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Email</label>
                                                <div class="input-overlay-container">
                                                    <input type="email" class="form-control" name="email" value="<?php echo $userData['email'] ?? ''; ?>">
                                                    <i class="fas fa-pencil-alt input-overlay-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Telemóvel</label>
                                                <div class="input-overlay-container">
                                                    <input type="text" class="form-control" name="telemovel" value="<?php echo $userData['telemovel'] ?? ''; ?>">
                                                    <i class="fas fa-pencil-alt input-overlay-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Username</label>
                                                <input type="text" class="form-control" value="<?php echo $username; ?>" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group info-item">
                                                <label class="info-label">Morada</label>
                                                <div class="input-overlay-container">
                                                    <input type="text" class="form-control" name="morada" value="<?php echo $userData['morada'] ?? ''; ?>">
                                                    <i class="fas fa-pencil-alt input-overlay-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right mt-4">
                                        <button type="submit" name="atualizar" class="btn-action">Guardar Alterações</button>
                                    </div>
                                </form>
                            </div>

                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="jquery.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script>
        function openTab(tabId) {
            var contents = document.querySelectorAll('.tab-content');
            var buttons = document.querySelectorAll('.tab-button');
            contents.forEach(c => c.classList.remove('active'));
            buttons.forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>