<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['utilizador'])) {
    header("Location: login.php");
    exit();
}

// Obter dados do utilizador da base de dados
$username = $_SESSION['utilizador'];
$stmt = $conn->prepare("SELECT * FROM utilizador WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Processar formulário de atualização de dados
$mensagem = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telefone = mysqli_real_escape_string($conn, $_POST['telefone']);
    $morada = mysqli_real_escape_string($conn, $_POST['morada']);
    $codigo_postal = mysqli_real_escape_string($conn, $_POST['codigo_postal']);
    $cidade = mysqli_real_escape_string($conn, $_POST['cidade']);
    
    // Atualizar na base de dados com prepared statement
    $stmt = $conn->prepare("UPDATE utilizador SET 
                          nome = ?, 
                          email = ?, 
                          telefone = ?,
                          morada = ?,
                          codigo_postal = ?,
                          cidade = ?
                          WHERE username = ?");
    $stmt->bind_param("sssssss", $nome, $email, $telefone, $morada, $codigo_postal, $cidade, $username);
    
    if ($stmt->execute()) {
        $mensagem = "Dados atualizados com sucesso!";
        // Atualizar dados locais
        $userData['nome'] = $nome;
        $userData['email'] = $email;
        $userData['telefone'] = $telefone;
        $userData['morada'] = $morada;
        $userData['codigo_postal'] = $codigo_postal;
        $userData['cidade'] = $cidade;
    } else {
        $mensagem = "Erro ao atualizar os dados: " . $conn->error;
    }
}
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
    <style>
        /* [Mantido todo o CSS original] */
    </style>
</head>
<body class="main-layout">
    <!-- Header inclui logo e menu de navegação -->
    <?php include "header.php"; ?>

    <!-- Conteúdo Principal -->
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
                                <div class="profile-avatar mx-auto">
                                    <?php echo strtoupper(substr($userData['nome'] ?? 'U', 0, 1)); ?>
                                </div>
                                <h2>Bem-vindo, <?php echo $userData['nome'] ?? $username; ?></h2>
                            </div>
                        </div>
                        
                        <div class="profile-body">
                            <div class="tab-buttons">
                                <button class="tab-button active" onclick="openTab('dados-pessoais')">Dados Pessoais</button>
                                <button class="tab-button" onclick="openTab('viagens')">Minhas Viagens</button>
                                <button class="tab-button" onclick="openTab('alterarPassword')">Alterar Password</button>
                            </div>
                            
                            <!-- Tab Dados Pessoais -->
                            <div id="dados-pessoais" class="tab-content active">
                                <h3 class="section-title">Dados Pessoais</h3>
                                
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Nome Completo</label>
                                                <input type="text" class="form-control" name="nome" value="<?php echo $userData['nome'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="<?php echo $userData['email'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Telefone</label>
                                                <input type="text" class="form-control" name="telefone" value="<?php echo $userData['telefone'] ?? ''; ?>">
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
                                                <input type="text" class="form-control" name="morada" value="<?php echo $userData['morada'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Código Postal</label>
                                                <input type="text" class="form-control" name="codigo_postal" value="<?php echo $userData['codigo_postal'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group info-item">
                                                <label class="info-label">Cidade</label>
                                                <input type="text" class="form-control" name="cidade" value="<?php echo $userData['cidade'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right mt-4">
                                        <button type="submit" name="atualizar" class="btn-action">Guardar Alterações</button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- [Restante do código mantido igual...] -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <!-- JavaScript Files -->
    <script src="jquery.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script>
        function openTab(tabId) {
            // [Código JavaScript mantido igual]
        }
    </script>
</body>
</html>