<?php

include "../basedados/basedados.h"; // Certifique-se de que o caminho para basedados.h está correto
include "utilizadores.php"; // Certifique-se de que o caminho para utilizadores.php está correto

session_start();

$loggedIn = false;

// Se o utilizador está com sessão iniciada, então está logado
if (isset($_SESSION["utilizador"])) {
    $loggedIn = true;
}

// 1. Inicializar variáveis de filtro a partir dos parâmetros GET
// Usamos o operador ?? (null coalescing) para definir uma string vazia se o parâmetro não existir
$filtro_origem = $_GET['origem'] ?? '';
$filtro_destino = $_GET['destino'] ?? '';
$filtro_data = $_GET['data'] ?? '';

// 2. Construir a query SQL dinamicamente
// Começamos com uma query base e adicionamos condições WHERE se os filtros existirem
$sql = "
    SELECT
        v.id_viagem,
        v.data,
        v.hora,
        v.hora_chegada, -- ADICIONADO AQUI: Selecionando a nova coluna
        v.preco,
        origem_loc.localidade AS origem,
        destino_loc.localidade AS destino
    FROM viagem v
    INNER JOIN rota r ON v.id_rota = r.id_rota
    INNER JOIN localidade origem_loc ON r.id_origem = origem_loc.id_localidade
    INNER JOIN localidade destino_loc ON r.id_destino = destino_loc.id_localidade
    WHERE 1=1
";

// Adicionar condições à query se os filtros estiverem preenchidos
if (!empty($filtro_origem)) {
    // Usar LIKE para pesquisa parcial (correspondência de substring)
    // Os '%' serão adicionados no bind_param para evitar SQL Injection
    $sql .= " AND origem_loc.localidade LIKE ?";
}
if (!empty($filtro_destino)) {
    $sql .= " AND destino_loc.localidade LIKE ?";
}
if (!empty($filtro_data)) {
    // Para datas, geralmente queremos uma correspondência exata
    $sql .= " AND v.data = ?";
}

// Adicionar ordenação
$sql .= " ORDER BY v.data, v.hora";

// 3. Preparar a query (para segurança contra SQL Injection)
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da query: " . $conn->error);
}

// 4. Ligar os parâmetros (bind_param) aos placeholders (?) na query
$params = [];
$types = ""; // String que define os tipos dos parâmetros ('s' para string, 'i' para int, 'd' para double)

if (!empty($filtro_origem)) {
    $params[] = "%" . $filtro_origem . "%"; // Adiciona os '%' aqui, não na query SQL
    $types .= "s"; // 's' para string
}
if (!empty($filtro_destino)) {
    $params[] = "%" . $filtro_destino . "%";
    $types .= "s";
}
if (!empty($filtro_data)) {
    $params[] = $filtro_data;
    $types .= "s"; // Data é tratada como string pelo prepared statement
}

// Se houver parâmetros para ligar, faça o bind
if (!empty($params)) {
    // A sintaxe `...$params` é o "splat operator" (desde PHP 5.6)
    // que "desempacota" o array $params como argumentos individuais para bind_param
    $stmt->bind_param($types, ...$params);
}

// 5. Executar a query
$stmt->execute();

// 6. Obter o resultado
$resultado = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Felix Bus - Viagens</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="icon" href="fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="owl.carousel.min.css">
    <link rel="stylesheet" href="owl.theme.default.min.css">
    <style>
        .viagem-card {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            outline: 1px solid black;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .viagem-info {
            display: flex;
            flex-direction: row; /* Alterado para row para exibição horizontal */
            align-items: center; /* Alinha os itens verticalmente ao centro */
            gap: 20px; /* Espaço entre os itens de informação */
        }
        .viagem-info span {
            margin: 0; /* Remove margem vertical dos spans individuais */
        }
        .viagem-horas {
            font-weight: bold;
            font-size: 18px;
            margin-right: 100px; /* Espaço entre horas e outras infos */
        }
        .viagem-preco {
            font-size: 20px;
            font-weight: bold;
            color: green;
        }
        .continuar-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .continuar-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body class="main-layout">
    <div id="sidebarUser" class="sidebar-user">
        <div class="sidebar-header">
            <span>Olá, <?php echo $_SESSION["utilizador"] ?? "utilizador" ?></span>
            <button onclick="toggleSidebar()" class="close-btn">×</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="pagina_utilizador.php">Perfil</a></li>
            <li><a href="carteira.php">Carteira</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="loader_bg">
        <div class="loader"><img src="loading.gif" alt="#" /></div>
    </div>
    <header>
        <div class="header">
            <div class="header_white_section">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="header_information">
                                <ul class="lista" style="display:flex; align-items: center; height: 100%;">
                                    <li><img src="1.png" alt="#" /> Alameda Cardeal Cerejeira</li>
                                    <li><img src="2.png" alt="#" /> +351 963 961 984</li>
                                    <li><img src="3.png" alt="#" /> felixbus@gmail.com</li>
                                    <?php if ($loggedIn): ?>
                                        <li>
                                            <a href="javascript:void(0);" onclick="toggleSidebar()" title="Perfil">
                                                <img src="icon_utilizador.png" alt="Ícone do Utilizador"
                                                    style="width: 48px; height: 48px;
                                                            border-radius: 50%;
                                                            background-color: #fff;">
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <a class="botao_de_login" href="login.php">Faz Login!</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                        <div class="full">
                            <div class="center-desk">
                                <div class="logo">
                                    <a href="index.php"><img src="logo.png" style="width:300px;" alt="#"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                        <div class="menu-area">
                            <div class="limit-box">
                                <nav class="main-menu">
                                    <ul class="menu-area-main">
                                        <li class="active"> <a href="index.php">Início</a> </li>
                                        <li> <a href="sobre_nos.php">Sobre nós</a> </li>
                                        <li><a href="viagens.php">Viagens</a></li>
                                        <li><a href="#contact">Contacta-nos</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="container mt-5">
        <h1 class="mb-4">Viagens Disponíveis</h1>
        <?php
        if ($resultado && $resultado->num_rows > 0) {
            while ($linha = $resultado->fetch_assoc()) {
                $id_viagem = htmlspecialchars($linha["id_viagem"]);
                // Formatação da hora de partida
                $hora_partida_formatada = (new DateTime($linha["hora"]))->format("H:i");
                // Formatação da hora de chegada diretamente do banco de dados
                $hora_chegada_formatada = (new DateTime($linha["hora_chegada"]))->format("H:i");

                echo "<div class='viagem-card'>";
                    echo "<div class='viagem-info'>";
                    // Exibindo as horas formatadas
                    echo "<div class='viagem-horas'>" . $hora_partida_formatada . " → " . $hora_chegada_formatada . "</div>";
                    // Agora 'origem' e 'destino' vêm da junção com a tabela de localidades
                    echo "<span><strong>Origem:</strong> " . htmlspecialchars($linha["origem"]) . "</span>";
                    echo "<span><strong>Destino:</strong> " . htmlspecialchars($linha["destino"]) . "</span>";
                    echo "<span><strong>Data:</strong> " . htmlspecialchars($linha["data"]) . "</span>";
                    echo "</div>";
                    echo "<div>";
                    echo "<div class='viagem-preco'>" . number_format($linha["preco"], 2, ',', '.') . " €</div>";
                    // Passando o id_viagem para a página de compra
                    echo "<a href='comprar_bilhete.php?id_viagem=" . htmlspecialchars($linha["id_viagem"]) . "' class='continuar-btn'>Continuar</a>";
                    echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Não foram encontradas viagens com os critérios de pesquisa.</p>";
        }
        $conn->close();
        ?>
    </div>

    
    <script src="jquery.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script src="jquery-3.0.0.min.js"></script>
    <script src="plugin.js"></script>
    <script src="jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="custom.js"></script>
    <script src="owl.carousel.js"></script>
    <script>
        $(document).ready(function() {
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                margin: 10,
                nav: true,
                loop: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 2
                    },
                    1000: {
                        items: 3
                    }
                }
            })
        })

        function toggleSidebar() {
            const sidebar = document.getElementById("sidebarUser");
            sidebar.classList.toggle("active");
        }
    </script>
</body>
</html>