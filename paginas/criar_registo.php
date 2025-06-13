    <?php
    include "../basedados/basedados.h";
    include "utilizadores.php";

    // Inicia a sessão PHP
    session_start();

    // Verifica se os dados foram enviados
    if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo']) || !isset($_POST['email_registo']) ||
        !isset($_POST['nome']) || !isset($_POST['morada']) || !isset($_POST['telemovel'])) {
        die("Os dados não foram enviados corretamente. Por favor, preencha todos os campos.");
    }

    // Obter os dados do formulário
    $user = $_POST["utilizador_registo"];
    $pass_raw = $_POST["password_registo"];
    $confirmar_pass = $_POST["confirmar_password"];
    $email = $_POST["email_registo"];
    $nome = $_POST["nome"];
    $morada = $_POST["morada"];
    $telemovel = $_POST["telemovel"];

    // md5 para hashing da password
    $pass_hashed = md5($pass_raw);

    // Por defeito, o tipo de utilizador é CLIENTE_NAO_VALIDO
    $tipo = CLIENTE_NAO_VALIDO;

    // Validar se as passwords coincidem
    if ($pass_raw !== $confirmar_pass) {
        echo "As passwords não coincidem! Redirecionando para o registo...";
        header("refresh:3; url=registo.php");
        exit();
    }

    // Query para verificar se o utilizador ou email já existem
    // Preparar a query 
    $stmt_check = $conn->prepare("SELECT nome_utilizador FROM utilizador WHERE nome_utilizador = ? OR email = ?");
    // Fazer o bind dos parâmetros
    $stmt_check->bind_param("ss", $user, $email);
    // Executar a query
    $stmt_check->execute();
    // Obter o resultado
    $result_check = $stmt_check->get_result();

    // Verificar se o utilizador ou email já existe na bd
    if ($result_check->num_rows > 0) {
        echo "Nome de utilizador ou email já existe. Redirecionando para o registo...";
        header("refresh:3; url=registo.php");
        // Fechar a statement e a conexão
        $stmt_check->close();
        // Fechar a conexão
        $conn->close();
        exit();
    }
    // Fechar a statement de verificação
    $stmt_check->close();

    // Obter o ultimo id da carteira (max + 1)
    $res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
    // Verificar se a query foi bem sucedida
    if (!$res) {
        echo "Erro ao obter o último ID da carteira: " . $conn->error;
        // Fechar a conexão
        $conn->close();
        exit();
    }
    // Verificar se a query retornou resultados
    $row = $res->fetch_assoc();
    // Associa o do novo id_carteira
    $new_id_carteira = $row['max_id'] + 1;

    // Criar nova carteira
    $conn->begin_transaction();

    // Preparar a query para inserir a nova carteira
    $stmt_carteira = $conn->prepare("INSERT INTO carteira (id_carteira, saldo) VALUES (?, 0)");
    // Verificar se a preparação da query foi bem sucedida
    if ($stmt_carteira === false) {
        echo "Erro na preparação da query da carteira: " . $conn->error;
        // Voltar atrás na transação
        $conn->rollback();
        // Fechar a conexão
        $conn->close();
        exit();
    }
    // Fazer o bind do novo id_carteira
    $stmt_carteira->bind_param("i", $new_id_carteira);
    // Executar a query para inserir a nova carteira
    if (!$stmt_carteira->execute()) {
        echo "Erro ao criar carteira: " . $stmt_carteira->error;
        // Fechar o statement
        $stmt_carteira->close();
        // Voltar atrás na transação
        $conn->rollback();
        // Fechar a conexão
        $conn->close();
        exit();
    }
    // Fechar o statement da carteira
    $stmt_carteira->close();

    // Inserir o novo utilizador com os novos campos
    // sql para inserir o novo utilizador
    $sql_insert_user = "INSERT INTO utilizador (password, nome_utilizador, 
                                                nome, morada, telemovel, 
                                                tipo_utilizador, id_carteira, 
                                                email) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar a query para inserir o novo utilizador
    $stmt_insert = $conn->prepare($sql_insert_user);

    // Verificar se a preparação da query foi bem sucedida
    if ($stmt_insert === false) {
        echo "Erro na preparação da query do utilizador: " . $conn->error;
        // Voltar atrás na transação
        $conn->rollback();
        // Fechar a conexão
        $conn->close();
        exit();
    }
    // Fazer o bind dos parâmetros
    $stmt_insert->bind_param("sssssiis", $pass_hashed, $user, $nome, $morada, $telemovel, $tipo, $new_id_carteira, $email);

    // Caso o insert funcione
    if ($stmt_insert->execute()) {
        // Commit da transação
        $conn->commit();
        echo "Registo efetuado com sucesso! Redirecionando...";
        header("refresh:3; url=index.php");
    } else {
        // Caso o insert falhe, desfaz a transação
        $conn->rollback();
        echo "Erro ao registar utilizador: " . $stmt_insert->error;
        header("refresh:5; url=registo.php");
    }
    // Fechar o statement de inserção
    $stmt_insert->close();
    // Fechar a conexão com a base de dadosS
    $conn->close();
    ?>