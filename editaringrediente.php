<?php
include "conexao.php";
require_once "require_login.php";
include "usuario_info.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

ob_start(); // Evita erros de cabeçalho

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produto inválido.";
    exit;
}

$id_ingrediente = intval($_GET['id']);
$mensagem = "";
$houveAlteracao = false;
$redirecionar = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];
    $categorias = $_POST['categorias'] ?? [];
    
    // Verifica duplicidade de nome (exceto o próprio)
    $verifica = $conexao->prepare("SELECT COUNT(*) FROM ingrediente WHERE nome_ingrediente = ? AND id_ingrediente != ?");
    $verifica->bind_param("si", $nome, $id_ingrediente);
    $verifica->execute();
    $verifica->bind_result($existe);
    $verifica->fetch();
    $verifica->close();

    if ($existe > 0) {
        echo "<p style='color:red;'>Já existe um ingrediente com esse nome.</p>";
    }
    else{

        $categorias_associadas = [];
        $res = $conexao->query("SELECT id_categoriadoingrediente FROM categoriadoingrediente_ingrediente WHERE id_ingrediente = $id_ingrediente");
        while ($linha = $res->fetch_assoc()) {
            $categorias_associadas[] = $linha['id_categoriadoingrediente'];
        }
        
        // 1. Atualiza dados do ingrediente
        $disponibilidade = ($quantidade > 0) ? 'Disponível' : 'Indisponível';
        $stmt = $conexao->prepare("UPDATE ingrediente SET nome_ingrediente=?, descricao=?, preco_adicional=?, quantidade_estoque=?, disponibilidade=? WHERE id_ingrediente=?");
        $stmt->bind_param("ssdisi", $nome, $descricao, $preco, $quantidade, $disponibilidade, $id_ingrediente);
        $stmt->execute();

        // 2. Atualiza as categorias associadas
        $conexao->query("DELETE FROM categoriadoingrediente_ingrediente WHERE id_ingrediente= $id_ingrediente");

        foreach ($categorias as $id_categoriadoingrediente) {
            $insere = $conexao->prepare("INSERT INTO categoriadoingrediente_ingrediente (id_categoriadoingrediente, id_ingrediente) VALUES (?, ?)");
            $insere->bind_param("ii", $id_categoriadoingrediente, $id_ingrediente);
            $insere->execute();
        }


        // Pega lista de categorias atualizada no banco
        $categorias_novas_banco = [];
        $res = $conexao->query("SELECT id_categoriadoingrediente FROM categoriadoingrediente_ingrediente WHERE id_ingrediente = $id_ingrediente");
        while ($linha = $res->fetch_assoc()) {
            $categorias_novas_banco[] = $linha['id_categoriadoingrediente'];
        }

        if ($categorias_novas_banco != $categorias_associadas) {
            $houveAlteracao = true;
        }

        if ($stmt->affected_rows > 0) {
            $houveAlteracao = true;
        }

        // 3. Atualiza imagem principal existente
        if (isset($_POST['imagem_principal'])) {
            $img_principal = intval($_POST['imagem_principal']);

            // Verifica a imagem principal atual
            $resAtual = $conexao->query("SELECT id_imagem FROM ingrediente_imagem WHERE id_ingrediente = $id_ingrediente AND imagem_principal = 1");
            $atual = $resAtual->fetch_assoc();

            if (!$atual || $atual['id_imagem'] != $img_principal) {
                // Desmarca a antiga e marca a nova
                $conexao->query("UPDATE ingrediente_imagem SET imagem_principal = 0 WHERE id_ingrediente = $id_ingrediente");
                $conexao->query("UPDATE ingrediente_imagem SET imagem_principal = 1 WHERE id_imagem = $img_principal");
                $houveAlteracao = true;
            }
        }
        
        // **VARIÁVEL DE CONTROLE PARA NOVAS IMAGENS**
        $primeira_nova_imagem = true;
        
        // 4. Adiciona novas imagens
        foreach ($_FILES['imagens']['tmp_name'] as $index => $tmp_name) {
            if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                
                $nome_arquivo = basename($_FILES['imagens']['name'][$index]);
                $destino = "uploads/" . time() . "_" . $nome_arquivo;

                if (move_uploaded_file($tmp_name, $destino)) {
                    $legenda = $_POST['legenda'][$index] ?? '';
                    $imagem_principal = 0;

                    // **LÓGICA IMPLEMENTADA AQUI:**
                    // Se o usuário não definiu uma imagem principal existente OU nova (via POST['imagem_principal']),
                    // E esta for a primeira nova imagem enviada, defina-a como principal.
                    
                    // Verifica se já existe alguma imagem principal no banco, se não existir, a primeira nova se torna principal.
                    $resPrincipal = $conexao->query("SELECT COUNT(*) FROM ingrediente_imagem WHERE id_ingrediente = $id_ingrediente AND imagem_principal = 1");
                    $rowPrincipal = $resPrincipal->fetch_row();
                    $temPrincipal = $rowPrincipal[0] > 0;
                    
                    if (!$temPrincipal && $primeira_nova_imagem) {
                        $imagem_principal = 1;
                    }
                    
                    $stmt_img = $conexao->prepare("INSERT INTO ingrediente_imagem (id_ingrediente, caminho_imagem, legenda, imagem_principal) VALUES (?, ?, ?, ?)");
                    $stmt_img->bind_param("issi", $id_ingrediente, $destino, $legenda, $imagem_principal);
                    $stmt_img->execute();
                    $houveAlteracao = true;
                    
                    // Marca que a primeira nova imagem foi processada (evita que as próximas também virem principal)
                    $primeira_nova_imagem = false;
                }
            }
        }

        if ($houveAlteracao) {
            $mensagem = "✅ingrediente atualizado com sucesso!";
            $redirecionar = true;
        } elseif (!empty($_GET['imagemRemovida'])) {
            $mensagem = " Imagem removida com sucesso!";
            $redirecionar = true;
        } else {
            $mensagem = "ℹ️ Nenhuma modificação foi feita.";
        }

    }
}
// --- Fim do processamento POST ---

// 5. Carrega dados para exibição (mesmo se o POST falhar)
$stmt = $conexao->prepare("SELECT * FROM ingrediente WHERE id_ingrediente= ?");
$stmt->bind_param("i", $id_ingrediente);
$stmt->execute();
$resultado = $stmt->get_result();
$ingrediente = $resultado->fetch_assoc();

$imagens = $conexao->query("SELECT * FROM ingrediente_imagem WHERE id_ingrediente = $id_ingrediente");


// Ingredientes associados
$categorias_associadas = [];
$res = $conexao->query("SELECT id_categoriadoingrediente FROM categoriadoingrediente_ingrediente WHERE id_ingrediente = $id_ingrediente");
while ($linha = $res->fetch_assoc()) {
    $categorias_associadas[] = $linha['id_categoriadoingrediente'];
}

// Lista de todos os Ingredientes
$categorias = $conexao->query("SELECT * FROM categoriadoingrediente");

?>



<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"  content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editar ingrediente</title>
        <script src="logout_auto.js"></script>

    <link rel="stylesheet" href="css/admin.css">
          <script src="js/darkmode2.js"></script>
               <script src="js/sidebar.js"></script>
               <script src="js/dropdown2.js"></script>
</head>
<body>
    
<button class="menu-btn">☰</button>

<div class="sidebar-overlay"></div>
    
<sidebar class="sidebar">
    <br><br>
             
            
            <a href="ver_ingredientes.php">Voltar aos ingredientes</a>
            
 <div class="sidebar-user-wrapper">

    <div class="sidebar-user" id="usuarioDropdown">

        <div class="usuario-avatar" style="background-color: <?= $corAvatar ?>;">
            <?= $iniciais ?>
        </div>

        <div class="usuario-dados">
            <div class="usuario-nome"><?= $nome ?></div>
            <div class="usuario-apelido"><?= $apelido ?></div>
        </div>

        <div class="usuario-menu" id="menuPerfil">
            <a href='editarusuario.php?id_usuario=<?= $usuario['id_usuario'] ?>'>
            <img class="icone" src="icones/user1.png" alt="Editar" title="Editar">     
            Editar Dados Pessoais</a>
            <a href="alterar_senha2.php">
            <img class="icone" src="icones/cadeado1.png" alt="Alterar" title="Alterar">     
            Alterar Senha</a>
            <a href="logout.php">
            <img class="iconelogout" src="icones/logout1.png" alt="Logout" title="Sair">     
            Sair</a>
        </div>

    </div>

    <img class="dark-toggle" id="darkToggle"
          src="icones/lua.png"
          alt="Modo Escuro"
          title="Alternar modo escuro">
</div>

        </sidebar>

<div class="conteudo">

    <?php if ($mensagem): ?>
        <div class="mensagem <?= str_contains($mensagem, '✅') ? 'success' : 'error' ?>">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>
    <h2>Editar ingrediente</h2>

    <form method="post" enctype="multipart/form-data">
        <label>Nome:</label><input type="text" name="nome" value="<?= htmlspecialchars($ingrediente['nome_ingrediente']) ?>" required><br>
        <label>Descrição:</label><textarea name="descricao" required><?= htmlspecialchars($ingrediente['descricao']) ?></textarea><br>
        <label>Preço:</label><input type="number" step="0.01" name="preco" value="<?= $ingrediente['preco_adicional'] ?>" required><br>
        <label>Quantidade:</label><input type="number" name="quantidade" value="<?= $ingrediente['quantidade_estoque'] ?>" required><br>
        
    
    <label>Categorias Associadas:</label><br>
    
<div class="ingredientes-container">
          <?php
$sql = "SELECT * from categoriadoingrediente ORDER BY id_categoriadoingrediente DESC";
$resultado = $conexao->query($sql);

while ($categoria = $resultado->fetch_assoc()) {
    
    $checked = in_array($categoria['id_categoriadoingrediente'], $categorias_associadas) ? 'checked' : '';
    
      echo "<div class='ingrediente-card'>";
    echo "  <label>";
    echo "    <input type='checkbox' name='categorias[]' value='{$categoria['id_categoriadoingrediente']}' class='ingrediente-checkbox' {$checked}> {$categoria['nome_categoriadoingrediente']}";
    echo "  </label>";

    echo "</div>";
}
?>  
</div >
    

    
        

        <h4>Imagens Existentes</h4>
        <?php 
        // Reinicializa o ponteiro para o início do resultado para reexibir (necessário após o processamento POST ter acessado o objeto $imagens, se for o caso)
        if ($imagens) {
            $imagens->data_seek(0);
        }
        while ($img = $imagens->fetch_assoc()): 
        ?>
            <div>
                <img src="<?= $img['caminho_imagem'] ?>" alt="Imagem" width="100"><br>
                <label>Legenda: <?= htmlspecialchars($img['legenda']) ?></label><br>
                <label>
                    Principal?
                    <input type="radio" name="imagem_principal" value="<?= $img['id_imagem'] ?>" <?= $img['imagem_principal'] ? 'checked' : '' ?>>
                </label><br>
            <a href="remover_imagem_ingrediente.php?id_imagem=<?= $img['id_imagem'] ?>&id_ingrediente=<?= $id_ingrediente ?>" 
   onclick="return confirm('Deseja remover esta imagem?')">
   <button type="button" id="remove">Remover a imagem</button>
</a>

                <hr>
            </div>
        <?php endwhile; ?>

        <h4>Adicionar Novas Imagens</h4>
        <div id="novas-imagens">
            <input type="file" name="imagens[]">
            <input type="text" name="legenda[]" placeholder="Legenda da nova imagem"><br>
        </div>
        <button type="button" onclick="adicionarCampoImagem()">+ Adicionar mais imagens</button><br><br>

        <button class="editar" type="submit">Atualizar Produto</button>

    </form>
    
          <?php if ($redirecionar): ?>
              <script>
    // Redireciona em 3 segundos
    setTimeout(() => {
        window.location.href = 'ver_ingredientes.php';
    }, 3000);
</script>
    <?php endif; ?>
    </div>

    <script>
        function adicionarCampoImagem() {
            const container = document.getElementById('novas-imagens');
            const index = container.children.length; // O índice aqui não é usado para radio button, mas pode ser útil no futuro.

            const div = document.createElement('div');
            // O código original do JS não adicionava o rádio button para as novas imagens no editar, 
            // mas o PHP pode ser adaptado para lidar com o upload sem ele, usando a lógica de "se não houver principal".
            div.innerHTML = `
                <input type="file" name="imagens[]">
                <input type="text" name="legenda[]" placeholder="Legenda da nova imagem"><br>
            `;
            container.appendChild(div);
        }

        
document.querySelectorAll('.ingrediente-checkbox').forEach(checkbox => {
    const card = checkbox.closest('.ingrediente-card');
    if (checkbox.checked) {
        card.classList.add('selecionado');
    }
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            card.classList.add('selecionado');
        } else {
            card.classList.remove('selecionado');
        }
    });
});

      
    </script>

</body>
</html>
