<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <link rel="stylesheet" href="tabela.css"> 
</head>
<body>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "coopanest";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    function formatarTelefone($telefone) {
        return preg_replace("/^(\\d{2})(\\d{5})(\\d{4})$/", "($1)$2-$3", $telefone);
    }

    function formatarCPF($cpf) {
        return preg_replace("/^(\\d{3})(\\d{3})(\\d{3})(\\d{2})$/", "$1.$2.$3-$4", $cpf);
    }

    $sql = "SELECT * FROM dados_originais";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Data de Nascimento</th>
                    <th>Idade</th>
                    <th>Gênero</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                    <th>Ações</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $data_nascimento_formatada = DateTime::createFromFormat('Y-m-d', $row['data_nascimento'])->format('d/m/Y');
            $cpf_formatado = formatarCPF($row['cpf']);
            $telefone_formatado = formatarTelefone($row['telefone']);

            echo "<tr data-id='" . $row['id'] . "'>
                    <td>" . $row['id'] . "</td>
                    <td contenteditable='false' data-column='nome'>" . $row['nome'] . "</td>
                    <td contenteditable='false' data-column='cpf'>" . $cpf_formatado . "</td>
                    <td contenteditable='false' data-column='data_nascimento'>" . $data_nascimento_formatada . "</td>
                    <td contenteditable='false' data-column='idade'>" . $row['idade'] . "</td>
                    <td contenteditable='false' data-column='genero'>" . $row['genero'] . "</td>
                    <td contenteditable='false' data-column='email'>" . $row['email'] . "</td>
                    <td contenteditable='false' data-column='telefone'>" . $telefone_formatado . "</td>
                    <td contenteditable='false' data-column='endereco'>" . $row['endereco'] . "</td>
                    <td>
                        <button class='edit-btn'>Editar</button>
                        <button class='save-btn' style='display:none;'>Salvar</button>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum dado encontrado.</p>";
    }

    $conn->close();
    ?>
    <script>
        function formatarTelefoneAoDigitar(telefone) {
            telefone = telefone.replace(/\D/g, ""); 
            return telefone.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1)$2-$3");
        }

        function formatarCPFAoDigitar(cpf) {
            cpf = cpf.replace(/\D/g, ""); 
            return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
        }

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const row = this.parentElement.parentElement;
                const cells = row.querySelectorAll('[contenteditable="false"]');

                cells.forEach(cell => {
                    const column = cell.getAttribute('data-column');

                    if (column === 'data_nascimento') {
                        const originalValue = cell.textContent.trim();
                        const formattedDate = originalValue.split('/').reverse().join('-');
                        cell.innerHTML = `<input type="date" value="${formattedDate}" style="width: 100%;">`;
                    } else if (column === 'telefone' || column === 'cpf') {
                        const originalValue = cell.textContent.trim();
                        const rawValue = originalValue.replace(/\D/g, "");
                        cell.innerHTML = `<input type="text" value="${rawValue}" style="width: 100%;">`;
                        if (column === 'telefone') {
                            cell.querySelector('input').addEventListener('input', function () {
                                this.value = formatarTelefoneAoDigitar(this.value);
                            });
                        } else if (column === 'cpf') {
                            cell.querySelector('input').addEventListener('input', function () {
                                this.value = formatarCPFAoDigitar(this.value);
                            });
                        }
                    } else {
                        cell.innerHTML = `<input type="text" value="${cell.textContent.trim()}" style="width: 100%;">`;
                    }

                    cell.classList.add('editando');
                });

                row.querySelector('.edit-btn').style.display = 'none';
                row.querySelector('.save-btn').style.display = 'inline-block';
            });
        });

        document.querySelectorAll('.save-btn').forEach(button => {
            button.addEventListener('click', function () {
                const row = this.parentElement.parentElement;
                const id = row.getAttribute('data-id');
                const cells = row.querySelectorAll('[data-column]');
                let data = {};

                cells.forEach(cell => {
                    const column = cell.getAttribute('data-column');

                    if (column === 'data_nascimento') {
                        const inputDate = cell.querySelector('input[type="date"]');
                        if (inputDate) {
                            const formattedDate = inputDate.value.split('-').reverse().join('/'); 
                            data[column] = formattedDate;
                            cell.innerHTML = formattedDate;
                        }
                    } else {
                        const inputField = cell.querySelector('input[type="text"]');
                        if (inputField) {
                            data[column] = inputField.value;
                            cell.innerHTML = inputField.value;
                        }
                    }

                    cell.classList.remove('editando');
                });

                fetch(`atualiza_dados.php?id=${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === "sucesso") {
                        alert(result.mensagem);
                    } else {
                        alert("Erro: " + result.mensagem.join("\n"));
                    }

                    row.querySelector('.edit-btn').style.display = 'inline-block';
                    row.querySelector('.save-btn').style.display = 'none';
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
            });
        });
    </script>
</body>
</html>