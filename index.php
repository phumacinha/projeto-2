    <?php

    use Pedro\Projeto2;

    $pessoa = new Pedro\Projeto2\Pessoa('Pedro Ssssss', 30, Projeto2\Funcao::Dev);

    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Teste Hot Reload</title>
    </head>

    <body>
        <h2>Dados da Pessoa</h2>
        <p><?php echo $pessoa; ?></p>
        <p>Nome: <?php echo $pessoa->nome; ?></p>
    </body>

    </html>