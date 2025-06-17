<?php
// Constantes para as operações de carteira
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
    // Inicia uma transação para garantir que toas operações sejam feitas apenas no commit
    $conn->begin_transaction();
    try {
        // Obter o saldo atual e bloquear a linha para evitar problemas de concorrência
        $stmt_get_saldo = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ? FOR UPDATE");
        if (!$stmt_get_saldo) {
            throw new Exception("Erro ao preparar query para obter saldo: " . $conn->error);
        }
        // fazer bind do parâmetro
        $stmt_get_saldo->bind_param("i", $id_carteira);
        // Executar a query
        $stmt_get_saldo->execute();
        // Obter o resultado e atribui à variável
        $result_get_saldo = $stmt_get_saldo->get_result();

        // Verificar se o saldo foi encontrado
        if ($result_get_saldo->num_rows === 0) {
            throw new Exception("Erro interno: Saldo do utilizador não encontrado para atualização.");
        }
        // Obter o saldo atual
        $row_get_saldo = $result_get_saldo->fetch_assoc();
        // Guardar o saldo anterior
        $saldo_anterior = $row_get_saldo['saldo'];
        // Calcular o novo saldo
        $novo_saldo = $saldo_anterior + $valor;
        // Fecha o statement
        $stmt_get_saldo->close();

        // Atualizar o saldo na tabela 'carteira'
        $stmt_update_saldo = $conn->prepare("UPDATE carteira SET saldo = ? WHERE id_carteira = ?");
        if (!$stmt_update_saldo) {
            throw new Exception("Erro ao preparar query para atualizar saldo: " . $conn->error);
        }
        // Fazer bind dos parâmetros
        $stmt_update_saldo->bind_param("di", $novo_saldo, $id_carteira);
        // Executar a query para atualizar o saldo e verifica se foi bem-sucedida
        if (!$stmt_update_saldo->execute()) {
            throw new Exception("Erro ao atualizar o saldo: " . $stmt_update_saldo->error);
        }
        // Fecha o statement
        $stmt_update_saldo->close();

        // Registar a transação na tabela 'carteira_log' para auditoria
        // Prepara a query para inserir o log da transação
        $stmt_insert_log = $conn->prepare("INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) 
                                           VALUES (?, ?, NOW(), ?)");
        // Verifica se a query foi preparada corretamente
        // Se não, lança a exceção com a mensagem de erro
        if (!$stmt_insert_log) {
            throw new Exception("Erro ao preparar query para registar transação: " . $conn->error);
        }
        // Faz bind dos parâmetros
        $stmt_insert_log->bind_param("iid", $id_carteira, $id_operacao_adicionar_saldo, $valor);
        // Executa a query para inserir o log da transação e verifica se foi bem-sucedida
        if (!$stmt_insert_log->execute()) {
            throw new Exception("Erro ao registar a transação no log: " . $stmt_insert_log->error);
        }
        // Fecha o statement
        $stmt_insert_log->close();

        // Commit da transação
        $conn->commit();
        // Retorna uma mensagem de sucesso com o novo saldo formatado
        return ['success' => true, 
                'message' => "Depósito de " . number_format($valor, 2, ',', '.') 
                . "€ realizado com sucesso! Novo saldo: " . number_format($novo_saldo, 2, ',', '.') . "€"];
    } catch (Exception $e) {
        // Se ocorrer um erro, desfaz a transação
        $conn->rollback();
        // Retorna uma mensagem de erro com a descrição do problema
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
    // Inicia uma transação para garantir que todas as operações sejam feitas apenas no commit
    $conn->begin_transaction();
    try {
        // Obter o saldo atual (FOR UPDATE para bloquear a linha enquanto a transação está a ocorer)
        $stmt_get_saldo = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ? FOR UPDATE");
        // Verifica se a query foi preparada corretamente
        if (!$stmt_get_saldo) {
            throw new Exception("Erro ao preparar query para obter saldo: " . $conn->error);
        }
        // Fazer bind do parâmetro
        $stmt_get_saldo->bind_param("i", $id_carteira);
        // Executar a query
        $stmt_get_saldo->execute();
        // Obter o resultado da query
        $result_get_saldo = $stmt_get_saldo->get_result();

        // Verificar se o saldo foi encontrado
        if ($result_get_saldo->num_rows === 0) {
            throw new Exception("Erro interno: Saldo do utilizador não encontrado para verificação.");
        }
        // Obter o saldo atual
        $row_get_saldo = $result_get_saldo->fetch_assoc();
        // Guardar o saldo anterior
        $saldo_anterior = $row_get_saldo['saldo'];
        // Fecha o statement
        $stmt_get_saldo->close();

        // Verificar se há saldo suficiente para o levantamento
        if ($saldo_anterior < $valor) {
            throw new Exception("Saldo insuficiente para realizar a operação. Saldo atual: "
                . number_format($saldo_anterior, 2, ',', '.') . "€");
        }

        // Calcular o novo saldo após o levantamento
        $novo_saldo = $saldo_anterior - $valor;

        // Atualizar o saldo
        $stmt_update_saldo = $conn->prepare("UPDATE carteira SET saldo = ? WHERE id_carteira = ?");
        // Verifica se a query foi preparada corretamente
        if (!$stmt_update_saldo) {
            throw new Exception("Erro ao preparar query para atualizar saldo: " . $conn->error);
        }
        // Fazer bind dos parâmetros
        $stmt_update_saldo->bind_param("di", $novo_saldo, $id_carteira);
        // Executar a query para atualizar o saldo e verifica se foi bem-sucedida
        if (!$stmt_update_saldo->execute()) {
            throw new Exception("Erro ao atualizar o saldo: " . $stmt_update_saldo->error);
        }
        // Fecha o statement
        $stmt_update_saldo->close();

        // Registar a transação na tabela 'carteira_log'
        // Prepara a query para inserir o log da transação
        $stmt_insert_log = $conn->prepare("INSERT INTO carteira_log (id_carteira, id_operacao, data, montante) VALUES (?, ?, NOW(), ?)");
        // Verifica se a query foi preparada corretamente
        if (!$stmt_insert_log) {
            throw new Exception("Erro ao preparar query para registar transação: " . $conn->error);
        }
        // Faz bind dos parâmetros
        $stmt_insert_log->bind_param("iid", $id_carteira, $id_operacao_retirar_saldo, $valor);
        // Executa a query para inserir o log da transação e verifica se foi bem-sucedida
        if (!$stmt_insert_log->execute()) {
            throw new Exception("Erro ao registar a transação no log: " . $stmt_insert_log->error);
        }
        // Fecha o statement
        $stmt_insert_log->close();

        // Commit da transação
        $conn->commit();
        // Retorna uma mensagem de sucesso com o novo saldo formatado
        return ['success' => true, 
                'message' => "Levantamento de " . number_format($valor, 2, ',', '.') 
                . "€ realizado com sucesso! Novo saldo: " . number_format($novo_saldo, 2, ',', '.') . "€"];
    } catch (Exception $e) {
        // Se ocorrer um erro, desfaz a transação
        $conn->rollback();
        // Retorna uma mensagem de erro com a descrição do problema
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
    // Prepara a query para obter o id_carteira do utilizador
    $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE nome_utilizador = ?");
    // Verifica se a query foi preparada corretamente
    if (!$stmt) {
        error_log("Erro ao preparar query para obter id_carteira: " . $conn->error);
        return null;
    }
    // Faz bind do parâmetro
    $stmt->bind_param("s", $nome_utilizador);
    // Executa a query
    $stmt->execute();
    // Obtém o resultado da query
    $result = $stmt->get_result();
    // Verifica se o resultado contém alguma linha
    if ($result->num_rows > 0) {
        // Busca o id_carteira do resultado
        $row = $result->fetch_assoc();
        // Fecha o statement
        $stmt->close();
        //e retorna o id_carteira
        return $row['id_carteira'];
    }
    // Se não encontrar, fecha o statement
    $stmt->close();
    // e retorna null
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
    // Prepara a query para obter o saldo da carteira
    $stmt = $conn->prepare("SELECT saldo FROM carteira WHERE id_carteira = ?");
    // Verifica se a query foi preparada corretamente
    if (!$stmt) {
        // Registra o erro no log de erros
        error_log("Erro ao preparar query para obter saldo: " . $conn->error);
        // Retorna 0.0 se houver erro na preparação da query
        return 0.0;
    }
    // Faz bind do parâmetro
    $stmt->bind_param("i", $id_carteira);
    // Executa a query
    $stmt->execute();
    // Obtém o resultado da query
    $result = $stmt->get_result();
    // Verifica se o resultado contém alguma linha
    if ($result->num_rows > 0) {
        // Busca o saldo do resultado
        $row = $result->fetch_assoc();
        // Fecha o statement
        $stmt->close();
        // e retorna o saldo como float
        return (float) $row['saldo'];
    }
    // Se não encontrar, fecha o statement
    $stmt->close();
    // e retorna 0.0
    return 0.0;
}
