<?php
include 'config/conexao.php'; // Inclua sua conexão com o banco de dados

// Buscar todas as abas
$queryAbas = $conn->query("SELECT * FROM abas ORDER BY nome_aba");

// Criar um array para armazenar as abas
$abas = [];
while ($aba = $queryAbas->fetch_assoc()) {
    $abas[] = $aba;
}

// Buscar os produtos e associar com o aba_id
$queryProdutos = $conn->query("
    SELECT p.*, a.nome_aba 
    FROM produtos p
    JOIN abas a ON p.aba_id = a.id
    ORDER BY a.nome_aba, p.id DESC
");

// Agrupar produtos por aba_id
$produtosPorAba = [];
while ($produto = $queryProdutos->fetch_assoc()) {
    $abaId = $produto['aba_id'];
    if (!isset($produtosPorAba[$abaId])) {
        $produtosPorAba[$abaId] = [];
    }
    $produtosPorAba[$abaId][] = $produto;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandrake Grifes Site</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/produtos.css">
    <link rel="stylesheet" href="css/celular.css">
    <link rel="icon" href="img/icon.png" type="image/x-icon">
</head>
<body>
<header>
    <h1>Mandrake Grifes</h1>
    <nav>
        <ul>
            <li><a href="index.html">Início</a></li>
            <li><a href="produtos.html">Produtos</a></li>
            <li><a href="historia.html">Nossa História</a></li>
            <li>
                <a href="javascript:void(0);" onclick="abrirCarrinho()">
                    <img src="img/SACOLA.png" alt="Carrinho">
                    <span id="contadorCarrinho">0</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- Botão flutuante do carrinho -->
<div id="carrinhoFlutuante" onclick="abrirCarrinho()">
    <img src="img/SACOLA.png" alt="Carrinho">
    <span id="contadorCarrinhoFlutuante">0</span>
</div>

<main>
    <section id="produtos">
        <h2>Nossos Produtos</h2>

        <!-- Barra Lateral Flutuante -->
        <div id="menuLateral" class="sidebar">
            <a href="javascript:void(0)" class="closebtn" id="closebtn">&times;</a>
            <a href="index.html">Início</a>

            <!-- Gerando as abas dinamicamente com PHP -->
            <?php foreach ($abas as $aba): ?>
                <button class="tablink" onclick="openTab(event, '<?php echo $aba['nome_aba']; ?>')"><?php echo $aba['nome_aba']; ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Botão para abrir a barra lateral -->
        <span id="openBtn" class="openbtn">&#9776; </span>

        <!-- Abas de Produtos -->
        <div class="abas-conteudo">
            <!-- Aqui serão carregados os produtos conforme a aba selecionada -->
            <?php foreach ($abas as $aba): ?>
                <div id="<?php echo $aba['nome_aba']; ?>" class="tabcontent"></div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<!-- Modal Carrinho -->
<div id="carrinhoModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharCarrinho()">&times;</span>
        <h2>Itens do Carrinho</h2>
        <div id="listaCarrinho" class="lista-carrinho"></div>
        <p id="totalCarrinho"></p>

        <h3>Opção de Entrega ou Retirada</h3>
        <label>
            <input type="radio" name="opcaoEntrega" value="entrega" onclick="toggleEndereco()"> Entrega
        </label>
        <label>
            <input type="radio" name="opcaoEntrega" value="retirada" onclick="toggleEndereco()"> Retirada
        </label>

        <!-- Formulário de Endereço para Entrega -->
        <div class="form-endereco" style="display: none;">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" placeholder="Digite seu CEP" onblur="calcularTaxaEntrega()" required>

            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" placeholder="Nome da Rua" readonly>

            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" placeholder="Bairro" readonly>

            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" placeholder="Número da Casa">

            <label for="referencia">Ponto de Referência (opcional):</label>
            <input type="text" id="referencia" name="referencia" placeholder="Ponto de Referência">

            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" placeholder="Cidade" readonly>

            <label for="taxaEntrega">Taxa de Entrega:</label>
            <input type="text" id="taxaEntrega" name="taxaEntrega" placeholder="Valor da entrega" readonly>
            
            <!-- Informações para Retirada na Loja -->
            <div id="infoRetirada" style="display: none;">
                <h4>Retirada na Loja</h4>
                <p>Endereço: R. Cachoeira do Campo, 1676 - Campo Grande, MS, 79096-703</p>
                <p>Horário de Funcionamento: 9:00h às 19:30h</p>
            </div>

            <!-- Botões de Ação -->
            <button id="finalizarCompra" onclick="finalizarCompra()">Finalizar Compra</button>
            <button onclick="limparCarrinho()">Limpar Carrinho</button>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2019 Mandrake Grifes</p>
</footer>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqK3ZpAY0LdyiEzCwpOHQIbDdfaz9LnFU&libraries=places"></script>
<script src="script.js"></script>
<script>
    const abas = <?php echo json_encode($abas); ?>;
    const produtosPorAba = <?php echo json_encode($produtosPorAba); ?>;

    function openTab(evt, abaNome) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("ativa");
        }
        document.getElementById(abaNome).style.display = "block";
        evt.currentTarget.classList.add("ativa");
        loadProdutosDaAba(abaNome);
    }

    function loadProdutosDaAba(abaNome) {
        const aba = abas.find(aba => aba.nome_aba === abaNome);
        const produtos = produtosPorAba[aba.id] || [];
        const container = document.getElementById(abaNome);
        container.innerHTML = '';
        produtos.forEach(function(produto) {
            var produtoDiv = document.createElement('div');
            produtoDiv.classList.add('produto');
            let tamanhoSelect = produto.tamanho ? `
                <label for="tamanho_${produto.id}">Tamanho:</label>
                <select id="tamanho_${produto.id}" class="selectTamanho">
                    ${produto.tamanho.split(',').map(t => `<option value="${t.trim()}">${t.trim()}</option>`).join('')}
                </select>
            ` : '';
            let corSelect = produto.cor ? `
                <label for="cor_${produto.id}">Cor:</label>
                <select id="cor_${produto.id}" class="selectCor">
                    ${produto.cor.split(',').map(c => `<option value="${c.trim()}">${c.trim()}</option>`).join('')}
                </select>
            ` : '';
            produtoDiv.innerHTML = `
                <img src="${produto.imagem}" alt="${produto.nome}">
                <h3>${produto.nome}</h3>
                <p>Preço: R$ ${produto.preco}</p>
                ${tamanhoSelect}
                ${corSelect}
                <button onclick="adicionarAoCarrinho(${produto.id}, '${produto.nome}', ${produto.preco})">Adicionar ao Carrinho</button>
            `;
            container.appendChild(produtoDiv);
        });
    }

    function adicionarAoCarrinho(id, nome, preco) {
        let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
        carrinho.push({ id, nome, preco });
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        atualizarCarrinho();
    }

    function atualizarCarrinho() {
        let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
        let contador = carrinho.length;
        document.getElementById('contadorCarrinho').innerText = contador;
        document.getElementById('contadorCarrinhoFlutuante').innerText = contador;
    }

    // Atualizar carrinho ao carregar a página
    window.onload = atualizarCarrinho;
</script>
</body>
</html>
