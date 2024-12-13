<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="formulario.css">

    <script>
    function validarFormulario(event) {
        const nomeField = document.querySelector('input[name="nome"]');
        const cpfField = document.querySelector('input[name="cpf"]');
        const dataNascimentoField = document.querySelector('input[name="data_nascimento"]');
        const idadeField = document.querySelector('input[name="idade"]');
        const emailField = document.querySelector('input[name="email"]');
        const telefoneField = document.querySelector('input[name="telefone"]');
        const enderecoField = document.querySelector('input[name="endereco"]');
        let isValid = true;

        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(nomeField.value.trim())) {
            nomeField.setCustomValidity("O Nome deve conter apenas letras e espaços.");
            isValid = false;
        } else {
            nomeField.setCustomValidity(""); 
        }

        const cpfValue = cpfField.value.replace(/\D/g, "");
        const telefoneValue = telefoneField.value.replace(/\D/g, "");

        if (!/^\d{11}$/.test(cpfValue)) {
            cpfField.setCustomValidity("O CPF deve conter 11 dígitos numéricos.");
            isValid = false;
        } else {
            cpfField.setCustomValidity(""); 
        }

        if (!/^\d{11}$/.test(telefoneValue)) {
            telefoneField.setCustomValidity("O telefone deve conter 11 dígitos numéricos (incluindo o DDD).");
            isValid = false;
        } else {
            telefoneField.setCustomValidity(""); 
        }

        if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailField.value)) {
            emailField.setCustomValidity("O email deve estar em um formato válido, como exemplo@dominio.com.");
            isValid = false;
        } else {
            emailField.setCustomValidity(""); 
        }

        if (!/^[a-zA-ZÀ-ÿ\s]+,\s*\d+(\s*[,|\s][a-zA-ZÀ-ÿ\s]*)?$/.test(enderecoField.value)) {
            enderecoField.setCustomValidity("O endereço deve estar no formato: Nome da Rua, Número.");
            isValid = false;
        } else {
            enderecoField.setCustomValidity(""); 
        }

        const dataNascimento = new Date(dataNascimentoField.value);
        const hoje = new Date();
        if (dataNascimento > hoje) {
            dataNascimentoField.setCustomValidity("A data de nascimento não pode ser uma data futura.");
            isValid = false;
        } else {
            dataNascimentoField.setCustomValidity("");
        }

        const idadeInserida = parseInt(idadeField.value, 10);
        const idadeCalculada = hoje.getFullYear() - dataNascimento.getFullYear();
        const aniversarioEsteAno = new Date(hoje.getFullYear(), dataNascimento.getMonth(), dataNascimento.getDate());
        if (hoje < aniversarioEsteAno) {
            idadeCalculada--; 
        }

        if (idadeInserida !== idadeCalculada) {
            idadeField.setCustomValidity("A idade não condiz com a data de nascimento.");
            isValid = false;
        } else {
            idadeField.setCustomValidity("");
        }

        cpfField.value = cpfValue;
        telefoneField.value = telefoneValue;

        return isValid;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const nomeField = document.querySelector('input[name="nome"]');
        const cpfField = document.querySelector('input[name="cpf"]');
        const telefoneField = document.querySelector('input[name="telefone"]');
        const dataNascimentoField = document.querySelector('input[name="data_nascimento"]');
        const idadeField = document.querySelector('input[name="idade"]');
        const emailField = document.querySelector('input[name="email"]');
        const enderecoField = document.querySelector('input[name="endereco"]');

        nomeField.addEventListener("input", function () {
            if (/^[a-zA-ZÀ-ÿ\s]+$/.test(this.value.trim())) {
                this.setCustomValidity("");
            }
        });

        //formatação automática cpf
        cpfField.addEventListener("input", function () {
            let value = this.value.replace(/\D/g, ""); 
            value = value.replace(/^(\d{3})(\d)/, "$1.$2"); 
            value = value.replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3"); 
            value = value.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d{2})$/, "$1.$2.$3-$4"); 
            this.value = value; 
            if (/^\d{11}$/.test(value.replace(/\D/g, ""))) {
                this.setCustomValidity("");
            }
        });

        //formatação automática telefone
        telefoneField.addEventListener("input", function () {
            let value = this.value.replace(/\D/g, ""); 
            value = value.replace(/^(\d{2})(\d)/, "($1) $2"); 
            value = value.replace(/(\d{5})(\d{4})$/, "$1-$2"); 
            this.value = value;
            if (/^\d{11}$/.test(value.replace(/\D/g, ""))) {
                this.setCustomValidity("");
            }
        });

        dataNascimentoField.addEventListener("input", function () {
            const hoje = new Date();
            const dataNascimento = new Date(this.value);
            if (dataNascimento > hoje) {
                this.setCustomValidity("A data de nascimento não pode ser no futuro.");
            } else {
                this.setCustomValidity(""); 
            }

            const idadeCalculada = hoje.getFullYear() - dataNascimento.getFullYear();
            if (dataNascimento > hoje.setFullYear(hoje.getFullYear() - idadeCalculada)) {
                idadeCalculada--;
            }
            if (idadeField.value && parseInt(idadeField.value, 10) !== idadeCalculada) {
                idadeField.setCustomValidity("A idade não condiz com a data de nascimento.");
            } else {
                idadeField.setCustomValidity(""); 
            }
        });

        idadeField.addEventListener("input", function () {
            const dataNascimento = new Date(dataNascimentoField.value);
            const hoje = new Date();
            const idadeCalculada = hoje.getFullYear() - dataNascimento.getFullYear();
            if (dataNascimento > hoje.setFullYear(hoje.getFullYear() - idadeCalculada)) {
                idadeCalculada--;
            }
            if (parseInt(this.value, 10) !== idadeCalculada) {
                this.setCustomValidity("A idade não condiz com a data de nascimento.");
            } else {
                this.setCustomValidity(""); 
            }
        });

        emailField.addEventListener("input", function () {
            if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(this.value)) {
                this.setCustomValidity("O email deve estar em um formato válido, como exemplo@dominio.com.");
            } else {
                this.setCustomValidity(""); 
            }
        });

        enderecoField.addEventListener("input", function () {
            if (!/^[a-zA-Z\s]+,\s*\d+(\s*[,|\s][a-zA-Z\s]*)?$/.test(this.value)) {
                this.setCustomValidity("O endereço deve estar no formato: Nome da Rua, Número.");
            } else {
                this.setCustomValidity(""); 
            }
        });
    });
</script>

</head>
<body>
    <h1>Cadastro de Usuário</h1>
    <form action="processa_formulario.php" method="POST" onsubmit="return validarFormulario(event)">
    <label>Nome:</label><br>
    <input type="text" name="nome" required><br>

    <label>CPF:</label><br>
    <input type="text" name="cpf" required><br>

    <label>Data de Nascimento:</label><br>
    <input type="date" name="data_nascimento" required><br>

    <label>Idade:</label><br>
    <input type="number" name="idade" required><br>

    <label>Gênero:</label><br>
    <select name="genero" required>
        <option value="Masculino">Masculino</option>
        <option value="Feminino">Feminino</option>
        <option value="Outro">Outro</option>
    </select><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br>

    <label>Telefone:</label><br>
    <input type="text" name="telefone" required><br>

    <label>Endereço:</label><br>
    <input type="text" name="endereco" required><br>

    <button type="submit">Cadastrar</button>
</form>
</body>
</html>