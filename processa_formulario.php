<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processamento de Formulário</title>
    <link rel="stylesheet" href="formulario.css"> 
</head>
<body>
    <div class="mensagem-container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "coopanest";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='erro'>Conexão falhou: " . $conn->connect_error . "</p>");
        }

        $nome = trim($_POST['nome']);
        $idade = trim($_POST['idade']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $endereco = trim($_POST['endereco']);
        $data_nascimento = trim($_POST['data_nascimento']);
        $cpf = trim($_POST['cpf']);
        $genero = trim($_POST['genero']);

        $cpf = preg_replace('/[^\d]/', '', $cpf); 
        $telefone = preg_replace('/[^\d]/', '', $telefone); 

        if (empty($nome) || empty($idade) || empty($email) || empty($telefone) || empty($endereco) || empty($data_nascimento) || empty($cpf) || empty($genero)) {
            die("<p class='erro'>Erro: Todos os campos são obrigatórios.</p>");
        }

        $hoje = date('Y-m-d');
        if ($data_nascimento > $hoje) {
            die("<p class='erro'>Erro: A data de nascimento não pode ser uma data futura.</p>");
        }

        $anoAtual = date('Y');
        $anoNascimento = (int)date('Y', strtotime($data_nascimento));
        $mesAtual = date('m');
        $mesNascimento = (int)date('m', strtotime($data_nascimento));
        $diaAtual = date('d');
        $diaNascimento = (int)date('d', strtotime($data_nascimento));

        $idadeCalculada = $anoAtual - $anoNascimento;
        if (($mesAtual < $mesNascimento) || ($mesAtual == $mesNascimento && $diaAtual < $diaNascimento)) {
            $idadeCalculada--; 
        }

        if ((int)$idade !== $idadeCalculada) {
            die("<p class='erro'>Erro: A idade não condiz com a data de nascimento.</p>");
        }

        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome)) {
            die("<p class='erro'>Erro: O Nome deve conter apenas letras e espaços.</p>");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("<p class='erro'>Erro: O email deve estar em um formato válido, como exemplo@dominio.com.</p>");
        }

        if (!preg_match('/^\d{11}$/', $cpf)) {
            die("<p class='erro'>Erro: O CPF deve conter 11 dígitos numéricos.</p>");
        }

        if (!preg_match('/^\d{11}$/', $telefone)) {
            die("<p class='erro'>Erro: O telefone deve conter 11 dígitos numéricos (incluindo o DDD).</p>");
        }

        $sql = "INSERT INTO dados_originais (nome, idade, email, telefone, endereco, data_nascimento, cpf, genero)
        VALUES ('$nome', '$idade', '$email', '$telefone', '$endereco', '$data_nascimento', '$cpf', '$genero')";

        if ($conn->query($sql) === TRUE) {
            echo "<p class='sucesso'>Dados cadastrados com sucesso!</p>";
        } else {
            echo "<p class='erro'>Erro ao cadastrar os dados: " . $conn->error . "</p>";
        }

        $conn->close();
        ?>
        <a href="formulario.php" class="botao-voltar">Voltar</a>
    </div>
</body>
</html>