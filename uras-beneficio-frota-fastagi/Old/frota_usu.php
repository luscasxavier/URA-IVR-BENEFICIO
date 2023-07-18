<?php
require_once 'apis_beneficio.php';
require_once 'FrameWorkUraTelek.php';

date_default_timezone_set('America/Sao_Paulo');
global $fastagi;
global $testeativo;


//ARQMAZENAR O NUMERO DE ORIGEM DO CLIENTE NA VARIAVEL ORIGEM
$origem = preg_replace("#[^0-9]#","",$fastagi->request['agi_callerid']);
$uniqueid = $fastagi->request['agi_uniqueid'];
$ddr='';
$ddr=$fastagi->request['agi_extension'];

if($ddr=='32938412') $ddr='40001571';
if($ddr=='32938402') $ddr='08007017066';
$horaAtual = date('H:i');

verbose('NÚMERO DO CLIENTE : '.$origem);

//INICIO DA URA
verbose("<<<<<<<<<<<<< INICIANDO URA FROTA >>>>>>>>>>>>>>>",3);

$timeStamp=time();
verbose("TIME STAMP : ".$timeStamp);
$canal= 'URA FROTA';
//$ticket=$origem.'_'.$timeStamp;
$ticket=$uniqueid;
$indice=0;
global $horaAtual;
global $canal;
global $ddr;
global $ticket;
global $indice;

//01
tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'PERCURSO', 'INICIO');
tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'CONTATO', $origem);

playback('FroUsu/01');
Menu_Principal($uniqueid, $origem);

////////////////////////////////////////////////////////////////////////////////////////////////////////////
//FUNCAO MENU PRINCIPAL
function Menu_Principal($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //02
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'USUARIO DO CARTAO OU EMPRESA CLIENTE OU ATENDENTE');

    $opcao='';
    //$opcao= coletar_dados_usuario("FroUsu/02tmp",6);
    //$opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/02tmp",6);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/02tmp_alt",6);
    if($opcao == '-1'){hangup();break;exit;}

    //03
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'RESPOSTA', 'OPCAO ESCOLHIDA PELO CLIENTE '.$opcao);

    switch($opcao){
        
        case '1':
            verbose("MENU USUARIO CARTAO");
            inicializa_ambiente_novo_menu();
            usu_cartao($uniqueid, $origem);
        break;

        case '2':
            verbose("EMPRESA CLIENTE");
            inicializa_ambiente_novo_menu();
            empresa_cli($uniqueid, $origem);
        break;

        /*case '9':
            verbose("FALAR COM ATENDENTE");
            verbose("HORA ATUAL : ".$horaAtual);
            verbose("CCCCCCCCCCCCCCCCCCCCCCC");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='usuario';
                $origemSolicitacao='URA_FROTA_USUARIO';
                $protocolo= api_ucc_protocolo_v2('', '', '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);

                //04
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL','PROTOCOLO', $protocolo);
                
                playback("FroUsu/18");
                falar_alfa($protocolo);
                playback("FroUsu/19");
                
                //05
                $fila='217';
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE','PERCURSO', $fila);
                tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                inserirprotocolobanco($origem,$protocolo);
                dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                //dial_return('gw02-uravirtual2/'.$fila);

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

                $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);
                
                pesquisa_satisfacao($uniqueid, $origem);
                hangup();
            }else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

                playback("FroCli/final_1");
                playback("FroCli/final_2");
                playback("FroCli/final_3");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }            
        break;*/
        
        default:
            if(retentar_dado_invalido("Menu_Principal","FroUsu/03","OPCAO INVALIDA")){
                //07
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'OPCAO INVALIDA(1 MENU USUARIO, 2 FROTA CLIENTE, 9 ATENDENTE)');
                Menu_Principal($uniqueid, $origem);
            }else{
                //08
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 MENU USUARIO, 2 FROTA CLIENTE, 9 ATENDENTE)');
                playback("FroUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_Principal","FroUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function usu_cartao($uniqueid, $origem){
    playback("FroUsu/06");
    inicializa_ambiente_novo_menu();
    validar_dados($uniqueid, $origem);
}

function validar_dados($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //19
    $indice++;
    tracking($canal, $ddr,$ticket, $indice, 'USUARIO DO CARTAO', 'PERCURSO', 'INFORMAR NUMERO DO CARTAO');    

    $cartao='';
    //$cartao= coletar_dados_usuario("FroUsu/11",19);
    $cartao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/11",19);
    if($cartao == '-1'){hangup();break;exit;}
    verbose("CARTAO INFORMADO : ".$cartao);

    //20
    $indice++;
    tracking($canal, $ddr,$ticket, $indice, 'USUARIO DO CARTAO', 'NUM CARTAO', $cartao);

    if(strlen($cartao)>=17){

        $cartao_vldd= valida_cartao($uniqueid, $origem, $cartao, '', '');
        if($cartao_vldd->{'cartaoFrota'}=="N" && $cartao_vldd->{'cartaoValido'}=="S"){
            verbose("CARTAO NAO ENCAIXA NA OPCAO FROTA");
            
            //44
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'RETORNO', 'CARTAO INVALIDO');

            playback("FroUsu/10");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();

        }elseif($cartao_vldd->{'cartaoValido'}=="S" && $cartao_vldd->{'cartaoFrota'}=="S"){
            verbose("CARTAO VALIDADO E PERTENCE A OPCAO FROTA");

            //44
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'RETORNO', 'CARTAO VALIDO');

            //45
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'PERCURSO', 'CARTAO FROTA');

            inicializa_ambiente_novo_menu();
            usu_cartao_vldd($uniqueid, $origem, $cartao, $cartao_vldd);

        }else{
            if(retentar_dado_invalido("usu_cartao","FroUsu/08","DADOS NAO VALIDADOS PELA API")){
                //46
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'PERCURSO', 'OPCAO INVALIDA');

                validar_dados($uniqueid, $origem);
            }else {
                //47
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO OPCAO');

                playback("FroUsu/08");
                playback("FroUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao","FroUsu/05","DADOS NAO VALIDADOS PELA API");
            }
        }
    }else{
        if(retentar_dado_invalido("usu_cartao","FroUsu/08","DADOS NAO VALIDADOS PELA API")){
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'PERCURSO', 'OPCAO INVALIDA(DIGITE O NMR DO CARTAO)');

            validar_dados($uniqueid, $origem);
        }else{
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO DADOS DE USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(DIGITE O NMR DO CARTAO)');

            playback("FroUsu/08");
            playback("FroUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao","FroUsu/05","DADOS NAO VALIDADOS PELA API");
        }
    }
}

function usu_cartao_vldd($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $transa_negada='';
    $transa_negada= vld_trnscao_ngd($cartao);
    verbose("TEM TRASACAO NEGADA : ".$transa_negada->{'transacaoNegada24hs'});

    if($transa_negada->{'transacaoNegada24hs'}=="S"){
        verbose("CLIENTE POSSUI TRANSACOES NEGADAS");
        //21
        //$indice++;
        //tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'RETORNO', );

        playback("FroUsu/15");
        $motivo= $transa_negada->{'motivoTransNegada'};
        verbose("MOTIVO DA TRANSACAO NEGADA : ".$motivo);

        //22
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'RETORNO', 'TRANSACAO NEGADA NAS ULTIMAS 24 HRS : '.$motivo);

        retorna_audio($motivo);
        playback("FroUsu/16");

        inicializa_ambiente_novo_menu();
        usu_cartao_vldd_final($uniqueid, $origem, $cartao, $cartao_vldd);

        
    }elseif($transa_negada->{'transacaoNegada24hs'}=="N"){
        verbose("CLIENTE NÃO POSSUI TRANSACOES NEGADAS");
        //21
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'RETORNO', 'TRANSACAO NAO NEGADA');

        inicializa_ambiente_novo_menu();
        usu_cartao_cslt_saldo($uniqueid, $origem, $cartao, $cartao_vldd);
        
    }else{
        verbose("SEM RETORNO PELA API");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(API NAO VALIDOU)');

        playback("FroUsu/03");
        playback("FroUsu/04");
        encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_vldd","FroUsu/05","SEM RETORNO PELA API");
    }
}

function usu_cartao_vldd_final($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //23
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'Deseja continuar atendimento? Digite 1 para SIM e 2 para NÃO');

    $opcao='';
    //$opcao= coletar_dados_usuario("FroUsu/17",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/17",1);
    if($opcao == '-1'){hangup();break;exit;}
    verbose("OPCAO DIGITADA : ".$opcao);

    //24
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'RESPOSTA', $opcao);

    switch ($opcao) {
        case '1':
            verbose("CLIENTE DESEJA CONTINUAR O ATENDIMENTO");
            inicializa_ambiente_novo_menu();
            usu_cartao_cslt_saldo($uniqueid, $origem, $cartao, $cartao_vldd);
        break;

        case '2':
            verbose("CLIENTE NÃO DESEJA CONTINUAR O ATENDIMENTO");

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'CLIENTE OPTOU POR ENCERRAR O ATENDIMENTO');
            playback("FroUsu/05");
            
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        break;

        default:
            verbose("OPCAO INVALIDA");
            if(retentar_dado_invalido("usu_cartao_vldd_final","FroUsu/03","OPCAO INVALIDA")){
                //42
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'OPCAO INVALIDA');

                usu_cartao_vldd_final($uniqueid, $origem, $cartao, $cartao_vldd);
            }else{
                //43
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO OPCAO');

                playback("FroUsu/03");
                playback("FroUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_vldd_final","FroUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function usu_cartao_cslt_saldo($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //25
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'Para consulta de saldo digite 1 Ou digite 9 para falar com um de nossos atendentes');
                
    $opcao='';
    //$opcao= coletar_dados_usuario("FroUsu/14",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/14",1);
    if($opcao == '-1'){hangup();break;exit;}

    //26
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'RESPOSTA', $opcao);

    switch ($opcao) {
        case '1':
            verbose("CONSULTA SALDO");
            inicializa_ambiente_novo_menu();
            consulta_saldo_senha($uniqueid, $origem, $cartao, $cartao_vldd);
        break;
        
        case '9':            
            verbose("FALAR COM ATENDENTE");
            verbose("HORA ATUAL : ".$horaAtual);
            verbose("DDDDDDDDDDDDDDDDDDDDDDDDD");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $cartaoId= $cartao_vldd->{'cartaoId'};
                $flagEnt='usuario';
                $origemSolicitacao='URA_FROTA_USUARIO';
                verbose("CARTAO : ".$cartao);
                $protocolo=api_ucc_protocolo_v2('', '', $cartao, $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);

                //27
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PROTOCOLO', $protocolo);

                playback("FroUsu/18");
                falar_alfa($protocolo);
                playback("FroUsu/19");

                //28
                $fila='202';
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', $fila);
                //29
                tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                tracking_canal_ativo($canal, $ddr, $ticket, $indice);

                inserirprotocolobanco($origem,$protocolo);
                dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                //dial_return('gw02-uravirtual2/'.$fila);

                $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);
                
                canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

                inicializa_ambiente_novo_menu();
                pesquisa_satisfacao($uniqueid, $origem);
                hangup();
            }else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

                playback("FroCli/final_1");
                playback("FroCli/final_2");
                playback("FroCli/final_3");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }            
        break;

        default:
            if(retentar_dado_invalido("usu_cartao_cslt_saldo","FroUsu/03","OPCAO INVALIDA")){
                //30
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'OPCAO INVALIDA(1 CONSULTA SALDO, 9 FALAR COM ATENDENTE)');

                usu_cartao_cslt_saldo($uniqueid, $origem, $cartao, $cartao_vldd);
            }else {
                //31
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 CONSULTA SALDO, 9 FALAR COM ATENDENTE)');

                playback("FroUsu/03");
                playback("FroUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_cslt_saldo","FroUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function consulta_saldo_senha($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //48
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'PERCURSO', 'INFORMAR SENHA');

    $senha='';
    //$senha= coletar_dados_usuario("FroUsu/22",5);
    $senha= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/22",5);
    if($senha == '-1'){hangup();break;exit;}
    //49
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'SENHA USUARIO', 'DADOS CONFIDENCIAIS');

    $cnpjcpf= $cartao_vldd->{'numeroCartao'};
    $ent= 'USU';
    $senha_validada= api_ucc_valida_senha($uniqueid, $cnpjcpf, $origem, $senha, $ent);

    if($senha_validada){
        verbose("SENHA VALIDADA");
        //50
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'RETORNO', 'SENHA CORRETA');

        inicializa_ambiente_novo_menu();
        consulta_saldo($uniqueid, $origem, $cartao, $cartao_vldd);
    }else{
        if(retentar_dado_invalido("consulta_saldo_senha","FroUsu/21","SENHA NAO VALIDADA PELA API")){
            //50
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'RETORNO', 'SENHA INCORRETA');
            //51
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'PERCURSO', 'OPCAO INVALIDA');

            consulta_saldo_senha($uniqueid, $origem, $cartao, $cartao_vldd);
        }else {
            //52
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDACAO SENHA USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INCORRETA');

            playback("FroUsu/21");
            playback("FroUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "consulta_saldo_senha","FroUsu/05","SENHA NAO VALIDADA PELA API");
        }
    }
}

function consulta_saldo($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $cartaoId= $cartao_vldd->{'cartaoId'};
    verbose("CARTAO ID : ".$cartaoId);
    $distribui_valores= distribui_valores($uniqueid, $origem, $cartaoId);
    verbose("CARTAO ESTA ATIVO : ".$distribui_valores->{'cartaoAtivo'});

    if($distribui_valores->{'cartaoAtivo'}=="S"){
        verbose("CARTAO ATIVO");
        //53
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'RETORNO', 'USUÁRIO ATIVO');

        if($distribui_valores->{'distribuicaoValores'}=='1'){
            verbose("DISTRIBUICAO DE VALORES É POR VEÍCULO");

            //54
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'RETORNO', 'DISTRIBUICAO_VEICULOS');

            verbose("VALOR DISPONIVEL : ".$distribui_valores->{'saldoDisponível'});
            playback("FroUsu/13");
            falar_valor($distribui_valores->{'saldoDisponível'});
            playback("FroUsu/13");
            falar_valor($distribui_valores->{'saldoDisponível'});

            //55
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', $distribui_valores->{'saldoDisponível'});

            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'SALDO INFORMADO');

            inicializa_ambiente_novo_menu();
            consulta_saldo_final($uniqueid, $origem, $cartao, $cartao_vldd);
        }else{
            verbose("DISTRIBUICAO DE VALORES É POR OUTROS");

            //54
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'RETORNO', 'DISTRIBUICAO_OUTROS');

            //56
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'CONSULTA INDISPONIVEL');

            playback("FroUsu/12");
            inicializa_ambiente_novo_menu();
            consulta_saldo_final($uniqueid, $origem, $cartao, $cartao_vldd);
        }
    }else{
        verbose("CARTAO INATIVO");
        //53
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'RETORNO', 'USUARIO INATIVO');

        playback("FroUsu/12");

        //57
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'CONSULTA INDISPONIVEL');

        inicializa_ambiente_novo_menu();
        consulta_saldo_final($uniqueid, $origem, $cartao, $cartao_vldd);
    }
}

function consulta_saldo_final($uniqueid, $origem, $cartao, $cartao_vldd){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //58
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'Digite 9 p/ falar com um de nossos atendentes ou desligue a ligacao');

    $opcao='';
    //$opcao= coletar_dados_usuario("FroUsu/09",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroUsu/09",1);
    if($opcao == '-1'){hangup();break;exit;}

    //59
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'RESPOSTA', $opcao);

    if($opcao==9){
        verbose("FALAR COM ATENDENTE");
        verbose("HORA ATUAL : ".$horaAtual);
        verbose("EEEEEEEEEEEEEEEEEEE");
        if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
            $origemSolicitacao='URA_FROTA_USUARIO';
            $flagEnt='usuario';
            $cartaoId= $cartao_vldd->{'cartaoId'};
            verbose("CARTAO ID : ".$cartaoId);
            $protocolo=api_ucc_protocolo_v2('', '', $cartao, $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);

            //60
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PROTOCOLO', $protocolo);
            
            playback("FroUsu/18");
            falar_alfa($protocolo);
            playback("FroUsu/19");

            //61
            $fila='202';
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', $fila);

            tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

            tracking_canal_ativo($canal, $ddr, $ticket, $indice);

            inserirprotocolobanco($origem,$protocolo);
            dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
            //dial_return('gw02-uravirtual2/'.$fila);

            $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
            tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

            canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');
            
            inicializa_ambiente_novo_menu();
            pesquisa_satisfacao($uniqueid, $origem);
            hangup();
        }else{
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

            playback("FroCli/final_1");
            playback("FroCli/final_2");
            playback("FroCli/final_3");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }
    }else{
        if(retentar_dado_invalido("consulta_saldo_final","FroUsu/03","OPCAO INVALIDA")){
            //73
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'OPCAO INVALIDA(9 FALAR COM ATENDENTE OU DESLIGUE A LIGACAO)');

            consulta_saldo_final($uniqueid, $origem, $cartao, $cartao_vldd);
        }else {
            //74
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(9 FALAR COM ATENDENTE OU DESLIGUE A LIGACAO)');

            verbose("GERANDO PROTOCOLO E ENCERRANDO A LIGACAO");
            playback("FroUsu/03");
            $origemSolicitacao='URA_FROTA_USUARIO';
            $categoria='Consulta Saldo';
            $flagEnt='usuario';
            $sub='Consulta Saldo – URA';
            $cartaoId= $cartao_vldd->{'cartaoId'};
            verbose("CARTAO ID : ".$cartaoId);
            $protocolo=api_ucc_protocolo_v2($categoria, '', $cartao, $flagEnt, $origemSolicitacao, $sub, $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);

            //75
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PROTOCOLO', $protocolo);

            playback("FroUsu/18");
            falar_alfa($protocolo);
            playback("FroUsu/05");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////
function Menu_Principal_fro_cli($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    verbose(">>>>> INICIOU MENU PRINCIPAL FROTA CLIENTE");
    if(!canal_ativo()) exit();

    $opcao='';
    ////$opcao= coletar_dados_usuario("FroCli/02tmp",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/02tmp",1);
    if($opcao== '-1'){hangup();break;}
    $opcao='2';

    switch ($opcao) {
        case '1':
            verbose("FROTA USUARIO");
            dial_fast('gw01-voztotal/32937651');
            hangup();
        break;

        case '2':
            verbose("MENU EMPRESA CLIENTE");
            inicializa_ambiente_novo_menu();
            empresa_cli($uniqueid, $origem);
        break;

        case '9':
            verbose("FALAR COM ATENDENTE");
            verbose("HORA ATUAL : ".$horaAtual);
            verbose("FFFFFFFFFFFFFFFFFFFF");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='cliente';
                $origemSolicitacao='URA_FROTA_CLIENTE';
                $protocolo=api_ucc_protocolo_v2('', '', '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);
                playback("FroCli/18");
                falar_alfa($protocolo);
                playback("FroCli/19");
                $fila='217';
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CONSULTA SALDO', 'PERCURSO', $fila);

                tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                tracking_canal_ativo($canal, $ddr, $ticket, $indice);

                inserirprotocolobanco($origem,$protocolo);
                dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                //dial_return('gw02-uravirtual2/'.$fila);

                $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

                canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

                inicializa_ambiente_novo_menu();
                pesquisa_satisfacao($uniqueid, $origem);
                hangup();
                exit();
            }else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

                playback("FroCli/final_1");
                playback("FroCli/final_2");
                playback("FroCli/final_3");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }
        break;
        
        default:
            if(retentar_dado_invalido("PRINCIPAL","FroCli/03","OPCAO INVALIDA"))Menu_Principal($uniqueid, $origem);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PRINCIPAL', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","FroCli/04","OPCAO INVALIDA");            
            }
        break;
    }
}

function empresa_cli($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $opcao='';
    //$opcao= coletar_dados_usuario("FroCli/49",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/49",1);

    //08
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'INFORMAR SE É OU SE DESEJA SER CLIENTE VALECARD');

    if($opcao== '-1'){hangup();break;}

    //09
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'RESPOSTA', $opcao);

    if($opcao==1){
        verbose("JA E CLIENTE VALECARD");
        inicializa_ambiente_novo_menu();
        validar_dados_fro_cli($uniqueid, $origem);

    }elseif($opcao==2){
        verbose("DESEJA SE TORNAR CLIENTE VALECARD");
        inicializa_ambiente_novo_menu();
        deseja_ser_cliente($uniqueid, $origem);
    }else{
        if(retentar_dado_invalido("empresa_cli","FroCli/03","OPCAO INVALIDA"))empresa_cli($uniqueid, $origem);
        else{
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "empresa_cli","FroCli/04","OPCAO INVALIDA");
        }
    }
}


function deseja_ser_cliente($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //27
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'PERCURSO', 'INFORMAR CPF OU CNPJ');

    $cpfcnpj= '';
    //$cpfcnpj= coletar_dados_usuario("FroCli/05",18);
    $cpfcnpj= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/05",18);
    if($cpfcnpj== '-1'){hangup();break;}

    verbose("INFORMADO : ".$cpfcnpj);

    //28
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'CPF/CNPJ', $cpfcnpj);

    if(strlen($cpfcnpj)==11){
        verbose("CLIENTE DIGITOU CPF : ".$cpfcnpj);

        //28
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'RETORNO', 'CPF DIGITOS VALIDOS');

        $lgpd='';
        //$lgpd= coletar_dados_usuario("FroCli/09",1);
        $lgpd= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/09",1);
        if($lgpd== '-1'){hangup();break;}

        //31
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'RETORNO', 'PERGUNTAR LGPD');
        
        if($lgpd=='1'){
            verbose("CLIENTE CONCORDOU COM A LEI LGPD");
            $telefone='';
            //$telefone= coletar_dados_usuario("FroCli/11",11);
            $telefone= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/11",11);
            if($telefone== '-1'){hangup();break;}

            //if($telefone=='TIMEOUT' || strlen($telefone)<=10 || $telefone==''){
            //    verbose("CLIENTE NÃO DIGITOU O NÚMERO DE CONTATO CORRETAMENTE");
            //    $telefone=$origem;
            //}
            verbose("NUMERO A SER UTILIZADO : ".$telefone);
            
            $categoria='Deseja ser cliente';
            $flagEnt='cliente';
            $origemSolicitacao='URA_FROTA_CLIENTE';
            $subCat='Prospect – URA';
            $protocolo=api_ucc_protocolo_v2($categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            playback("FroCli/60");
            playback("FroCli/18");
	        falar_alfa($protocolo);
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
            exit();
            }else{
                verbose("CLIENTE DISCORDOU COM A LEI LGPD");
                playback("FroCli/12");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
                exit();
            }

    }elseif(strlen($cpfcnpj)==14){
        verbose("CLIENTE DIGITOU CNPJ : ".$cpfcnpj);

        //28
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'RETORNO', 'CNPJ DIGITOS VALIDOS');

        $telefone='';
        //$telefone=coletar_dados_usuario("FroCli/11",11);
        $telefone= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/11",11);
        if($telefone== '-1'){hangup();break;}

        //if($telefone=='TIMEOUT' || strlen($telefone)<=10 || $telefone==''){
            //verbose("CLIENTE NÃO DIGITOU O NÚMERO DE CONTATO CORRETAMENTE");
            //$telefone=$origem;
        //}
        verbose("NUMERO A SER UTILIZADO : ".$telefone);

        $categoria='Deseja ser cliente';
        $flagEnt='cliente';
        $origemSolicitacao='URA_FROTA_CLIENTE';
        $subCat='Prospect – URA';
        $protocolo=api_ucc_protocolo_v2($categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);
        playback("FroCli/60");
        playback("FroCli/18");
        falar_alfa($protocolo);
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();

    }else{
        //29
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'RETORNO', 'CPF/CNPJ INVÁLIDO');

        if(retentar_dado_invalido("PRINCIPAL","FroCli/03","OPCAO INVALIDA"))deseja_ser_cliente($uniqueid, $origem);
        else{
            //30
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS CPF/CNPJ');
            
            playback("FroCli/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","FroCli/06","OPCAO INVALIDA"); 
        } 
    }
}
function validar_dados_fro_cli($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $identificador='';
    //$identificador=coletar_dados_usuario("FroCli/50",14);
    $identificador= coleta_dados2($canal, $ddr, $ticket, $indice, "FroCli/50",14);

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'INFORMAR COD CLIENTE OU CNPJ');
    //trancking 10

    if($identificador== '-1'){hangup();break;}

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'CODCLIENTE/CNPJ', $identificador);
    //trancking 11

    if(strlen($identificador)<11 && $identificador!='TIMEOUT'){

        verbose("CLIENTE INFORMOU CODIGO : ".$identificador);

        $validaIdent= validar_cnpj_cpf_cod($uniqueid, $origem, $identificador);
        verbose("CLIENTE VALIDADO : ".$validaIdent->{'codClienteValidado'});
        verbose("TIPO CLIENTE : ".$validaIdent->{'clienteBeneficio'});       

        if($validaIdent->{'codClienteValidado'}=="S" && $validaIdent->{'clienteBeneficio'}=="N"){

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'COD CLIENTE VÁLIDO');
            //trancking 12
            
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'CLIENTE FROTA');
            //trancking 13

            verbose("CODIGO INFORMADO VALIDADO CORRETAMENTE E PERTENCE A FROTA");
            inicializa_ambiente_novo_menu();
            empresa_cli_validado_cod($uniqueid, $origem, $identificador, $validaIdent);
            
        }elseif($validaIdent->{'codClienteValidado'}=="N" && $validaIdent->{'clienteBeneficio'}=="S" ){

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'COD CLIENTE INVÁLIDO');
            //trancking 12

            if(retentar_dado_invalido("validar_dados","FroCli/08","CODIGO NAO VALIDADO PELA API"))validar_dados_fro_cli($uniqueid, $origem);
            else{
                playback("FroCli/08");

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS COD CLIENTE');
                //trancking 15

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "validar_dados","FroCli/04","CODIGO NAO VALIDADO PELA API");
            }
        }elseif($validaIdent->{'clienteBeneficio'}=="S" && $validaIdent->{'codClienteValidado'}=="S"){

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'COD CLIENTE VÁLIDO');
            //trancking 12
            
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'CLIENTE BENEFICIO');
            //trancking 13

            verbose("CLIENTE IDENTIFICADO COMO BENEFICIO");
            playback("FroCli/65");

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'CHAMADA DESLIGADA');
            //trancking 14
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
            exit();

        }else{

            if(retentar_dado_invalido("validar_dados","FroCli/08","CODIGO NAO VALIDADO PELA API"))validar_dados_fro_cli($uniqueid, $origem);
            else{
                playback("FroCli/08");
                $flagEnt='cliente';
                $origemSolicitacao='URA_FROTA_CLIENTE';
                $protocolo=api_ucc_protocolo_v2('', $identificador, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                playback("FroCli/18");
                falar_alfa($protocolo);
                verbose("PROTOCOLO GERADO : ".$protocolo);
                playback("FroCli/19");
                verbose("ENCAMINHA PARA UM ATENDENTE");
                verbose("HORA ATUAL : ".$horaAtual);
                verbose("GGGGGGGGGGGGGGGGGGG");
                if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                    $fila='200';

                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                    tracking_canal_ativo($canal, $ddr, $ticket, $indice);
                    
                    inserirprotocolobanco($origem,$protocolo);
                    dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                    //dial_return('gw02-uravirtual2/'.$fila);

                    $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

                    canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

                    inicializa_ambiente_novo_menu();
                    pesquisa_satisfacao($uniqueid, $origem);
                    hangup();
                }else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');
    
                    playback("FroCli/final_1");
                    playback("FroCli/final_2");
                    playback("FroCli/final_3");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                    hangup();
                }
            }
        }
    }elseif(strlen($identificador)>=11){
        
        if(strlen($identificador)==11){
            verbose("CLIENTE DIGITOU CPF : ".$identificador);
        }elseif (strlen($identificador)==14) {
            verbose("CLIENTE DIGITOU CNPJ : ".$identificador);
        }else{
            verbose("CLIENTE DIGITOU O DADO : ".$identificador);
        }

        $validaIdent=valida_cli_frota($uniqueid, $origem, $identificador);        
        verbose("CPF OU CNPJ VALIDO : ".$validaIdent->{'codClienteValidado'});
        
        if($validaIdent->{'codClienteValidado'}=="S" &&  $validaIdent->{'clienteFrota'}=="S"){
            verbose("CPF/CNPJ VALIDADO PELA API");

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'CNPJ VÁLIDO');
            //trancking 16

            $listaCartoes= $validaIdent->{'listaProdutos'};
            $qntdd= count($listaCartoes);
            verbose("QUANTIDADE DE CONTRATOS : ".$qntdd);
            
            if($qntdd>=1){
                inicializa_ambiente_novo_menu();
                validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent);
            }else{
                //72
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR CNPJ CLIENTE', 'RETORNO', 'CONTRATO NAO ENCONTRADO');

                if(retentar_dado_invalido("validar_dados","FroCli/100","NENHUM CONTRATO ENCONTRADO")){
                    //73
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR CNPJ CLIENTE', 'PERCURSO', 'OPCAO INVALIDA');

                    validar_dados_fro_cli($uniqueid, $origem);
                }else{
                    //74
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR CNPJ CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO OPCAO');
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "validar_dados","FroCli/04","NENHUM CONTRATO ENCONTRADO");
                }
            }
            

        }else{
            verbose("CNPJ NÃO VALIDADO PELA API");

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RETORNO', 'CNPJ INVALIDO');
            //trancking 16

            if(retentar_dado_invalido("validar_dados","FroCli/08","CNPJ/CPF NAO VALIDADO PELA API")){
                //61
                //$indice++;
                //tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'DADOS INVALIDOS');

                validar_dados_fro_cli($uniqueid, $origem);
            }else{
                playback("FroCli/08");
                $origemSolicitacao='URA_FROTA_CLIENTE';
                $flagEnt='cliente';
                $protocolo=api_ucc_protocolo_v2('', $identificador, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                playback("FroCli/18");
                falar_alfa($protocolo);

                //60
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PROTOCOLO', $protocolo);
                verbose("HORA ATUAL : ".$horaAtual);
                verbose("HHHHHHHHHHHHHHHHHHHH");
                if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                    verbose("CLIENTE NÃO VALIDADO, ENCAMINHANDO PARA A CENTRAL DE ATENDIMENTO");
                    playback("FroCli/19");

                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'FILA ENVIADA 200');
                    //trancking 23

                    $fila='200';
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'ATENDIMENTO TRANSFERIDO PARA A FILA: '.$fila);
                    
                    tracking_canal_ativo($canal, $ddr, $ticket, $indice);

                    inserirprotocolobanco($origem,$protocolo);
                    dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                    //dial_return('gw02-uravirtual2/'.$fila);

                    $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

                    canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);
                    
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');
                    
                    inicializa_ambiente_novo_menu();
                    pesquisa_satisfacao($uniqueid, $origem);
                    hangup();
                }else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');
    
                    playback("FroCli/final_1");
                    playback("FroCli/final_2");
                    playback("FroCli/final_3");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                    hangup();
                }
            }
        }
    }else{
        if(retentar_dado_invalido("validar_dados","FroCli/08","DADOS INFORMADOS INCORRETAMENTE"))validar_dados_fro_cli($uniqueid, $origem);
        else{
            playback("FroCli/08");
            $flagEnt='cliente';
            $origemSolicitacao='URA_FROTA_CLIENTE';
            $protocolo=api_ucc_protocolo_v2('', $identificador, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            playback("FroCli/18");
            falar_alfa($protocolo);
            verbose("HORA ATUAL : ".$horaAtual);
            verbose("IIIIIIIIIIIIIIIIII");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                playback("FroCli/19");
                verbose("ENCAMINHA PARA UM ATENDENTE");

                $fila='200';
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                tracking_canal_ativo($canal, $ddr, $ticket, $indice);

                inserirprotocolobanco($origem,$protocolo);
                dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
                //dial_return('gw02-uravirtual2/'.$fila);

                $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

                canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

                inicializa_ambiente_novo_menu();
                pesquisa_satisfacao($uniqueid, $origem);
                hangup();
            }else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

                playback("FroCli/final_1");
                playback("FroCli/final_2");
                playback("FroCli/final_3");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }
        }
    }
}

function validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
        
    //$listaCartoes= $produtosCpf->{'listaCartoes'};
    $listaCartoes= $validaIdent->{'listaProdutos'};
    $qntdd= count($listaCartoes);
    $fat=0;
    $cartaoIdSelecionado= [];
    $ContratoAudio='';

    $telemetria='';
    $abastecimento='';
    $manutencao='';

    $telemetria= $listaCartoes->{'telemetria'};
    $abastecimento= $listaCartoes->{'abastecimento'};
    $manutencao= $listaCartoes->{'manutencao'};

    if($abastecimento!='' && !is_null($abastecimento)){
        verbose("CARTAO ID DE ABASTECIMENTO : ".$abastecimento);
        $ContratoAudio="FroCli/52_1&";

    }elseif($manutencao!='' && !is_null($manutencao)){
        verbose("CARTAO ID DE MANUTENCAO : ".$manutencao);
        $ContratoAudio.="FroCli/52_2&";

    }elseif($telemetria!='' && !is_null($telemetria)){
        verbose("CARTAO ID DE TELEMETRIA : ".$telemetria);
        $ContratoAudio.="FroCli/52_3&";
    }else{
        playback("FroCli/03");
        if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","CPF NAO VALIDADO PELA API"))perda_ou_roubo($uniqueid, $origem);
        else{
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(NAO VALIDADO PELA API)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "validar_dados_cnpjcpf","FroCli/04","CPF NAO VALIDADO PELA API");
        }
    }

    $opcao='';
    verbose("AUDIO A SER REPRODUZIDO : ".$ContratoAudio);
    $opcao=background($ContratoAudio,1);

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'INFORMAR PRODUTOS');
    //trancking 17

    if($opcao == '-1'){hangup();break;exit;}

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'RESPOSTA', $opcao);
    //trancking 18


    if(!$cartaoIdSelecionado[$opcao]){
        $opcao==9;
    }

    switch($opcao){
        case '1':
        if($abastecimento){
            $cartaoId= $abastecimento;
            verbose("SELECIONOU CARTAO ABASTECIMENTO COM O ID : ".$cartaoId);
            $validaCartao=valida_cartao($uniqueid, $origem, $identificador, '', '');
            inicializa_ambiente_novo_menu();
            empresa_cli_cpf($uniqueid, $origem, $identificador, $validaIdent, $validaCartao, $cartaoId);
        }else{
            if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","OPCAO INVALIDA"))validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent);
            else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                
                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","FroCli/04","OPCAO INVALIDA");
            }
        }
        break;
        
        case '2':
            if($manutencao){
                $cartaoId= $manutencao;
                verbose("SELECIONOU CARTAO MANUTENCAO COM O ID : ".$cartaoId);
                $validaCartao=valida_cartao($uniqueid, $origem, $identificador, '', '');
                inicializa_ambiente_novo_menu();
                empresa_cli_cpf($uniqueid, $origem, $identificador, $validaIdent, $validaCartao, $cartaoId);
            }else{
                if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","OPCAO INVALIDA"))validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","FroCli/04","OPCAO INVALIDA");
                }
            }
        break;

        case '3':
            if($telemetria){
                $cartaoId= $telemetria;
                verbose("SELECIONOU CARTAO telemetria COM O ID : ".$cartaoId);
                $validaCartao=valida_cartao($uniqueid, $origem, $identificador, '', '');
                inicializa_ambiente_novo_menu();
                empresa_cli_cpf($uniqueid, $origem, $identificador, $validaIdent, $validaCartao, $cartaoId);
            }else{
                if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","OPCAO INVALIDA"))validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","FroCli/04","OPCAO INVALIDA");
                }
            }
        break;

        default:
            playback("FroCli/03");
            if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","CPF NAO VALIDADO PELA API")){

                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'SELECAO PRODUTO INVALIDA');
                //trancking 19

                validar_dados_cnpjcpf($uniqueid, $origem, $identificador, $validaIdent);
            
            }
            else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO PRODUTO');
                //trancking 20

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "validar_dados_cnpjcpf","FroCli/04","CPF NAO VALIDADO PELA API");
            }
        break;
    }
}

function empresa_cli_cpf($uniqueid, $origem, $identificador, $validaIdent, $validaCartao, $cartaoId){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $processId='WKF_Prospect';
    verbose("IDENTIFICADOR : ".$identificador);
    verbose("ORIGEM : ".$origem);

    $flagEnt='cliente';
    $origemSolicitacao='URA_FROTA_CLIENTE';
    $protocolo=api_ucc_protocolo_v2('', $identificador, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
    verbose("PROTOCOLO GERADO : ".$protocolo);

    //24
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PROTOCOLO', $protocolo);

    playback("FroCli/18");
    falar_alfa($protocolo);
    verbose("HORA ATUAL : ".$horaAtual);
    verbose("AAAAAAAAAAAAAAAAAAA");
    if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
        playback("FroCli/19");
        //24
        $fila='200';
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

        tracking_canal_ativo($canal, $ddr, $ticket, $indice);

        inserirprotocolobanco($origem,$protocolo);
        dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?
        //dial_return('gw02-uravirtual2/'.$fila);

        $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
        tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

        canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

        inicializa_ambiente_novo_menu();
        pesquisa_satisfacao($uniqueid, $origem);
        hangup();
    }else{
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

        playback("FroCli/final_1");
        playback("FroCli/final_2");
        playback("FroCli/final_3");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }
}

function empresa_cli_validado_cod($uniqueid, $origem, $identificador, $validaIdent){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $cnpjcpf=$validaIdent->{'cpfCpnj'};
    verbose("CPF OU CNPJ DO CLIENTE : ".$cnpjcpf);
    $flagEnt='cliente';
    $origemSolicitacao='URA_FROTA_CLIENTE';
    $protocolo=api_ucc_protocolo_v2('', $cnpjcpf, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
    verbose("PROTOCOLO GERADO : ".$protocolo);

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PROTOCOLO', $protocolo);
    //trancking 24

    playback("FroCli/18");
    falar_alfa($protocolo);
    verbose("HORA ATUAL : ".$horaAtual);
    verbose("BBBBBBBBBBBBBBBBBBBBBBB");
    if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
        playback("FroCli/19");
        $fila ='200';
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'LIGACAO DIRECIONADA FILA '.$fila);
        //trancking 25

        tracking_canal_ativo($canal, $ddr, $ticket, $indice);

        inserirprotocolobanco($origem,$protocolo);
        dial_return('gw02-kontac33/'.$fila);
        //dial_return('gw02-uravirtual2/'.$fila);

        $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
        tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

        canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');

        inicializa_ambiente_novo_menu();
        pesquisa_satisfacao($uniqueid, $origem);
        hangup();
    }else{
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FALAR COM ATENDENTE', 'PERCURSO', 'CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO');

        playback("FroCli/final_1");
        playback("FroCli/final_2");
        playback("FroCli/final_3");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////
function pesquisa_satisfacao($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

    //09
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'LIGACAO CONTINUADA');

    //10
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'ATENDIMENTO TRATOU A SOLICITACAO?');

    $opcao='';
    //$opcao= coletar_dados_usuario("Fraseologia/PS01",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "Fraseologia/PS01",1);
    if($opcao == '-1'){hangup();break;exit;}
    
    if($opcao=='1' || $opcao=='2'){
        tracking_canal_ativo($canal, $ddr, $ticket, $indice);

        if($opcao=='1')$resp= 'SIM';
        if($opcao=='2')$resp= 'NÃO';

        //11
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'RESPOSTA', $resp);

        inicializa_ambiente_novo_menu();
        pesquisa_satisfacao2($uniqueid, $origem);

    }else{
        playback("FroCli/03");
        if(retentar_dado_invalido("pesquisa_satisfacao","Fraseologia/PS04","OPCAO INVALIDA")){
            //13
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'OPCAO INVALIDA');
            pesquisa_satisfacao($uniqueid, $origem);
        }else{
            //12
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO OPCAO');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "pesquisa_satisfacao","Fraseologia/PS05","OPCAO INVALIDA"); 
        }         
    }

}

function pesquisa_satisfacao2($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    tracking_canal_ativo($canal, $ddr, $ticket, $indice);

    //14
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'INFORMAR PERGUNTA(AVALIACAO 1 A 5)');

    $opcao='';
    //$opcao= coletar_dados_usuario("Fraseologia/PS02",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "Fraseologia/PS02",1);
    if($opcao == '-1'){hangup();break;exit;}

    if($opcao>='1' && $opcao<='5'){
        tracking_canal_ativo($canal, $ddr, $ticket, $indice);
        
        //15
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'RESPOSTA', $opcao);

        playback("Fraseologia/PS03");

        //18
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'FINALIZACAO DA PESQUISA DE SATISFACAO');
        hangup();

    }else{
        verbose("INFORMADO PELO CLIENTE : ".$opcao);
        playback("FroCli/03");
        if(retentar_dado_invalido("pesquisa_satisfacao2","Fraseologia/PS04","OPCAO INVALIDA")){
            //17
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'OPCAO INVALIDA');
            pesquisa_satisfacao2($uniqueid, $origem);
        }else{
            //16
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO OPCAO');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "pesquisa_satisfacao2","Fraseologia/PS05","OPCAO INVALIDA"); 
        } 
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////

tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'PERCURSO', 'LIGACAO DESLIGADA PELO CLIENTE');
return 0;
hangup();
break;
exit();
?>
