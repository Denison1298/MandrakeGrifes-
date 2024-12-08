// Variáveis para manipulação de elementos
const sidebar = document.getElementById("menuLateral");
const overlay = document.getElementById("overlay") || document.createElement("div");
overlay.id = "overlay";
overlay.style.position = "fixed";
overlay.style.top = "0";
overlay.style.left = "0";
overlay.style.width = "100%";
overlay.style.height = "100%";
overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
overlay.style.zIndex = "999";
overlay.style.display = "none";
document.body.appendChild(overlay);

// Inicializar o carrinho
let cart = [];

// Mostrar ou esconder o overlay
function toggleOverlay(show) {
    overlay.style.display = show ? "block" : "none";
}

// Abrir a barra lateral
function openNav() {
    sidebar.style.width = "250px";
    toggleOverlay(true);
}

// Fechar a barra lateral
function closeNav() {
    sidebar.style.width = "0";
    toggleOverlay(false);
}

// Fechar ao clicar fora da barra lateral
overlay.addEventListener("click", closeNav);

// Botão de fechar na barra lateral
document.getElementById("closebtn").addEventListener("click", closeNav);

// Funcionalidade de abas de produtos
function openTab(evt, tabName) {
    const tabs = document.querySelectorAll(".aba");
    tabs.forEach(tab => tab.style.display = "none");

    const tablinks = document.querySelectorAll(".tablink");
    tablinks.forEach(link => link.classList.remove("active"));

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}

// Adicionar ao carrinho
function adicionarAoCarrinho(produtoId, nome, preco) {
    const tamanhoSelect = document.querySelector(`.select-tamanho[data-id="${produtoId}"]`);
    const corSelect = document.querySelector(`.select-cor[data-id="${produtoId}"]`);

    const tamanho = tamanhoSelect ? tamanhoSelect.value : "";
    const cor = corSelect ? corSelect.value : "";

    if (!nome || !preco) {
        alert("Dados do produto inválidos.");
        return;
    }

    const produto = { id: produtoId, nome, preco, tamanho, cor };
    cart.push(produto);
    atualizarCarrinho();

    alert(`Produto "${nome}" foi adicionado ao carrinho!`);
}

// Atualizar contador do carrinho
function atualizarCarrinho() {
    const contadorCarrinho = document.getElementById("contadorCarrinho");
    const contadorCarrinhoFlutuante = document.getElementById("contadorCarrinhoFlutuante");
    contadorCarrinho.textContent = cart.length;
    contadorCarrinhoFlutuante.textContent = cart.length;
}

// Abrir o carrinho e mostrar os produtos
function abrirCarrinho() {
    const carrinhoDiv = document.getElementById("carrinhoDetalhes") || document.createElement("div");
    carrinhoDiv.id = "carrinhoDetalhes";
    carrinhoDiv.style.position = "fixed";
    carrinhoDiv.style.top = "50%";
    carrinhoDiv.style.left = "50%";
    carrinhoDiv.style.transform = "translate(-50%, -50%)";
    carrinhoDiv.style.backgroundColor = "#fff";
    carrinhoDiv.style.border = "1px solid #ccc";
    carrinhoDiv.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
    carrinhoDiv.style.padding = "20px";
    carrinhoDiv.style.zIndex = "1000";

    let html = "<h3>Produtos no Carrinho</h3>";
    if (cart.length === 0) {
        html += "<p>O carrinho está vazio!</p>";
    } else {
        cart.forEach((produto, index) => {
            html += `
                <div>
                    <strong>${index + 1}. ${produto.nome}</strong>
                    <p>Preço: R$ ${produto.preco.toFixed(2)}</p>
                    <p>Tamanho: ${produto.tamanho || "Não selecionado"}</p>
                    <p>Cor: ${produto.cor || "Não selecionado"}</p>
                    <hr>
                </div>
            `;
        });
    }

    html += '<button onclick="fecharCarrinho()">Fechar</button>';
    carrinhoDiv.innerHTML = html;
    document.body.appendChild(carrinhoDiv);

    toggleOverlay(true); // Mostrar overlay
}

// Fechar carrinho
function fecharCarrinho() {
    const carrinhoDiv = document.getElementById("carrinhoDetalhes");
    if (carrinhoDiv) {
        document.body.removeChild(carrinhoDiv);
    }
    toggleOverlay(false); // Esconder overlay
}

// Inicializar aba padrão
document.addEventListener("DOMContentLoaded", () => {
    const defaultTab = document.querySelector(".tablink");
    if (defaultTab) {
        defaultTab.click();
    }
});
