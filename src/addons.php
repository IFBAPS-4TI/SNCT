<?php
/*
 * NÃO EDITAR ESSE ARQUIVO. Adicione os arquivos na pasta "controllers"
 */
foreach (glob(__DIR__ . '/../src/models/*.php') as $filename) {
    include $filename;
}
foreach (glob(__DIR__ . '/../src/controllers/*.php') as $filename) {
    include $filename;
}

