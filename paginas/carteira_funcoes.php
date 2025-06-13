<?php
define('ADICIONAR_SALDO', 1);
define('RETIRAR_SALDO', 2);
define('COMPRAR_BILHETE', 3);
define('VENDER_BILHETE', 4);

/**
 * Adiciona saldo à carteira de um utilizador e regista a transação.
 *
 * @param mysqli $conn A conexão com a base de dados.
 * @param int $id_carteira O ID da carteira do utilizador.
 * @param float $valor O valor a ser depositado.
 * @param int $id_operacao_adicionar_saldo O ID da operação 'Adicionar Saldo'.
 * @return array Um array associativo com 'success' (booleano) e 'message' (string).
 */
function adicionarSaldo(mysqli $conn, int $id_carteira, float $valor, int $id_operacao_adicionar_saldo): array
{
    // Inicia uma transação para garantir a atomicidade das operações
    $conn->begin_transaction();
    try {
        // Obter o saldo atual, bloqueando a linha para evitar problemas de concorrência
        $stmt_get_saldo = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ? FOR UPDATE");
        if (!$stmt_get_saldo) {
            throw new Exception("Erro ao preparar query para obter saldo: " . $conn->error);
        }
        $stmt_get_saldo->bind_param("i", $id_carteira);
        $stmt_get_saldo->execute();
        $result_get_saldo = $stmt_get_saldo->get_result();

        if ($result_get_saldo->num_rows === 0) {
            throw new Exception("Erro interno: Saldo do utilizador não encontrado para atualização.");
        }
        $row_get_saldo = $result_get_saldo->fetch_assoc();
        $saldo_anterior = $row_get_saldo['saldo'];
        $novo_saldo = $saldo_anterior + $valor;
        $stmt_get_saldo->close();

        // Atualizar o saldo na tabela 'carteira'
        $stmt_update_saldo = $conn->prepare("UPDATE carteira SET saldo = ? WHERE id_carteira = ?");
        if (!$stmt_update_saldo) {
            throw new Exception("Erro ao preparar query para atualizar saldo: " . $conn->error);
        }
        $stmt_update_saldo->bind_param("di", $novo_saldo, $id_carteira);
        if (!$stmt_update_saldo->execute()) {
            throw new Exception("Erro ao atualizar o saldo: " . $stmt_update_saldo->error);
        }
        $stmt_update_saldo->close();

        // 3. Registar a transação na tabela 'carteira_log'
        $stmt_insert_log = $conn->prepare("INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) VALUES (?, ?, NOW(), ?)");
        if (!$stmt_insert_log) {
            throw new Exception("Erro ao preparar query para registar transação: " . $conn->error);
        }
        $stmt_insert_log->bind_param("iid", $id_carteira, $id_operacao_adicionar_saldo, $valor);
        if (!$stmt_insert_log->execute()) {
            throw new Exception("Erro ao registar a transação no log: " . $stmt_insert_log->error);
        }
        $stmt_insert_log->close();

        $conn->commit();
        return ['success' => true, 'message' => "Depósito de " . number_format($valor, 2, ',', '.') . "€ realizado com sucesso! Novo saldo: " . number_format($novo_saldo, 2, ',', '.') . "€"];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => "Ocorreu um erro inesperado na transação: " . $e->getMessage()];
    }
}

/**
 * Retira saldo da carteira de um utilizador e regista a transação.
 *
 * @param mysqli $conn A conexão com a base de dados.
 * @param int $id_carteira O ID da carteira do utilizador.
 * @param float $valor O valor a ser levantado.
 * @param int $id_operacao_retirar_saldo O ID da operação 'Retirar Saldo'.
 * @return array Um array associativo com 'success' (booleano) e 'message' (string).
 */
function retirarSaldo(mysqli $conn, int $id_carteira, float $valor, int $id_operacao_retirar_saldo): array
{
    $conn->begin_transaction();
    try {
        // Obter o saldo atual (FOR UPDATE para bloquear a linha enquanto a transação está em andamento)
        $stmt_get_saldo = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ? FOR UPDATE");
        if (!$stmt_get_saldo) {
            throw new Exception("Erro ao preparar query para obter saldo: " . $conn->error);
        }
        $stmt_get_saldo->bind_param("i", $id_carteira);
        $stmt_get_saldo->execute();
        $result_get_saldo = $stmt_get_saldo->get_result();

        if ($result_get_saldo->num_rows === 0) {
            throw new Exception("Erro interno: Saldo do utilizador não encontrado para verificação.");
        }
        $row_get_saldo = $result_get_saldo->fetch_assoc();
        $saldo_anterior = $row_get_saldo['saldo'];
        $stmt_get_saldo->close();

        // Verificar se há saldo suficiente para o levantamento
        if ($saldo_anterior < $valor) {
            throw new Exception("Saldo insuficiente para realizar o levantamento. Saldo atual: " . number_format($saldo_anterior, 2, ',', '.') . "€");
        }

        $novo_saldo = $saldo_anterior - $valor;

        // 2. Atualizar o saldo
        $stmt_update_saldo = $conn->prepare("UPDATE carteira SET saldo = ? WHERE id_carteira = ?");
        if (!$stmt_update_saldo) {
            throw new Exception("Erro ao preparar query para atualizar saldo: " . $conn->error);
        }
        $stmt_update_saldo->bind_param("di", $novo_saldo, $id_carteira);
        if (!$stmt_update_saldo->execute()) {
            throw new Exception("Erro ao atualizar o saldo: " . $stmt_update_saldo->error);
        }
        $stmt_update_saldo->close();

        // 3. Registar a transação na tabela 'carteira_log'
        $stmt_insert_log = $conn->prepare("INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) VALUES (?, ?, NOW(), ?)");
        if (!$stmt_insert_log) {
            throw new Exception("Erro ao preparar query para registar transação: " . $conn->error);
        }
        $stmt_insert_log->bind_param("iid", $id_carteira, $id_operacao_retirar_saldo, $valor);
        if (!$stmt_insert_log->execute()) {
            throw new Exception("Erro ao registar a transação no log: " . $stmt_insert_log->error);
        }
        $stmt_insert_log->close();

        $conn->commit();
        return ['success' => true, 'message' => "Levantamento de " . number_format($valor, 2, ',', '.') . "€ realizado com sucesso! Novo saldo: " . number_format($novo_saldo, 2, ',', '.') . "€"];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => "Ocorreu um erro inesperado na transação: " . $e->getMessage()];
    }
}

/**
 * Obtém o ID da carteira de um utilizador.
 *
 * @param mysqli $conn A conexão com a base de dados.
 * @param string $nome_utilizador O nome de utilizador.
 * @return int|null O ID da carteira ou null se não for encontrado.
 */
function getIdCarteiraUtilizador(mysqli $conn, string $nome_utilizador): ?int
{
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE nome_utilizador = ?");
    if (!$stmt) {
        error_log("Erro ao preparar query para obter id_carteira: " . $conn->error);
        return null;
    }
    $stmt->bind_param("s", $nome_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id_carteira'];
    }
    $stmt->close();
    return null;
}

/**
 * Obtém o saldo atual da carteira.
 *
 * @param mysqli $conn A conexão com a base de dados.
 * @param int $id_carteira O ID da carteira.
 * @return float O saldo atual ou 0.0 se não for encontrado.
 */
function getSaldoAtual(mysqli $conn, int $id_carteira): float
{
    $stmt = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ?");
    if (!$stmt) {
        error_log("Erro ao preparar query para obter saldo: " . $conn->error);
        return 0.0;
    }
    $stmt->bind_param("i", $id_carteira);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float) $row['saldo'];
    }
    $stmt->close();
    return 0.0;
}
?>