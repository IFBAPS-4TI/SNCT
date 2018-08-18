# SNCT

*Sistema de Gerenciamento para a SNCT*

[![License](https://img.shields.io/badge/license-Apache2.0-blue.svg)](https://github.com/IFBAPS-4TI/SNCT/blob/master/LICENSE)

***
## Instalando

Depende de:
* [Composer](https://getcomposer.org/download/)
* [PHP 5](http://php.net/downloads.php) (ou superior)


### Executando
Instale as dependências do Composer e inicie o servidor virtual:
```sh
composer install
php -S 0.0.0.0:8080 -t public
```
### Outras plataformas
Caso esteja utilizando Nginx, Apache ou outro servidor HTTP, aponte o `public_html` para a pasta `public` do projeto.
 
Não é necessário mover as outras pastas da raiz do projeto já que estas não serão acessadas pelo usuário e podem ser acessadas pelo PHP sem necessariamente serem acessíveis pelo Servidor HTTP.

## Licença
    Sistema de Gerenciamento para a SNCT (IFBA - Campus Porto Seguro)
    Copyright (C) 2018 Ethann Jin Sun Erickson, Guilherme Scaranse, Moisés Augusto, Marcos Davi Florêncio e Santhiago Rocha
    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at
    
        http://www.apache.org/licenses/LICENSE-2.0
    
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
