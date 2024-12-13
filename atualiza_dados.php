<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coopanest";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);

$erros = [];

if (isset($data['nome']) && !preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $data['nome'])) {
    $erros[] = "O Nome deve conter apenas letras e espaços.";
}

if (isset($data['cpf'])) {
    $data['cpf'] = preg_replace('/[^\d]/', '', $data['cpf']); 
    if (!preg_match('/^\d{11}$/', $data['cpf'])) {
        $erros[] = "O CPF deve conter 11 dígitos numéricos.";
    }
}

if (isset($data['data_nascimento']) && !empty($data['data_nascimento'])) {
    if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data['data_nascimento'])) {
        $erros[] = "A data de nascimento deve estar no formato dd/mm/yyyy.";
    } else {
        $data_convertida = DateTime::createFromFormat('d/m/Y', $data['data_nascimento']);
        if ($data_convertida) {
            $data['data_nascimento'] = $data_convertida->format('Y-m-d');
            $hoje = new DateTime();
            if ($data_convertida > $hoje) {
                $erros[] = "A data de nascimento não pode ser no futuro.";
            }
        } else {
            $erros[] = "A data de nascimento é inválida.";
        }
    }
}

if (isset($data['idade']) && isset($data['data_nascimento'])) {
    $data_nascimento = new DateTime($data['data_nascimento']);
    $hoje = new DateTime();
    $idade_calculada = $hoje->diff($data_nascimento)->y;

    if ((int)$data['idade'] !== $idade_calculada) {
        $erros[] = "A idade não condiz com a data de nascimento. Idade calculada: $idade_calculada.";
    }
}

if (isset($data['genero']) && !in_array($data['genero'], ["Masculino", "Feminino", "Outro", "masculino", "feminino", "outro"])) {
    $erros[] = "O gênero deve ser 'Masculino', 'Feminino' ou 'Outro'.";
}

if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $erros[] = "O email deve estar em um formato válido, como exemplo@dominio.com.";
}

if (isset($data['telefone'])) {
    $data['telefone'] = preg_replace('/[^\d]/', '', $data['telefone']); 
    if (!preg_match('/^\d{11}$/', $data['telefone'])) {
        $erros[] = "O telefone deve conter 11 dígitos numéricos (incluindo o DDD).";
    }
}

if (isset($data['endereco']) && !preg_match('/^[\w\s]+,\s*\d+$/', $data['endereco'])) {
    $erros[] = "O endereço deve estar no formato: Nome da Rua, Número.";
}

if (!empty($erros)) {
    echo json_encode(["status" => "erro", "mensagem" => $erros]);
    exit;
}

$set = [];
foreach ($data as $column => $value) {
    $set[] = "$column = '$value'";
}
$setString = implode(", ", $set);
$sql = "UPDATE dados_originais SET $setString WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    date_default_timezone_set('America/Recife');
    $data_atualizacao = date('Y-m-d H:i:s');
    $colunas = implode(", ", array_keys($data));
    $valores = implode("', '", array_values($data));
    $insert_sql = "INSERT INTO dados_editados ($colunas, data_atualizacao) VALUES ('$valores', '$data_atualizacao')";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Dados atualizados e registrados na tabela de edições!"]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao registrar na tabela de edições: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar os dados: " . $conn->error]);
}

$conn->close();
?>