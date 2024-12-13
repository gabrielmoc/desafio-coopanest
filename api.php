<?php
header("Content-Type: application/json");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coopanest";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["erro" => "Falha na conexão com o banco de dados"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($_SERVER['PATH_INFO'], '/'));

if ($uri[0] !== "dados") {
    http_response_code(404);
    echo json_encode(["erro" => "Endpoint não encontrado"]);
    exit;
}

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM dados_originais";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $dados = [];
            while ($row = $result->fetch_assoc()) {
                $row['data_nascimento'] = DateTime::createFromFormat('Y-m-d', $row['data_nascimento'])->format('d/m/Y');
                $dados[] = $row;
            }
            echo json_encode($dados);
        } else {
            echo json_encode([]);
        }
        break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $erros = validarDados($data);
        
            if (!empty($erros)) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => $erros]);
                exit;
            }
        
            $data['cpf'] = preg_replace('/[^\d]/', '', $data['cpf']);
            $data['telefone'] = preg_replace('/[^\d]/', '', $data['telefone']);
        
            if (!empty($data['data_nascimento'])) {
                $dataConvertida = DateTime::createFromFormat('d/m/Y', $data['data_nascimento']);
                if ($dataConvertida) {
                    $data['data_nascimento'] = $dataConvertida->format('Y-m-d');
                } else {
                    http_response_code(400);
                    echo json_encode(["status" => "erro", "mensagem" => "A data de nascimento deve estar no formato dd/mm/yyyy."]);
                    exit;
                }
            }
        
            $sql = "INSERT INTO dados_originais (nome, idade, email, telefone, endereco, data_nascimento, cpf, genero)
                    VALUES ('{$data['nome']}', '{$data['idade']}', '{$data['email']}', '{$data['telefone']}', '{$data['endereco']}', '{$data['data_nascimento']}', '{$data['cpf']}', '{$data['genero']}')";
        
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["status" => "sucesso", "mensagem" => "Dados cadastrados com sucesso"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "erro", "mensagem" => "Erro ao inserir os dados: " . $conn->error]);
            }
            break;
        

        case 'PUT':
            if (!isset($uri[1]) || !is_numeric($uri[1])) {
                http_response_code(400);
                echo json_encode(["erro" => "ID inválido"]);
                exit;
            }
            $id = $uri[1];
            $data = json_decode(file_get_contents("php://input"), true);
            $erros = validarDados($data);
        
            if (!empty($erros)) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => $erros]);
                exit;
            }
        
            $data['cpf'] = preg_replace('/[^\d]/', '', $data['cpf']);
            $data['telefone'] = preg_replace('/[^\d]/', '', $data['telefone']);
        
            if (!empty($data['data_nascimento'])) {
                $dataConvertida = DateTime::createFromFormat('d/m/Y', $data['data_nascimento']);
                if ($dataConvertida) {
                    $data['data_nascimento'] = $dataConvertida->format('Y-m-d');
                } else {
                    http_response_code(400);
                    echo json_encode(["status" => "erro", "mensagem" => "A data de nascimento deve estar no formato dd/mm/yyyy."]);
                    exit;
                }
            }
        
            $set = [];
            foreach ($data as $column => $value) {
                $set[] = "$column = '$value'";
            }
            $setString = implode(", ", $set);
            $sqlUpdate = "UPDATE dados_originais SET $setString WHERE id = $id";
        
            if ($conn->query($sqlUpdate) === TRUE) {
                $timestamp = date('Y-m-d H:i:s');
                $sqlInsert = "INSERT INTO dados_editados (id_usuario, nome, cpf, data_nascimento, idade, genero, email, telefone, endereco, data_atualizacao)
                              VALUES ('$id', '{$data['nome']}', '{$data['cpf']}', '{$data['data_nascimento']}', '{$data['idade']}', 
                              '{$data['genero']}', '{$data['email']}', '{$data['telefone']}', '{$data['endereco']}', '$timestamp')";
                $conn->query($sqlInsert);
        
                echo json_encode(["status" => "sucesso", "mensagem" => "Dados atualizados com sucesso"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar os dados: " . $conn->error]);
            }
            break;

    default:
        http_response_code(405);
        echo json_encode(["erro" => "Método não permitido"]);
        break;
}

$conn->close();

function validarDados($data) {
    $erros = [];

    if (empty($data['nome']) || !preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $data['nome'])) {
        $erros[] = "O nome deve conter apenas letras e espaços";
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email deve estar em um formato válido";
    }

    if (!isset($data['cpf']) || !preg_match('/^\d{11}$/', preg_replace('/[^\d]/', '', $data['cpf']))) {
        $erros[] = "O CPF deve conter 11 dígitos numéricos";
    }

    if (!isset($data['telefone']) || !preg_match('/^\d{11}$/', preg_replace('/[^\d]/', '', $data['telefone']))) {
        $erros[] = "O telefone deve conter 11 dígitos numéricos";
    }

    if (!isset($data['data_nascimento']) || !DateTime::createFromFormat('d/m/Y', $data['data_nascimento'])) {
        $erros[] = "A data de nascimento deve estar no formato dd/mm/yyyy";
    }

    if (!isset($data['idade']) || !is_numeric($data['idade']) || (int)$data['idade'] <= 0) {
        $erros[] = "A idade deve ser um número inteiro maior que 0";
    }

    if (!isset($data['genero']) || !in_array($data['genero'], ["Masculino", "Feminino", "Outro"])) {
        $erros[] = "O gênero deve ser Masculino, Feminino ou Outro";
    }

    return $erros;
}
?>