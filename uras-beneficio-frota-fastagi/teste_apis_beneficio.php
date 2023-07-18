<?php
require_once 'apis_beneficio.php';

$opcao = $argv[1];

switch ($opcao) {
    case 1:
        echo api_login_token();
        break;

    case 2:
        echo get_uniqueId_kontac($argv[2], $argv[3])."\n";
        break;

    case 3:
        echo altera_status_cartao($argv[2], $argv[3], $argv[4], $argv[5])."\n";
        break;

    case 4:
        echo valida_cli_frota($argv[2], $argv[3], $argv[4])."\n";
        break;

    case 5:
        echo validar_cnpj_cpf_cod($argv[2], $argv[3], $argv[4])."\n";
        break;

    case 6:
        echo vld_trnscao_ngd($argv[2])."\n";
        break;

    case 7:
        echo cancela_usu_cartao($argv[2], $argv[3], $argv[4], $argv[5])."\n";
        break;

    case 8:
        echo nova_senha_usu($argv[2])."\n";
        break;

    case 9:
        echo vld_resp_pid($argv[2], $argv[3], $argv[4])."\n";
        break;

    case 10:
        echo api_ucc_protocolo_v2($argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9])."\n";
        break;

    case 11:
        echo valida_cvv_cartao($argv[2], $argv[3])."\n";
        break;

    case 12:
        echo busca_saldo_usu($argv[2])."\n";
    break;

    case 13:
        echo api_ucc_valida_senha($argv[2], $argv[3], $argv[4], $argv[5], $argv[6])."\n";
    break;

    default:
        echo 'OPÇÃO INVALIDA';
        break;
}

?>