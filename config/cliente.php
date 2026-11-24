<?php

return [
    /*
    | Área do Cliente (Tabela de Vendas / pedidos).
    | Enquanto as informações ainda estão sendo preparadas, a área fica "em construção":
    | o visitante vê um aviso e o botão para voltar ao site, e o login fica desativado.
    | Para liberar, defina CLIENTE_AREA_ENABLED=true no .env e rode `php artisan config:clear`.
    */
    'area_enabled' => (bool) env('CLIENTE_AREA_ENABLED', false),
];
