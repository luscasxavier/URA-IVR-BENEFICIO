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

if($ddr=='32938411') $ddr='40051212';
if($ddr=='32938404') $ddr='08007015402';

verbose('NÚMERO DO CLIENTE : '.$origem);

//INICIO DA URA
verbose("<<<<<<<<<<<<<INICIANDO URA BENEFICIO USUARIO>>>>>>>>>>>>>>>",3); 

$timeStamp=time();
verbose("TIME STAMP : ".$timeStamp);
$canal= 'URA BENEFICIO';
//$ticket=$origem.'_'.$timeStamp;
$ticket=$uniqueid;
$indice=0;
$horaAtual = date('H:i');

global $horaAtual;
global $canal;
global $ddr;
global $ticket;
global $indice;

//01
tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'PERCURSO', 'INICIO');
tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'CONTATO', $origem);

playback('uraBeneUsu/01');
//playback('uraBeneUsu/45');
Menu_Ura($uniqueid, $origem);

///////////////////////////////////////////////////////////////////////////////////////////////////////////
//FUNCAO MENU PRINCIPAL
function Menu_Ura($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    if(!tracking_canal_ativo($canal, $ddr, $ticket, $indice)) exit();
    //02
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'PERDA/ROUBO OU USUARIO DO CARTAO OU EMPRESA CLIENTE OU ATENDENTE');

    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneUsu/02",1);
    //$opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/02",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/02_alt",1);
    if($opcao== '-1'){hangup();break;}

    //03
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'RESPOSTA', 'OPCAO ESCOLHIDA PELO CLIENTE: '.$opcao);

    switch ($opcao) {
        case '1':
            verbose("PERDA OU ROUBO");
            //03.1
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PERCURSO', 'PERDA OU ROUBO DE CARTAO');
            inicializa_ambiente_novo_menu();
            perda_ou_roubo($uniqueid, $origem);
        break;
        
        case '2':
            verbose("USUARIO DO CARTAO");
            //03.1
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'MENU USUARIO DO CARTAO');
            inicializa_ambiente_novo_menu();
            usuario_cartao($uniqueid, $origem);
        break;

        case '3':
            verbose("EMPRESA CLIENTE");
            //03.1
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'EMPRESA CLIENTE');
            inicializa_ambiente_novo_menu();
            Validar_dados_Cliente($uniqueid, $origem);
        break;

        /*case '9':
            verbose("FALAR COM ATENDENTE");
            verbose("HORA ATUAL : ".$horaAtual);

            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='usuario';
                $origemSolicitacao='URA_BENEFICIO_USUARIO';
                $protocolo=api_ucc_protocolo_v2('', '', '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);

                //04
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU ATENDENTE', 'PROTOCOLO', $protocolo);

                verbose("PROTOCOLO GERADO : ".$protocolo);
                playback("uraBeneUsu/47");
                falar_alfa($protocolo);
                playback("uraBeneUsu/48");
                $fila= '237';
                //05
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU ATENDENTE', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

                inserirprotocolobanco($origem,$protocolo);
                dial_return('gw02-kontac33/'.$fila);
                //dial_return('gw02-uravirtual2/'.$fila);
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'PERCURSO', 'CLIENTE AINDA ESTÁ NA LINHA');
                pesquisa_satisfacao($uniqueid, $origem);
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
            if(retentar_dado_invalido("Menu_Ura","uraBeneUsu/03","OPCAO INVALIDA")){
                //07
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'OPCAO INVALIDA(1 PERDA E ROUBO, 2 USUARIO DO CARTAO, 3 EMPRESA CLIENTE, 9 ATENDENTE)');
                Menu_Ura($uniqueid, $origem);
            }else{
                //08
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU PRINCIPAL', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 PERDA E ROUBO, 2 USUARIO DO CARTAO, 3 EMPRESA CLIENTE, 9 ATENDENTE)');

                playback("uraBeneUsu/03");
                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_Ura","uraBeneUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function perda_ou_roubo($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //19
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'INFORMAR NUMERO DO CARTAO OU CPF ');

    $identificador='';
    //$identificador= coletar_dados_usuario("uraBeneUsu/07",19);
    $identificador= coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/07",19);
    if($identificador== '-1'){hangup();break;}

    //20
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'NUM CARTAO/CPF', $identificador);

    if(strlen($identificador)==11){
        verbose("CLIENTE DIGITOU CPF : ".$identificador);
        //21
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CPF/ CARTAO DIGITOS VALIDOS');

        $cpfValidado= valida_prod_cpf($uniqueid, $origem, $identificador);
        if($cpfValidado->{'cpfValido'}=="S"){
            verbose("CLIENTE VALIDADO PELA API");
            inicializa_ambiente_novo_menu();
            perda_ou_roubo_valida_cpf($uniqueid, $origem, $identificador, $cpfValidado);
        }else{
            playback("uraBeneUsu/08");
            if(retentar_dado_invalido("perda_ou_roubo","uraBeneUsu/04","CPF NAO VALIDADO PELA API")){
                //33
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'CPF INVALIDO');
                perda_ou_roubo($uniqueid, $origem);
            }else{
                //34
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS CPF INVALIDO');
                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_ou_roubo","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
            }
        }

    }elseif(strlen($identificador)<=17 && strlen($identificador)>=14){
        verbose("CLIENTE DIGITOU NUMERO DO CARTAO : ".$identificador);
        //21
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CPF/ CARTAO DIGITOS VALIDOS');

        $validaCartao=valida_cartao($uniqueid, $origem, $identificador, '', '');
        if($validaCartao->{'cartaoValido'}=="S" && $validaCartao->{'cartaoFrota'}=="N"){
            verbose("CARTAO VALIDADO PELA API");
            //35
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CARTAO BENEFICIO');

            inicializa_ambiente_novo_menu();
            perda_ou_roubo_valida_cartao($uniqueid, $origem, $identificador, $validaCartao);

        }elseif($validaCartao->{'cartaoFrota'}=="S"){
            verbose("CARTAO IDENTIFICADO COMO FROTA PELA API");
            //35
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CARTAO NAO E BENEFICIO');
            playback("uraBeneUsu/46");
            playback("uraBeneUsu/05");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
            
        }else{
            playback("uraBeneUsu/08");
            if(retentar_dado_invalido("perda_ou_roubo","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_ou_roubo($uniqueid, $origem);
            else{
                playback("uraBeneUsu/04");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'Perda ou roubo', 'PERCURSO', 'TENTATIVAS EXCEDIDAS');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_ou_roubo","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
            }
        }

        

    }else{
        if(retentar_dado_invalido("perda_ou_roubo","uraBeneUsu/20","DADOS DIGITADOS NAO BATEM COM O NECESSARIO")){
            //22
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CPF/CARTAO INVALIDO');

            perda_ou_roubo($uniqueid, $origem);
        }else{
            //23
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'TENTATIVAS EXCEDIDAS CPF/CARTAO INVALIDOS');

            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_ou_roubo","uraBeneUsu/05","DADOS DIGITADOS NAO BATEM COM O NECESSARIO");
        }
    }
}

function perda_ou_roubo_valida_cpf($uniqueid, $origem, $identificador, $cpfValidado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //24
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'PERGUNTAR LGPD');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/09",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/09",1);
    if($opcao== '-1'){hangup();break;}

    if($opcao==1){
        verbose("CLIENTE CONCORDOU COM A LEI LGPD");
        //25
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'ACEITOU LGPD');
        inicializa_ambiente_novo_menu();        
        perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);

    }elseif($opcao==2){
        verbose("CLIENTE DISCORDOU COM A LEI LGPD");
        //25
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'NAO ACEITOU LGPD');
        playback("uraBeneUsu/19");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }else{
        if(retentar_dado_invalido("perda_ou_roubo_valida_cpf","uraBeneUsu/03","CPF NAO VALIDADO PELA API")){
            //31
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'DADOS INVALIDOS(LGPD)');

            perda_ou_roubo_valida_cpf($uniqueid, $origem, $identificador, $cpfValidado);
        }else{
            //32
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(LGPD)');

            playback("uraBeneUsu/03");
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_ou_roubo_valida_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
        }
    }
}

function perda_ou_roubo_valida_cartao($uniqueid, $origem, $identificador, $validaCartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $cartaoId= $validaCartao->{'cartaoId'};
    verbose("CARTAO ID IDENTIFICADO : ".$cartaoId);
    $nmrCartao= $validaCartao->{'numeroCartao'};
    verbose("NUMERO DE CARTAO IDENTIFICADO : ".$nmrCartao);
    verbose("ENCAMINHANDO PARA O MENU DE VALIDAR SENHA");
    inicializa_ambiente_novo_menu();
    menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);        
}

function perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //26
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'SELECIONAR PRODUTO');
    
    $listaCartoes= $cpfValidado->{'listaCartoes'};
    $qntdd= count($listaCartoes);
    verbose("QUANTIDADE DE CARTOES : ".$qntdd);
    $fat=0;
    $cartaoIdSelecionado= [];
    $ContratoAudio='';

    foreach($listaCartoes as $key=> $value){

        $fat++;
        $dadosCartao= $cpfValidado->{'listaCartoes'}[$fat-1];
        $cartaoId= $dadosCartao->{'cartaoId'};
        verbose("CARTAO ID : ".$cartaoId);
        $finalCartao= $dadosCartao->{'finalCartao'};
        verbose("FINAL DO CARTAO : ".$finalCartao);
        $produto= $dadosCartao->{'produto'};
        verbose("PRODUTO DO CARTAO : ".$produto);

        switch ($produto) {
            case 'ALIMENTACAO':
                $audio='1';    
                $cartaoIdSelecionado[1]=$cartaoId;
            break;

            case 'CONVENIO':
                $audio='2';    
                $cartaoIdSelecionado[2]=$cartaoId;            
            break;

            case 'REFEICAO':
                $audio='3';
                $cartaoIdSelecionado[3]=$cartaoId;
            break;

            case 'COMBUSTIVEL':
                $audio='4';
                $cartaoIdSelecionado[4]=$cartaoId;
            break;

            case 'BONUS':
                $audio='5';
                $cartaoIdSelecionado[5]=$cartaoId;     
            break;
            
            default:
            verbose("ENTROU NESSA CONDITION");
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            break;
        }

        $ContratoAudio.= "uraBeneUsu/44_".$audio;
        $audioFinalCartao= retornar_alfa($finalCartao);

        $ContratoAudio.="&".$audioFinalCartao;

        if($fat<$qntdd){
            $ContratoAudio.='&';
        }
    }

    //27
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', '1 CARTAO ALIMENTACAO/ 2 CARTAO CONVENIO/ 3 CARTAO REFEICAO/ 4 CARTAO COMBUSTIVEL/ 5 CARTAO BONUS');

    verbose("AUDIO A SER REPRODUZIDO : ".$ContratoAudio);
    $opcao='';
    $opcao=background($ContratoAudio,1);
    if($opcao == '-1'){hangup();break;exit;}

    if(!$cartaoIdSelecionado[$opcao]){
        $opcao==9;
    }

    //28
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', $opcao);

    switch($opcao){
        case '1':
            $cartaoId= $cartaoIdSelecionado[1];
            verbose("SELECIONOU CARTAO ALIMENTACAO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmrCartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    playback("uraBeneUsu/04");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            }
        break;
        
        case '2':
            $cartaoId= $cartaoIdSelecionado[2];
            verbose("SELECIONOU CARTAO CONVENIO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmrCartao= $validaCartao->{'numeroCartao'};
                verbose("asfeageetsfqecbvabv= ".$nmrCartao);
                inicializa_ambiente_novo_menu();
                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    playback("uraBeneUsu/04");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');
                    
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            }
        break;

        case '3':
            $cartaoId= $cartaoIdSelecionado[3];
            verbose("SELECIONOU CARTAO REFEICAO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmrCartao= $validaCartao->{'numeroCartao'};
                verbose("asfeageetsfqecbvabv= ".$nmrCartao);
                inicializa_ambiente_novo_menu();
                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    playback("uraBeneUsu/04");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            }
        break;

        case '4':
            $cartaoId= $cartaoIdSelecionado[4];
            verbose("SELECIONOU CARTAO COMBUSTIVEL COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmrCartao= $validaCartao->{'numeroCartao'};
                verbose("asfeageetsfqecbvabv= ".$nmrCartao);
                inicializa_ambiente_novo_menu();
                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    playback("uraBeneUsu/04");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');
                    
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            }
        break;

        case '5':
            $cartaoId= $cartaoIdSelecionado[5];
            verbose("SELECIONOU CARTAO BONUS COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmrCartao= $validaCartao->{'numeroCartao'};
                verbose("asfeageetsfqecbvabv= ".$nmrCartao);
                inicializa_ambiente_novo_menu();
                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    playback("uraBeneUsu/04");
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR O PRODUTO)');
                    
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
                }
            }
        break;

        default:
        verbose("ENTROU NESSA OUTRA CONTITIONES");
            if(retentar_dado_invalido("perda_roubo_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API")){
                //29
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'DADOS INVALIDOS(SELECIONE O PRODUTO)');
                perda_roubo_cpf($uniqueid, $origem, $identificador, $cpfValidado);
            }else{
                //30
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_roubo_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
            }
        break;
    }
}

function menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    $a=$validaCartao->{'statusCartao'};
    verbose("STATUS DO CARTAO : ".$a);
    if($a=='A'){
        //43
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'RETORNO', 'CARTAO DESBLOQUEADO');

        verbose("ENTROU NO MENU DE SENHA");
        //38
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'INFORMAR SENHA DO USUARIO');

        $senha='';
        //$senha= coletar_dados_usuario("uraBeneUsu/22",5);
        $senha= coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/22",5);
        if($senha== '-1'){hangup();break;}

        //39
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'SENHA', 'DADOS CONFIDENCIAIS');

        $entidade="USU";
        verbose("IDENTIFICADOR : ".$nmrCartao);
        //verbose("SENHA : ".$senha);
        $validaSenha=api_ucc_valida_senha($uniqueid, $nmrCartao, $origem, $senha, $entidade);
        verbose("RETORNO DA API : ".$validaSenha);
        if($validaSenha){
            verbose("SENHA VALIDADA PELA API : ".$validaSenha);
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA VALIDA');

            inicializa_ambiente_novo_menu();
            P_R_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao, $senha);

        }else{
            if(retentar_dado_invalido("menu_valida_senha_PR","uraBeneUsu/21","SENHA NAO VALIDADO PELA API")){
                //40
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA INVALIDA');

                menu_valida_senha_PR($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao);
            }else{
                //42
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

                playback("uraBeneUsu/21");
                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "menu_valida_senha_PR","uraBeneUsu/05","SENHA NAO VALIDADO PELA API");
            }
        }
    }else{
        verbose("CARTAO JA SE ENCONTRA BLOQUEADO OU SINISTRADO");
        //43
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'RETORNO', 'CARTAO BLOQUEADO - URA');

        playback("uraBeneUsu/CartBloq");
        playback("uraBeneUsu/04");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
        exit;
    }
    
}

function P_R_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //44
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PERCURSO', 'INFORMAR CONFIRMACAO DE CANCELAMENTO');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/23",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/23",1);
    if($opcao== '-1'){hangup();break;}

    //45
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'RESPOSTA', $opcao);

    if($opcao==1){
        $novo_status='S';
        $ent="USU";
        //$cartaoId= $validaCartao->{'cartaoId'};
        $alteracaoStatus= altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);
        if($alteracaoStatus->{'statusAlterado'}=='S'){
            $processId='WKF_Atendimentos';
            $categoria='Cancelamento de Cartão';
            $flagEnt='usuario';
            $origemSolicitacao='URA_BENEFICIO_USUARIO';
            $subCat='Perda ou Roubo - URA';
            $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, $nmrCartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, $nmrCartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            playback("uraBeneUsu/24_1");
            verbose("NUMERO DO CARTAO A FALAR : ".$nmrCartao);
            falar_alfa($nmrCartao);
            playback("uraBeneUsu/24_2");
            verbose("PROTOCOLO GERADO : ".$protocolo);

            //46
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PROTOCOLO', $protocolo);

            falar_alfa($protocolo);
            verbose("SEGUNDA VIA DO CARTAO DISPONÍVEL : ".$alteracaoStatus->{'flagSegundaViaAuto'});

            if($alteracaoStatus->{'flagSegundaViaAuto'}=='S'){
                //49
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'RETORNO', 'CARTAO GERADO');
                $processId='WKF_Atendimentos';
                $categoria='Cancelamento de Cartão';
                $flagEnt='usuario';
                $origemSolicitacao='URA_BENEFICIO_USUARIO';
                $subCat='Perda ou Roubo - URA';
                $protocolo2= protocolo_v2_v2($processId, $categoria, $identificador, $nmrCartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
                //$protocolo2= api_ucc_protocolo_v2($categoria, $identificador, $nmrCartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
                playback("uraBeneUsu/27");

                //50
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PROTOCOLO', $protocolo2);
                
                falar_alfa($protocolo2);
                playback("uraBeneUsu/25_1");
                playback("uraBeneUsu/sete");
                playback("uraBeneUsu/25_2");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }else{
                //49
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'RETORNO', 'CARTAO GERADO');

                playback("uraBeneUsu/26");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            }

        }else{
            if(retentar_dado_invalido("menu_valida_senha_PR","uraBeneUsu/04","STATUS DO CARTAO NÃO ALTERADO"))P_R_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao, $senha);
            else{
                playback("uraBeneUsu/04");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(RETORNO API)');
                
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "menu_valida_senha_PR","uraBeneUsu/05","SENHA NAO VALIDADO PELA API");
            }
        }

    }else{
        playback("uraBeneUsu/03");
        if(retentar_dado_invalido("PRINCIPAL","uraBeneUsu/20","OPCAO INVALIDA")){
            //47
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PERCURSO', 'OPCAO INVALIDA(1 PARA CONFIRMAR O CANCELAMENTO)');

            P_R_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmrCartao, $senha);
        }else{
            //48
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO DE CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 PARA CONFIRMAR O CANCELAMENTO)');

            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","uraBeneUsu/05","OPCAO INVALIDA");
        }
    }
}

function usuario_cartao($uniqueid, $origem){
    playback("uraBeneUsu/06");
    inicializa_ambiente_novo_menu();
    usu_cartao_valida_dados($uniqueid, $origem);
}

function usu_cartao_valida_dados($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //19
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'INFORMAR NUMERO DO CARTAO OU CPF ');

    $identificador='';
    //$identificador= coletar_dados_usuario("uraBeneUsu/07",17);
    $identificador= coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/07",17);
    if($identificador== '-1'){hangup();break;}

    //20
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'NUM CARTAO/CPF', $identificador);

    if(strlen($identificador)==11){
        verbose("CLIENTE DIGITOU CPF : ".$identificador);
        //21
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CPF/ CARTAO DIGITOS VALIDOS');

        $cpfValidado= valida_prod_cpf($uniqueid, $origem, $identificador);
        if($cpfValidado->{'cpfValido'}=="S"){
            verbose("CPF VALIDADO PELA API");
            inicializa_ambiente_novo_menu();
            usu_cartao_valida_cpf($uniqueid, $origem, $identificador, $cpfValidado);
        }else{
            playback("uraBeneUsu/08");
            
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'OPCAO CPF/ CARTAO INVALIDO PELA API');

            if(retentar_dado_invalido("usu_cartao_valida_cpf","uraBeneUsu/04","CPF NAO VALIDADO PELA API"))usu_cartao_valida_dados($uniqueid, $origem);
            else{
                playback("uraBeneUsu/04");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CPF/ CARTAO DIGITOS INVALIDOS)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_valida_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
            }
        }

    }elseif(strlen($identificador)<=17 && strlen($identificador)>=14 || strlen($identificador)==12){
        verbose("CLIENTE DIGITOU NUMERO DO CARTAO : ".$identificador);
        //21
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CPF/ CARTAO DIGITOS VALIDOS');

        inicializa_ambiente_novo_menu();
        usu_cartao_valida_cartao($uniqueid, $origem, $identificador);

    }else{
        if(retentar_dado_invalido("usu_cartao_valida_dados","uraBeneUsu/20","DADOS DIGITADOS NAO BATEM COM O NECESSARIO")){
            //22
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'OPCAO CPF/ CARTAO INVALIDO');
            usu_cartao_valida_dados($uniqueid, $origem);
        }
        else{
            playback("uraBeneUsu/04");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO CPF/ CARTAO INVALIDO)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_valida_dados","uraBeneUsu/05","DADOS DIGITADOS NAO BATEM COM O NECESSARIO");
        }
    }
}

function usu_cartao_valida_cpf($uniqueid, $origem, $identificador, $cpfValidado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //24
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'PERGUNTAR LGPD');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/09",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/09",1);
    if($opcao== '-1'){hangup();break;}

    if($opcao==1){
        verbose("CLIENTE CONCORDOU COM A LEI LGPD");
        //25
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'ACEITOU LGPD');
        inicializa_ambiente_novo_menu();
        usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);

    }elseif($opcao==2){
        verbose("CLIENTE DISCORDOU COM A LEI LGPD");
        //25
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'NAO ACEITOU LGPD');
        playback("uraBeneUsu/19");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }else{
        if(retentar_dado_invalido("perda_ou_roubo_valida_cpf","uraBeneUsu/03","CPF NAO VALIDADO PELA API")){
            //31
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'DADOS INVALIDOS(LGPD)');

            usu_cartao_valida_cpf($uniqueid, $origem, $identificador);
        }else{
            //32
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(LGPD)');
            
            playback("uraBeneUsu/03");
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "perda_ou_roubo_valida_cpf","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
        }
    }
    
}

function usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //26
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'SELECIONAR PRODUTO');
        
    $listaCartoes= $cpfValidado->{'listaCartoes'};
    $qntdd= count($listaCartoes);
    $fat=0;
    $cartaoIdSelecionado= [];
    $ContratoAudio='';

    foreach($listaCartoes as $key=> $value){
        $dadosCartao= $cpfValidado->{'listaCartoes'}[$fat];
        $cartaoId= $dadosCartao->{'cartaoId'};
        verbose("CARTAO ID : ".$cartaoId);
        $finalCartao= $dadosCartao->{'finalCartao'};
        $produto= $dadosCartao->{'produto'};
    
        switch ($produto) {
            case 'ALIMENTACAO':
                $audio='1';    
                $cartaoIdSelecionado1=$cartaoId;
                //$cartaoIdSelecionado[1]=$cartaoId;
            break;
    
            case 'CONVENIO':
                $audio='2';    
                $cartaoIdSelecionado2=$cartaoId;
                //$cartaoIdSelecionado[2]=$cartaoId;
                
            break;
    
            case 'REFEICAO':
                $audio='3';
                $cartaoIdSelecionado3=$cartaoId;
                //$cartaoIdSelecionado[3]=$cartaoId;
            break;
    
            case 'COMBUSTIVEL':
                $audio='4';
                $cartaoIdSelecionado4=$cartaoId;
                //$cartaoIdSelecionado[4]=$cartaoId;
            break;
    
            case 'BONUS':
                $audio='5';
                $cartaoIdSelecionado5=$cartaoId;
                //$cartaoIdSelecionado[5]=$cartaoId;
            break;
                
            default:
                if(retentar_dado_invalido("usu_cartao_2","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONAR PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_2","uraBeneUsu/05","OPCAO INVALIDA");
                }
            break;
        }
    
        $ContratoAudio.= "uraBeneUsu/44_".$audio."&";
        $audioFinalCartao= retornar_alfa($finalCartao);
    
        $ContratoAudio.=$audioFinalCartao;
    
        if($fat<$qntdd){
            $ContratoAudio.='&';
        }        
        $fat++;
    }

    //27
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', '1 CARTAO ALIMENTACAO COM FINAL/ 2 CARTAO CONVENIO/ 3 CARTAO REFEICAO/ 4 CARTAO COMBUSTIVEL/ 5 CARTAO BONUS');

    $opcao='';
    $opcao=background($ContratoAudio,1);
    if($opcao == '-1'){hangup();break;exit;}
    
    if(!$cartaoIdSelecionado[$opcao]){
        $opcao==9;
    }

    //28
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', $opcao);
    
    switch($opcao){
        case '1':
            $cartaoId= $cartaoIdSelecionado1;
            //$cartaoId= $cartaoIdSelecionado[1];
            verbose("SELECIONOU CARTAO ALIMENTACAO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmr_cartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
                }
            }
        break;

        case '2':
            $cartaoId= $cartaoIdSelecionado2;
            //$cartaoId= $cartaoIdSelecionado[2];
            verbose("SELECIONOU CARTAO CONVENIO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmr_cartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
                }
            }
        break;

        case '3':
            $cartaoId= $cartaoIdSelecionado3;
            //$cartaoId= $cartaoIdSelecionado[3];
            verbose("SELECIONOU CARTAO REFEICAO COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmr_cartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
                }
            }
        break;

        case '4':
            $cartaoId= $cartaoIdSelecionado4;
            //$cartaoId= $cartaoIdSelecionado[4];
            verbose("SELECIONOU CARTAO COMBUSTIVEL COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmr_cartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');
                    
                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
                }
            }
        break;

        case '5':
            $cartaoId= $cartaoIdSelecionado5;
            //$cartaoId= $cartaoIdSelecionado[5];
            verbose("SELECIONOU CARTAO BONUS COM O ID : ".$cartaoId);
            if($cartaoId){
                $validaCartao=valida_cartao($uniqueid, $origem, $cartaoId, '', '');
                $nmr_cartao= $validaCartao->{'numeroCartao'};
                inicializa_ambiente_novo_menu();
                usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA"))usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
                else{
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
                }
            }
        break;

        default:
            if(retentar_dado_invalido("usu_cartao_perda_roubo_cpf","uraBeneUsu/04","OPCAO INVALIDA")){
                //29
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'DADOS INVALIDOS(SELECIONE O PRODUTO)');
                usu_cartao_2($uniqueid, $origem, $identificador, $cpfValidado);
            }
            else{
                //30
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_perda_roubo_cpf","uraBeneUsu/05","OPCAO INVALIDA");
            }
        break;
    }
    
}

function usu_cartao_valida_cartao($uniqueid, $origem, $identificador){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $validaCartao=valida_cartao($uniqueid, $origem, $identificador, '', '');
    $nmr_cartao= $validaCartao->{'numeroCartao'};
    verbose("NUMERO CARTAO : ".$nmr_cartao);

    verbose("CARTAO VALIDO : ".$validaCartao->{'cartaoValido'});
    verbose("CARTAO FROTA : ".$validaCartao->{'cartaoFrota'});
    if($validaCartao->{'cartaoValido'}=="S" && $validaCartao->{'cartaoFrota'}=="N"){
        verbose("CARTAO VALIDADO PELA API");
        //35
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CARTAO BENEFICIO');

        $cartaoId= $validaCartao->{'cartaoId'};
        inicializa_ambiente_novo_menu();
        usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        
    }elseif($validaCartao->{'cartaoFrota'}=="S"){
        verbose("CARTAO IDENTIFICADO COMO FROTA PELA API");
        //35
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CARTAO NAO E BENEFICIO');

        playback("uraBeneUsu/46");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }else{
        playback("uraBeneUsu/08");
        if(retentar_dado_invalido("usu_cartao_valida_cartao","uraBeneUsu/04","CARTAO NAO VALIDADO PELA API")){
            //35
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'CARTAO NÃO IDENTIFICADO PELA API');

            usu_cartao_valida_dados($uniqueid, $origem);
        }else{
            //35
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RETORNO', 'TENTATIVAS EXCEDIDAS CARTAO INVALIDO');
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_valida_cartao","uraBeneUsu/05","CARTAO NAO VALIDADO PELA API");
        }
    }
}

function usu_cartao_dados_validados($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    verbose("RETORNO DA API : ".$validaCartao->{'statusCartao'});

    if($validaCartao->{'statusCartao'}=='B' || $validaCartao->{'statusCartao'}=='E'){
        verbose("CARTAO DO CLIENTE SE ENCONTRA BLOQUEADO OU EM ANALISE");

        //51
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CARTAO BLOQUEADO - URA');
        
        inicializa_ambiente_novo_menu();
        usu_cartao_dsblquear($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
    }else{
        verbose("CLIENTE NAO POSSUI CARTAO BLOQUEADO");
        //51
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CARTAO DESBLOQUEADO');
        inicializa_ambiente_novo_menu();
        usu_cartao_saldo($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
    }
}

function usu_cartao_dsblquear($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $novo_status='';
    $ent='USU';
    $novoSttsCrto= altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);
    verbose("USUÁRIO APTO A DESBLOQUEAR CARTAO : ".$novoSttsCrto->{'flagUsuarioDesbloqueio'});

    if($novoSttsCrto->{'flagUsuarioDesbloqueio'}=='S'){
        verbose("USUÁRIO COM PERFIL PARA DESBLOQUEAR O CARTAO");
        //52
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'USUARIO AUTORIZADO');

        //53
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'SEU CARTAO ENCONTRA-SE BLOQUEADO, DESEJA DESBLOQUEAR AGORA? DIGITE 1 PARA SIM E 2 PARA NAO');

        $opcao='';
        //$opcao=coletar_dados_usuario("uraBeneUsu/10",1);
        $opcao=coleta_dados2($canal, $ddr, $ticket, $indice, "uraBeneUsu/10",1);
        if($opcao == '-1'){hangup();break;exit;}

        //54
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RESPOSTA', $opcao);

        switch ($opcao) {
            case '1':
                verbose("CLIENTE DESEJA DESBLOQUEAR O CARTAO");
                inicializa_ambiente_novo_menu();
                menu_desbloquear_cartao_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            break;

            case '2':
                verbose("CLIENTE NÃO DESEJA DESBLOQUEAR O CARTAO");
                playback("uraBeneUsu/11");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
                hangup();
            break;
        
            default:
                playback("uraBeneUsu/03");
                if(retentar_dado_invalido("usu_cartao_dsblquear","uraBeneUsu/04","OPCAO INVALIDA")){
                    //55
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RESPOSTA', 'OPCAO INVALIDA(1 DESBLOQUEAR O CARTAO, 2 NÃO DESBLOQUEAR O CARTAO)');

                    usu_cartao_dsblquear($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
                }else{
                    //56
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS (1 DESBLOQUEAR O CARTAO, 2 NÃO DESBLOQUEAR O CARTAO)');

                    playback("uraBeneUsu/04");
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_dsblquear","uraBeneUsu/05","OPCAO INVALIDA");
                }
            break;
        }
    }else{
        verbose("USUARIO NÃO TEM PERFIL PARA DESBLOQUEAR CARTAO");
        //52
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'USUARIO NÃO AUTORIZADO');

        playback("uraBeneUsu/50");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    } 
}

function menu_desbloquear_cartao_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //38
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'INFORMAR SENHA DO USUARIO');

    $senha='';
    //$senha=coletar_dados_usuario("uraBeneUsu/22",5);
    $senha=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/22",5);
    if($senha == '-1'){hangup();break;exit;}

    //39
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'SENHA', 'NUMERO DIGITADO PELO USUARIO');

    $entidade= "USU";
    verbose("NUMERO DO CARTAO : ".$nmr_cartao);
    $validaSenha=api_ucc_valida_senha($uniqueid, $nmr_cartao, $origem, $senha, $entidade);
    if($validaSenha){
        verbose("SENHA VALIDADA PELA API : ".$validaSenha);
        //40
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA VALIDA');

        inicializa_ambiente_novo_menu();
        menu_desbloquear_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);

    }else{
        if(retentar_dado_invalido("menu_desbloquear_cartao_senha","uraBeneUsu/21","SENHA NAO VALIDADO PELA API")){
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA INVALIDA');

            menu_desbloquear_cartao_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        }else{
            //42
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SENHA INVALIDA)');

            playback("uraBeneUsu/21");
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "menu_desbloquear_cartao_senha","uraBeneUsu/05","SENHA NAO VALIDADO PELA API");
        }
    }
}

function menu_desbloquear_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $novo_status='';
    $ent='USU';
    
    $novoSttsCrto= altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);
    verbose("USUÁRIO APTO A DESBLOQUEAR CARTAO : ".$novoSttsCrto->{'flagUsuarioDesbloqueio'});

    if($novoSttsCrto->{'flagUsuarioDesbloqueio'}=='S'){
        //82
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RETORNO', 'TEM PERFIL DE DESBLOQUEIO');

        //83
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'PERCURSO', 'PARA CONFIRMAR SOLICITACAO DE DESBLOQUEIO, DIGITE 1 PARA SIM E 2 PARA NAO');

        $opcao='';
        //$opcao=coletar_dados_usuario("uraBeneUsu/32",1);
        $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/32",1);
        if($opcao == '-1'){hangup();break;exit;}

        //84
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RESPOSTA', 'OPCAO ESCOLHIDA PELO USUARIO: '.$opcao);

        if($opcao=='1'){
            $novo_status='A';
            $ent='USU';
            $novoSttsCrto= altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);
            if($novoSttsCrto->{'statusAlterado'}=="S"){
                verbose("CARTAO DESBLOQUEADO COM SUCESSO");

                //85
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RESPOSTA', 'SEM FALHA NA ATIVACAO');
                $processId='WKF_Atendimentos';
                $categoria='Desbloqueio do cartão';
                $flagEnt='usuario';
                $origemSolicitacao='URA_BENEFICIO_USUARIO';
                $subCat='Desbloqueio do cartão - URA';
                $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
                //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);

                verbose("PROTOCOLO GERADO : ".$protocolo);
                playback("uraBeneUsu/33");
                falar_alfa($protocolo);

                //86
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'PROTOCOLO', $protocolo);

                inicializa_ambiente_novo_menu();
                desbloquear_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
                
            }else{
                verbose("CARTAO NÃO DESBLOQUEADO");

                //85
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RESPOSTA', 'SEM FALHA NA ATIVACAO');

                playback("uraBeneUsu/30");
                playback("uraBeneUsu/13");
                inicializa_ambiente_novo_menu();
                desbloquear_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
            }

        }elseif($opcao=='2'){
            playback("uraBeneUsu/13");
            inicializa_ambiente_novo_menu();
            desbloquear_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
            
        }else{
            playback("uraBeneUsu/03");
            if(retentar_dado_invalido("menu_desbloquear_cartao","uraBeneUsu/20","OPCAO INVALIDA"))menu_desbloquear_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
            else{
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "menu_desbloquear_cartao","uraBeneUsu/05","OPCAO INVALIDA");
            }
        }
    }else{
        verbose("CARTAO NÃO DESBLOQUEADO");
        //82
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RETORNO', 'NAO TEM PERFIL DE DESBLOQUEIO');

        playback("uraBeneUsu/30");
        playback("uraBeneUsu/31");
        playback("uraBeneUsu/13");
        inicializa_ambiente_novo_menu();
        desbloquear_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);

        /*$indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();*/
    }    
}

function desbloquear_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //87
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'PERCURSO', 'DESEJA VOLTAR AO MENU DO USUARIO? 1 PARA SIM OU 2 PARA NAO');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/28",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/28",1);
    if($opcao == '-1'){hangup();break;exit;}

    //88
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'RESPOSTA', 'OPCAO ESCOLHIDA PELO USUARIO: '.$opcao);

    switch ($opcao) {
        case '1':
            verbose("CLIENTE DESEJA RETORNAR AO MENU USUARIO DO CARTAO");
            inicializa_ambiente_novo_menu();
            usuario_cartao($uniqueid, $origem);
        break;
        
        case '2':
            verbose("CLIENTE DESEJA FINALIZAR A LIGACAO");
            playback("uraBeneUsu/05");

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        break;
        
        default:
            playback("uraBeneUsu/03");
            if(retentar_dado_invalido("desbloquear_cartao_final","uraBeneUsu/20","OPCAO INVALIDA")){
                //89
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'PERCURSO', 'OPCAO INVALIDA (VOLTAR AO MENU DO USUARIO? 1 PARA SIM OU 2 PARA NAO)');
                menu_desbloquear_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
            }
            else{
                //90
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'DESBLOQUEIO DO CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS (VOLTAR AO MENU DO USUARIO? 1 PARA SIM OU 2 PARA NAO)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "menu_desbloquear_cartao","uraBeneUsu/05","OPCAO INVALIDA");
            }    
        break;
    }
}

function usu_cartao_saldo($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $BSCsaldo=busca_saldo_usu($cartaoId);
    $vencimento= $BSCsaldo->{'data'};

    if($BSCsaldo->{'grupoProduto'}=='CONSUMO'){
        verbose("CLIENTE DO GRUPO CONSUMO");
        //57
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CARTAO CONSUMO');

        $Contadorlimites= count($BSCsaldo->{'saldos'});
        $limites= $BSCsaldo->{'saldos'};

        if($Contadorlimites>1){
            verbose("CLIENTE POSSUI MULTILIMITE");
            //59
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CLIENTE TEM MULTILIMITE');
            foreach ($limites as $value) {
                $tipo= $value->{'limite'};
                verbose("TIPO DE LIMITE : ".$tipo);
                $saldo= $value->{'saldo'};
                verbose("VALOR DO SALDO : ".$saldo);
                
                playback("uraBeneUsu/".$tipo);
                playback("uraBeneUsu/15_1");
                playback("uraBeneUsu/15_2");
                falar_valor($saldo);
                playback("uraBeneUsu/15_3");
                verbose("DATA DO VENCIMENTO : ".$vencimento);
                falar_data($vencimento);
            }
        }else{
            verbose("CLIENTE NÃO POSSUI MULTILIMITE");
            //59
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CLIENTE TEM MULTILIMITE');

            $tipo= $limites[0]->{'limite'};
            verbose("TIPO DE LIMITE : ".$tipo);
            $saldo= $limites[0]->{'saldo'};
            verbose("VALOR DO SALDO : ".$saldo);

            playback("uraBeneUsu/".$tipo);
            playback("uraBeneUsu/15_1");
            playback("uraBeneUsu/15_2");
            falar_valor($saldo);
            playback("uraBeneUsu/15_3");
            verbose("DATA DO VENCIMENTO : ".$vencimento);
            falar_data($vencimento);

            //60
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'VALOR INFORMADO: '.$saldo);
            tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', ' LIMITE/ MULTILIMITE INFORMADO');
        }

        inicializa_ambiente_novo_menu();
        usu_cartao_saldo_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);

    }elseif($BSCsaldo->{'grupoProduto'}=='CARGA'){
        verbose("CLIENTE DO GRUPO CARGA");
        //57
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RETORNO', 'CARTAO CARGA');

        $saldo= $BSCsaldo->{'saldos'}[0]->{'saldo'};
        verbose("SALDO DO CLIENTE : ".$saldo);
        //58
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'VALOR INFORMADO: '.$saldo);
        tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'SALDO INFORMADO');

        playback("uraBeneUsu/14_1");        
        falar_valor($saldo);

        inicializa_ambiente_novo_menu();
        usu_cartao_saldo_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);

    }else{
        playback("uraBeneUsu/03");
        if(retentar_dado_invalido("usu_cartao_saldo","uraBeneUsu/04","API NAO RETORNOU NADA"))usu_cartao_saldo($uniqueid, $origem, $identificador, $validaCartao, $cartaoId);
        else{
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(API NAO IDENTIFICOU)');

            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_saldo","uraBeneUsu/05","API NAO RETORNOU NADA");
        }
    }
}

function usu_cartao_saldo_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //61
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'DESEJA OUVIR O SALDO NOVAMENTE? 1 PARA SIM E 2 PARA NAO');
    verbose("FHJDKSHDKLHFHDHKJLSFD ".$ticket);
    verbose("FHJDKSHDKLHFHDHKJLSFD 2".$ticket);
    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/16",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/16",1);
    if($opcao == '-1'){hangup();break;exit;}
    if(!canal_ativo()) exit();

    //62
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RESPOSTA', $opcao);
    
    switch ($opcao) {
        case 1:
            verbose("CLIENTE DESEJA OUVIR NOVAMENTE");
            inicializa_ambiente_novo_menu();
            usu_cartao_saldo($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        break;

        case 2:
            verbose("CLIENTE DESEJA CONTINUAR");
            inicializa_ambiente_novo_menu();
            usu_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        break;
        
        default:
            playback("uraBeneUsu/03");
            if(retentar_dado_invalido("usu_cartao_saldo_final","uraBeneUsu/20","OPCAO INVALIDA")){
                //65
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'OPCAO INVALIDA(1 OUVIR NOVAMENTE, 2 CONTINUAR)');

                usu_cartao_saldo_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                //66
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 OUVIR NOVAMENTE, 2 CONTINUAR)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_saldo_final","uraBeneUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function usu_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //63
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', '2 VIA DE SENHA (1) OU ALTERAR SENHA (2) OU 2 VIA DE CARTAO (3) OU MENU INICIAL (8) OU FALAR COM ATENDENTE (9)');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneUsu/17",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/17",1);
    if($opcao == '-1'){hangup();break;exit;}

    //64
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'RESPOSTA', $opcao);

    switch ($opcao) {
        case '1':
            verbose("SEGUNDA VIA DE SENHA");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'SEGUNDA VIA DE SENHA', 'PERCURSO', 'SEGUNDA VIA DE SENHA');
            inicializa_ambiente_novo_menu();
            sgnda_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        break;

        case '2':
            verbose("ALTERAR SENHA");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA USUARIO', 'PERCURSO', 'ALTERAR SENHA USUARIO');
            inicializa_ambiente_novo_menu();
            altera_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        break;
        
        case '3':
            verbose("SEGUNDA VIA DO CARTAO");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'SEGUNDA VIA DO CARTAO', 'PERCURSO', 'SEGUNDA VIA DO CARTAO');
            inicializa_ambiente_novo_menu();
            sgndo_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        break;
        
        case '8':
            verbose("MENU INICIAL");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'MENU INICIAL', 'PERCURSO', 'MENU INICIAL');
            inicializa_ambiente_novo_menu();
            Menu_Ura($uniqueid, $origem);
        break; 

        case '9':
            verbose("FALAR COM ATENDENTE");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='usuario';
                $origemSolicitacao='URA_BENEFICIO_USUARIO';
                $processId= 'WKF_Atendimentos';
                if(strlen($identificador)>14) $identificador='';

                $protocolo= protocolo_v2_v2($processId, '', $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                //$protocolo=api_ucc_protocolo_v2('', $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);

                //67
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PROTOCOLO', $protocolo);

                playback("uraBeneUsu/47");
                falar_alfa($protocolo);
                playback("uraBeneUsu/48");

                $fila='212';
                //68
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);

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
                exit;
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
            playback("uraBeneUsu/03");
            if(retentar_dado_invalido("usu_cartao_final","uraBeneUsu/20","OPCAO INVALIDA")){
                //70
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'OPCAO INVALIDA(SGNDA VIA SENHA(1), ALTERA SENHA(2), SGNDA VIA CARTAO(3), MENU INICIAL(8), FALAR ATENDENTE(9))');
                usu_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                //71
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO DO CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SGNDA VIA SENHA(1), ALTERA SENHA(2), SGNDA VIA CARTAO(3), MENU INICIAL(8), FALAR ATENDENTE(9))');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "usu_cartao_final","uraBeneUsu/05","OPCAO INVALIDA");
            }
        break;
    }
}

function sgnda_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $possuiCvv= possuiCvv($cartaoId, $uniqueId, $origem);
    verbose("CLIENTE POSSUI CVV : ".$possuiCvv->{'possuiCVV'});

    if($possuiCvv->{'possuiCVV'}=='S'){
        verbose("CLIENTE POSSUI CVV");
        //91
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RETORNO', 'CARTAO COM CVV');

        //92
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'INFORMAR CVV DO CARTAO');

        $cvv='';
        //$cvv=coletar_dados_usuario("uraBeneUsu/34",3);
        $cvv=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/34",3);
        if($cvv == '-1'){hangup();break;exit;}

        //93
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'CVV', $cvv);

        verbose("CVV INFORMADO : ".$cvv);
        verbose("CARTAO ID INFORMADO : ".$cartaoId);
        $cvvValidado= valida_cvv_cartao($cvv, $cartaoId);

        if($cvvValidado->{'cvvValidado'}=="S"){
            verbose("CODIVO CVV VALIDADO");
            //94
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RETORNO', 'CVV VALIDO');

            inicializa_ambiente_novo_menu();
            sgnda_senha_pid($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);

        }else{
            verbose("CODIVO CVV INVALIDO");
            //94
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RETORNO', 'CVV INVALIDO');
            
            if(retentar_dado_invalido("sgnda_senha","uraBeneUsu/35","CPF NAO VALIDADO PELA API"))sgnda_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId);
            else{
                //96
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(INFORME O CVV)');
                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "sgnda_senha","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
            }
        }
    }else{
        verbose("CLIENTE NAO POSSUI CVV");
        //91
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RETORNO', 'CARTAO SEM CVV');

        verbose("ENCAMINHANDO PARA O PID");
        inicializa_ambiente_novo_menu();
        sgnda_senha_pid($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        
        /*$processId='WKF_PROTOCOLO_PAI';
        $protocolo= api_ucc_protocolo_easy($processId, $cnpjcpf='00000000000', $origem);
        VERBOSE("PROTOCOLO GERADO : ".$protocolo);
        playback("uraBeneUsu/47");
        falar_alfa($protocolo);
        playback("uraBeneUsu/49");
        playback("uraBeneUsu/47");
        falar_alfa($protocolo);
        playback("uraBeneUsu/48");
        dial_fast('gw02-kontac33/217');
        hangup();*/
    }
}

function sgnda_senha_pid($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $finalCpf='';
    $mesNasc='';
    $anoNasc='';


    //VER COM A CAMILA SE PRECISA AUMENTAR O INDICE
    //97.1
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', 'PERGUNTA: 3 ULTIMOS DIGITOS DO SEU CPF');
    //$finalCpf=coletar_dados_usuario("uraBeneUsu/29_1",3);
    $finalCpf=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/29_1",3);
    if($finalCpf == '-1'){hangup();break;exit;}
    //97.2
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', $finalCpf);

    //97.1
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', 'PERGUNTA: MES DO SEU NASCIMENTO');
    //$mesNasc=coletar_dados_usuario("uraBeneUsu/29_2",2);
    $mesNasc=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/29_2",2);
    if($mesNasc == '-1'){hangup();break;exit;}
    //97.2
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', $mesNasc);

    //97.1
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', 'PERGUNTA: ANO DE SEU NASCIMENTO');
    //$anoNasc=coletar_dados_usuario("uraBeneUsu/29_3",4);
    $anoNasc=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/29_3",4);
    if($anoNasc == '-1'){hangup();break;exit;}
    //97.2
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', $anoNasc);

    $validaRespostas= pergunta_pid($uniqueid, $origem, $cartaoId, $finalCpf, $anoNasc, $mesNasc);

    if($validaRespostas->{'passouPid'}=='S'){
        verbose("API VALIDOU OS ACERTOS DO PID : ".$validaRespostas->{'passouPid'});
        //97
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', 'PID DADOS CORRETOS');

        inicializa_ambiente_novo_menu();
        sgnd_senha_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            
    }else{
        //97
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PID', 'PID DADOS INCORRETOS');
        verbose("CLIENTE NÃO ACERTOU A QUANTIDADE MINIMA DE PERGUNTAS : ".$validaRespostas->{'passouPid'});
        playback("uraBeneUsu/04");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }
}

function sgnd_senha_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //98
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'DESEJA CONFIRMAR O RESET DE SENHA, DIGITE 1 PARA SIM OU 2 PARA NAO');

    $rstSenha='';
    //$rstSenha=coletar_dados_usuario("uraBeneUsu/36",1);
    $rstSenha=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/36",1);
    verbose("DADO INFORMADO : ".$rstSenha);
    if($rstSenha == '-1'){hangup();break;}

    //99
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RESPOSTA', $rstSenha);


    if($rstSenha=='1'){
        verbose("IDENTIFICADOR : ".$identificador);
        $reset_senha= nova_senha_usu($identificador);
        if($reset_senha->{'novaSenha'}){
            verbose("SENHA GERADA COM SUCESSO");
            playback("uraBeneUsu/37");
            playback("uraBeneUsu/38");
            falar_alfa($reset_senha->{'novaSenha'});

            //101.1
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'SENHA INFORMADA');

            //101
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'RETORNO', $reset_senha->{'novaSenha'});

            playback("uraBeneUsu/38");
            falar_alfa($reset_senha->{'novaSenha'});
            $processId='WKF_Atendimentos';
            $categoria='2 Via de Senha';
            $flagEnt='usuario';
            $origemSolicitacao='URA_BENEFICIO_USUARIO';
            $subCat='2 Via de Senha - URA';
            $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, '', $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, '', $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            //102
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PROTOCOLO', $protocolo);

            playback("uraBeneUsu/47");
            falar_alfa($protocolo);
            verbose("FINALIZANDO CLIENTE");
            playback("uraBeneUsu/05");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();

        }else{
            verbose("API NÃO GEROU NOVA SENHA");
            playback("uraBeneUsu/04");
            playback("uraBeneUsu/05");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }
    }elseif($rstSenha=='2'){
        verbose("CLIENTE CANCELOU O RESET DE SENHA");

        //100
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'SOLICITACAO CANCELADA PELO CLIENTE');
        $processId='WKF_Atendimentos';
        $categoria='2 Via de Senha';
        $flagEnt='usuario';
        $origemSolicitacao='URA_BENEFICIO_USUARIO';
        $subCat='Não Concluido - URA';
        $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, $nmr_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);

        //100
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PROTOCOLO', $protocolo);

        playback("uraBeneUsu/47");
        falar_alfa($protocolo);
        verbose("FINALIZANDO CLIENTE");
        playback("uraBeneUsu/13");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }else{
        verbose("DADO INVALIDO");
        playback("uraBeneUsu/03");
        if(retentar_dado_invalido("sgnda_senha","uraBeneUsu/20","CPF NAO VALIDADO PELA API")){
            //103
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'OPCAO INVALIDA(CONFIRMAR O RESET DE SENHA)');
            sgnd_senha_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        }else{
            //104
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DE SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CONFIRMAR O RESET DE SENHA)');

            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "sgnda_senha","uraBeneUsu/05","CPF NAO VALIDADO PELA API");
        }
    }
}

function altera_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    //38
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'INFORMAR SENHA DO USUARIO');

    $senha='';
    //$senha= coletar_dados_usuario("uraBeneUsu/22",5);
    $senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/22",5);
    if($senha== '-1'){hangup();break;}

    //39
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'SENHA', 'DADOS CONFIDENCIAIS');

    $entidade= "USU";
    //$nmr_cartao= $validaCartao->{'numeroCartao'};
    verbose("NUMERO DO CARTAO : ".$nmr_cartao);
    $validaSenha=api_ucc_valida_senha($uniqueid, $nmr_cartao, $origem, $senha, $entidade);
    if($validaSenha){
        verbose("SENHA VALIDADA PELA API : ".$validaSenha);

        //40
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA VALIDA');

        inicializa_ambiente_novo_menu();
        altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);

    }else{
        if(retentar_dado_invalido("altera_senha","uraBeneUsu/21","SENHA NAO VALIDADO PELA API")){
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA INVALIDA');

            altera_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        }else{
            //42
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            playback("uraBeneUsu/21");
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha","uraBeneUsu/05","SENHA NAO VALIDADO PELA API");
        }
    }
}

function altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //105
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'INFORMAR NOVA SENHA DO USUARIO');

    $nova_senha='';
    //$nova_senha= coletar_dados_usuario("uraBeneUsu/41",8);
    $nova_senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/41",8);
    if($nova_senha== '-1'){hangup();break;}

    if($nova_senha == $senha || $nova_senha == $senha){
        verbose("SENHA NOVA E ANTIGA SÃO IGUAIS");
        if(retentar_dado_invalido("altera_senha_validada","uraBeneCli/SenhasIguais","SENHA NOVA E ANTIGA SÃO IGUAIS"))altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS USUARIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(SENHA INVALIDA)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha_validada","uraBeneCli/04","SENHA NOVA E ANTIGA SÃO IGUAIS");
        }
    }

    //106
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'NOVA SENHA', 'DADOS CONFIDENCIAIS');

    if(strlen($nova_senha)>5 || strlen($nova_senha)<=4 || $nova_senha=='' || $nova_senha=='TIMEOUT'){
        verbose("SENHA DIGITADA COM MAIS OU MENOS CARACTERES QUE O PADRÃO");
        playback("uraBeneUsu/21");
        if(retentar_dado_invalido("altera_senha_validada","uraBeneUsu/12","SENHAS NAO FORAM DIGITADAS CORRETAMENTE"))altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
        else{
            playback("uraBeneUsu/04");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha_validada","uraBeneUsu/05","SENHAS NAO FORAM DIGITADAS CORRETAMENTE");
        }
    }

    verbose("NOVA SENHA DIGITADA CORRETAMENTE");

    //107
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'INFORMAR NOVA SENHA DO USUARIO');

    $rpt_nv_senha='';
    //$rpt_nv_senha= coletar_dados_usuario("uraBeneUsu/42",8);
    $rpt_nv_senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/42",8);
    if($rpt_nv_senha== '-1'){hangup();break;}

    //108
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'REPETIR NOVA SENHA', 'DADOS CONFIDENCIAIS');
    
    if(strlen($nova_senha)>5 || strlen($nova_senha)<=4 || $nova_senha=='' || $nova_senha=='TIMEOUT'){
        verbose("SENHA DIGITADA COM MAIS OU MENOS CARACTERES QUE O PADRÃO");
        playback("uraBeneUsu/21");
        if(retentar_dado_invalido("altera_senha_validada","uraBeneUsu/12","SENHAS NAO FORAM DIGITADAS CORRETAMENTE"))altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
        else{
            playback("uraBeneUsu/04");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha_validada","uraBeneUsu/05","SENHAS NAO FORAM DIGITADAS CORRETAMENTE");
        }
    }
    verbose("NOVA SENHA DIGITADA NOVAMENTE CORRETAMENTE");

    if($nova_senha == $rpt_nv_senha){
        verbose("SENHAS FORAM DIGITADAS CORRETAMENTE");
        //109
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RETORNO', 'SENHA VALIDA');

        $entidade="USU";
        $nmr_cartao= $validaCartao->{'numeroCartao'};
        verbose("NUMERO CARTAO : ".$nmr_cartao);
        $alteraSenha= api_ucc_altera_senha($uniqueid, $origem, $nmr_cartao, $nova_senha, $entidade);

        if($alteraSenha){
            verbose("SENHA ALTERADA COM SUCESSO : ".$alteraSenha);
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RETORNO', 'SENHA ALTERADA COM SUCESSO');
            $processId='WKF_Atendimentos';
            $categoria='Altera senha de cartão';
            $flagEnt='usuario';
            $origemSolicitacao='URA_BENEFICIO_USUARIO';
            $subCat='Alterar Senha - URA';
            $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, $cartaoId, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, $cartaoId, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            //110
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PROTOCOLO', $protocolo);

            playback("uraBeneUsu/43");
            falar_alfa($protocolo);
            playback("uraBeneUsu/05");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }else{
            verbose("SENHA NÃO FOI APROVADA PELA API");
            playback("uraBeneUsu/21");
            if(retentar_dado_invalido("altera_senha_validada","uraBeneUsu/12","SENHAS NAO FORAM DIGITADAS CORRETAMENTE"))altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $senha);
            else{
                playback("uraBeneUsu/04");
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha_validada","uraBeneUsu/05","SENHAS NAO FORAM DIGITADAS CORRETAMENTE");
            }
        }

    }else{
        verbose("SENHAS DIGITADOS SÃO DIFERENTES");
        playback("uraBeneUsu/21");
        if(retentar_dado_invalido("altera_senha_validada","uraBeneUsu/12","SENHAS NAO FORAM DIGITADAS CORRETAMENTE")){
            //109
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RETORNO', 'SENHA INVALIDA');

            altera_senha_validada($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);
        }else{
            //112
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "altera_senha_validada","uraBeneUsu/05","SENHAS NAO FORAM DIGITADAS CORRETAMENTE");
        }
    }
}

function sgndo_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $possuiCvv= possuiCvv($cartaoId, $uniqueId, $origem);
    if($possuiCvv->{'possuiCVV'}=='S'){
        verbose("CLIENTE POSSUI CVV");
        //113
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'CARTAO COM CVV');

        //114
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'PERCURSO', 'INFORMAR CVV DO CARTAO');

        $cvv='';
        //$cvv=coletar_dados_usuario("uraBeneUsu/34",3);
        $cvv=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/34",3);
        if($cvv == '-1'){hangup();break;exit;}
        //115
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'CVV', $cvv);

        $cvvValidado= valida_cvv_cartao($cvv, $cartaoId);
        if($cvvValidado->{'cvvValidado'}=='S'){
            verbose("CVV VALIDADO COM SUCESSO");
            //116
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'CVV VALIDO');

            inicializa_ambiente_novo_menu();
            sgndo_cartao_vld_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        }else{
            verbose("CODIVO CVV INVALIDO");
            if(retentar_dado_invalido("sgndo_cartao","uraBeneUsu/35","CVV NAO VALIDADO PELA API")){
                //116
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'CVV INVALIDO');

                sgndo_cartao($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
            }else{
                //118
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CVV DO CARTAO)');

                playback("uraBeneUsu/04");
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "sgndo_cartao","uraBeneUsu/05","CVV NAO VALIDADO PELA API");
            }
        }
    }else{
        verbose("CARTAO NAO POSSUI CVV");
        //113
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'CARTAO SEM CVV');

        inicializa_ambiente_novo_menu();
        sgndo_cartao_vld_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
    }
}

function sgndo_cartao_vld_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //38
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'INFORMAR SENHA DO USUARIO');

    $senha='';
    //$senha= coletar_dados_usuario("uraBeneUsu/22",5);
    $senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneUsu/22",5);
    if($senha== '-1'){hangup();break;}

    //39
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'SENHA', 'DADOS CONFIDENCIAIS');

    $entidade= "USU";
    $nmr_cartao= $validaCartao->{'numeroCartao'};
    $validaSenha=api_ucc_valida_senha($uniqueid, $nmr_cartao, $origem, $senha, $entidade);
    if($validaSenha){
        verbose("SENHA VALIDADA PELA API : ".$validaSenha);
        //40
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA VALIDA');

        inicializa_ambiente_novo_menu();
        sgndo_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha);

    }else{
        if(retentar_dado_invalido("sgndo_cartao_vld_senha","uraBeneUsu/21","SENHA NAO VALIDADO PELA API")){
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'RETORNO', 'SENHA INVALIDA');

            sgndo_cartao_vld_senha($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao);
        }else{
            //42
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            playback("uraBeneUsu/21");
            playback("uraBeneUsu/04");
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "sgndo_cartao_vld_senha","uraBeneUsu/05","SENHA NAO VALIDADO PELA API");
        }
    }
}

function sgndo_cartao_final($uniqueid, $origem, $identificador, $validaCartao, $cartaoId, $nmr_cartao, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $novo_status= 'S';
    $ent= "USU";
    $alteraStatus= altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);
    verbose("STATUS DA ALTERACAO : ".$alteraStatus->{'flagSegundaViaAuto'});
    if($alteraStatus->{'flagSegundaViaAuto'}=='S'){
        verbose("SEGUNDA VIA DO CARTAO DISPONÍVEL");

        //119
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'GERACAO DE 2 VIA DISPONIVEL');
        $processId='WKF_Atendimentos';
        $categoria='2 Via de Cartão';
        $flagEnt='usuario';
        $origemSolicitacao='URA_BENEFICIO_USUARIO';
        $subCat='2 Via de Cartão - URA';
        $protocolo= protocolo_v2_v2($processId, $categoria, $identificador, $cartaoId, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        //$protocolo= api_ucc_protocolo_v2($categoria, $identificador, $cartaoId, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);
        //120
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'PROTOCOLO', $protocolo);

        playback("uraBeneUsu/27");
        falar_alfa($protocolo);
        playback("uraBeneUsu/25_1");
        playback("uraBeneUsu/sete");
        playback("uraBeneUsu/25_2");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }elseif($alteraStatus->{'flagSegundaViaAuto'}=='N'){
        verbose("SEGUNDA VIA DO CARTAO NÃO DISPONÍVEL");
        //119
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, '2 VIA DO CARTAO', 'RETORNO', 'GERACAO DE 2 VIA INDISPONIVEL');

        playback("uraBeneUsu/26");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }else{
        playback("uraBeneUsu/04");
        playback("uraBeneUsu/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//BENEFICIO CLIENTE

function Menu_Principal($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    verbose(">>>>> INICIOU MENU PRINCIPAL");
    if(!canal_ativo()) exit();

    $menu= "";
    //$menu= coletar_dados_usuario("uraBeneCli/02",1);
    if($menu== '-1'){hangup();break;}
    $menu='3';
  
    switch ($menu) {
        case '1':
            verbose(">>>>> PERDA OU ROUBO"); 
            inicializa_ambiente_novo_menu();
            M1_Perda_ou_roubo($uniqueid, $origem);
        break;

        case '2':
            verbose(">>>>> USUARIO DO CARTAO");
            //inicializa_ambiente_novo_menu();
            //M2_Usuario($uniqueid, $origem);
            dial_fast('gw02-voztotal/32937640');
            hangup();
        break;
        
        case '3':
            verbose(">>>>> EMPRESA CLIENTE");
            inicializa_ambiente_novo_menu();
            Validar_dados_Cliente($uniqueid, $origem);
        break;

        case '9':
            verbose(">>>>> FALAR COM UM ATENDENTE");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='cliente';
                $origemSolicitacao='URA_BENEFICIO_CLIENTE';
                $protocolo= api_ucc_protocolo_v2('', '', '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);
                playback("uraBeneCli/18");
                falar_alfa($protocolo);
                playback("uraBeneCli/19");

                $fila='212';
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
            if(retentar_dado_invalido("PRINCIPAL","uraBeneCli/03","OPCAO INVALIDA"))Menu_Principal($uniqueid, $origem);
            else {
                playback('uraBeneCli/04');
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","uraBeneCli/05","OPCAO INVALIDA");
            }
        break;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Validar_dados_Cliente($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //36
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS CLIENTE', 'PERCURSO', 'DIGITE 1 SE JA E CLIENTE VALECARD DO PRODUTO BENEFICIO OU 2 SE DESEJA CONTRATAR');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneCli/06",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/06",1);
    if($opcao == '-1'){hangup();break;}

    //37
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS CLIENTE', 'RESPOSTA', $opcao);

    switch ($opcao) {
        case '1':
            verbose("JA É CLIENTE BENEFICIO");
            playback("uraBeneCli/audioExtra");
            inicializa_ambiente_novo_menu();
            M3_Emp_Ja_Cliente($uniqueid, $origem);
        break;

        case '2':
            verbose("DESEJA CONTRATAR BENEFICIO");
            inicializa_ambiente_novo_menu();
            M3_Emp_Deseja_Credenciar($uniqueid, $origem);
        break;
        
        default:
            playback("uraBeneCli/03");
            if(retentar_dado_invalido("VALIDAR DADOS","uraBeneCli/20","OPCAO INVALIDA")){
                //63
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS CLIENTE', 'PERCURSO', 'OPCAO INVALIDA(1 JA É CLIENTE BENEF, 2 DESEJA CONTRATAR)');

                Validar_dados_Cliente($uniqueid, $origem);
            }else{
                //64
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'VALIDAR DADOS CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 JA É CLIENTE BENEF, 2 DESEJA CONTRATAR)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "VALIDAR DADOS CLIENTE","uraBeneCli/04","OPCAO INVALIDA");
            }
        break;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M3_Emp_Ja_Cliente($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //38
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'INFORMAR COD CLIENTE OU CNPJ OU CPF');

    $digitado= '';
    //$digitado= coletar_dados_usuario("uraBeneCli/07",16);
    $digitado= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/07",16);
    if($digitado == '-1'){hangup();break;}

    //39
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'CODCLIENTE/CNPJ/CPF', $digitado);

    if(strlen($digitado)==6){
        $retorno= validar_cnpj_cpf_cod($uniqueid, $origem, $digitado);
        verbose("CODIGO INFORMADO : ".$digitado);
        if($retorno->{'clienteBeneficio'}== "N" && $retorno->{'codClienteValidado'}== "S"){
            verbose("CODIGO IDENTIFICADO COMO NÃO SENDO BENEFICIO");
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/CNPJ/CPF VALIDO');
            //41
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'CLIENTE E FROTA');

            playback("uraBeneCli/51");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();

        }elseif($retorno->{'codClienteValidado'}== "N"){
            verbose("NÃO VALIDADO PELA API");
            if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/20","OPCAO INVALIDA")){
                //40
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/CNPJ/CPF INVALIDO');
                
                M3_Emp_Ja_Cliente($uniqueid, $origem);
            }else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
            }

        }elseif($retorno->{'clienteBeneficio'}== "S" && $retorno->{'codClienteValidado'}== "S"){
            verbose("ENCAMINHANDO PARA O MENU EMPRESA CLIENTE");
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/ CNPJ/ CPF VALIDO');

            $contratoSelecionado= $digitado;
            $cnpjcpf= $retorno->{'cpfCpnj'};
            inicializa_ambiente_novo_menu();
            M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);

        }else{
            verbose("NÃO VALIDADO PELA API");
            playback("uraBeneCli/03");
            if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/20","OPCAO INVALIDA")){
                //40
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/CNPJ/CPF INVALIDO');

                M3_Emp_Ja_Cliente($uniqueid, $origem);
            }else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");            
            }
        }

    }elseif(strlen($digitado)==11 || strlen($digitado)==14){
        $retorno= validar_cnpj_cpf_cod($uniqueid, $origem, $digitado);
        verbose("CLIENTE DIGITOU CNPJ OU CPF : ".$digitado);

        if($retorno->{'clienteBeneficio'}== "S"){
            verbose("CLIENTE É BENEFICIO");
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/ CNPJ/ CPF VALIDO');

            inicializa_ambiente_novo_menu();
            EmpCli_Seleciona_contrato($uniqueid, $origem, $cnpjcpf, $retorno);

        }else{
            verbose("NÃO VALIDADO PELA API");
            if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/20","CLIENTE NÃO É BENEFICIO")){
                //40
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/ CNPJ/ CPF INVALIDO');

                M3_Emp_Ja_Cliente($uniqueid, $origem);
            }else{
                //49
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS CNPJ/CPF INVALIDOS');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","CLIENTE NÃO É BENEFICIO");
            }
        }
    }else{
        verbose("DIGITADO DE FORMA INVÁLIDA");
        if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/20","DADOS DIGITADOS INCORRETAMENTE")){
            //40
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'COD CLIENTE/CNPJ/CPF INVALIDO');

            M3_Emp_Ja_Cliente($uniqueid, $origem);
        }else{
            //49
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS CNPJ/CPF INVALIDOS');
            
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","DADOS DIGITADOS INCORRETAMENTE");
        }
    }
}

function EmpCli_Seleciona_contrato($uniqueid, $origem, $cnpjcpf, $retorno){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //$contratos= $retorno->{'listaProdutos'}[0];
    $contratos= $retorno->{'listaProdutos'};

    $audioDinamico='';
    $fat= 1;

    $produtosNumerados = array();
    $indice = 1;

    foreach ($contratos as $key => $value){
        $contrato=$value->{'ctr01'};
        if($contrato!=''){
            //verbose("ENCONTRADO PRODUTO ".$key." COM O CONTRATO ".$contrato);
            $audioDinamico .= "FroCli/DIGITE".$fat."&uraBeneCli/".$key."&";
            $fat++;

            $nomeProduto[$indice] = $key;
            $produtosNumerados[$indice] = $contrato;
            verbose("VARIAVEL NOME DO PRODUTO: ".$nomeProduto[$indice]);
            verbose("VARIAVEL PRODUTONUMERADO: ".$produtosNumerados[$indice]);
            $indice++;
            
        }
    }

    $audioDinamico = rtrim($audioDinamico, '&');

    if($audioDinamico==''){
        verbose("CLIENTE NÃO POSSUI NENHUM CONTRATO");
        if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","CLIENTE NÃO É BENEFICIO")){
            //42
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'CONTRATO NAO ENCONTRADO');
            M3_Emp_Ja_Cliente($uniqueid, $origem);
        }else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RESPOSTA', 'TENTATIVAS EXCEDIDAS(CLIENTE NAO BENEFICIO)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","CLIENTE NÃO É BENEFICIO");
        }
    }

    verbose("AUDIO A SER REPRODUZIDO : ".$audioDinamico);
    //43
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RETORNO', 'PRODUTOS DO CLIENTE');

    //44
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', '1 - CONTRATO ALIMENTACAO/ 2 - CONVENIO/ 3 - REFEICAO/ 4 - BONUS/ 5 - FARMACIA/ 6 - VIAGEM/ 7 - COMBUSTIVEL/ 8 - LOGISTICA');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneCli/52",1);
    $opcao=background($audioDinamico, 2);
    if($opcao == '-1'){hangup();break;exit;}
    $cnpjcpf= $retorno->{'cpfCpnj'};

    tracking_canal_ativo($canal, $ddr, $ticket, $indice);
    //45
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'RESPOSTA', $opcao);

    if (isset($produtosNumerados[$opcao]) && $opcao <= $fat) {

        $contratoSelecionado = $produtosNumerados[$opcao];
        $produto= $nomeProduto[$opcao];
        verbose("CONTRATO DO PRODUTO ".$produto." SELECIONADO: " . $contratoSelecionado);

        tracking_canal_ativo($canal, $ddr, $ticket, $indice);

        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);

    } else {
        playback("FroCli/03");
        if(retentar_dado_invalido("validar_dados_cnpjcpf","FroCli/20","SELECAO PRODUTO INVALIDA")){
            //19
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'SELECAO PRODUTO INVALIDA');

            EmpCli_Seleciona_contrato($uniqueid, $origem, $cnpjcpf, $retorno);
        
        }
        else{
            //20
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'CLIENTE VALECARD', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SELECAO PRODUTO');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "validar_dados_cnpjcpf","FroCli/04","CPF NAO VALIDADO PELA API");
        }
    }
    /*
        switch ($opcao){
            case '1':
                $contratoSelecionado=$contratos->{'alimentacao'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
                
            break;

            case '2':
                $contratoSelecionado=$contratos->{'convenio'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;
                    
            case '3':
                $contratoSelecionado=$contratos->{'refeicao'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;

            case '4':
                $contratoSelecionado=$contratos->{'bonus'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;

            case '5':
                $contratoSelecionado=$contratos->{'farmacia'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;

            case '6':
                $contratoSelecionado=$contratos->{'viagem'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;

            case '7':
                $contratoSelecionado=$contratos->{'combustivel'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;

            case '8':
                $contratoSelecionado=$contratos->{'valecardLogistica'}->{'ctr01'};
                if($contratoSelecionado){
                    //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                    inicializa_ambiente_novo_menu();
                    M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
                }else{
                    verbose("CONTRATO SELECIONADO É VAZIO : ".$contratoSelecionado);
                    if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/100","OPCAO INVALIDA"))EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                    else {
                        $indice++;
                        tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                        encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                    }
                }
            break;
                    
            default:
                verbose("CLIENTE SELECIONOU UMA OPCAO INVALIDA");
                if(retentar_dado_invalido("M3_Emp_Ja_Cliente","uraBeneCli/20","OPCAO INVALIDA")){
                    //46
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'OPCAO INVALIDA(SELECIONE O PRODUTO)');

                    EmpCli_Seleciona_contrato($uniqueid, $origem, $digitado, $retorno);
                }else{
                    //47
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'CLIENTE_BENEFICIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SELECIONE O PRODUTO)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M3_Emp_Ja_Cliente","uraBeneCli/04","OPCAO INVALIDA");
                }
            break;
        }
    */
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M3_Emp_Deseja_Credenciar($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    verbose("ENTROU NO MENU DESEJA SE CREDENCIAR");
    //54
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'INFORMAR CNPJ OU CPF');

    $cpfcnpj= '';
    //$cpfcnpj= coletar_dados_usuario("uraBeneCli/08",18);
    $cpfcnpj= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/08",18);
    if($cpfcnpj== '-1'){hangup();break;}

    //55
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'CPF/ CNPJ', $cpfcnpj);

    verbose("INFORMADO : ".$cpfcnpj);
    verbose("fhdjshdfslhjgdfsgh : ".strlen($cpfcnpj));

    if(strlen($cpfcnpj)>=11 && strlen($cpfcnpj)<=14){
        verbose("CNPJ/CPF DIGITADO CORRETAMENTE");
        inicializa_ambiente_novo_menu();
        checa_contrato($uniqueid, $origem, $cpfcnpj);
    }else{
        verbose("CLIENTE INFORMOU DE FORMA INCORRETA");
        if(retentar_dado_invalido("PRINCIPAL","uraBeneCli/03","OPCAO INVALIDA")){
            //57
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'OPCAO INVALIDA(INFORME O CPF/CNPJ)');

            M3_Emp_Deseja_Credenciar($uniqueid, $origem);
        }else{
            //58
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(INFORME O CPF/CNPJ)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","uraBeneCli/04","OPCAO INVALIDA");  
        }
    }
}

function checa_contrato($uniqueid, $origem, $cpfcnpj){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $retorno= validar_cnpj_cpf_cod($uniqueid, $origem, $cpfcnpj);
    
    $listProd= $retorno->{'listaProdutos'};
    $a='0';
    $audio='';
    foreach ($listProd as $nomeObjeto => $valorObjeto) {
        if($valorObjeto->{'ctr01'} !=''){
            verbose("CONTRATO ENCONTRADO: ".$nomeObjeto);
            $audio.="uraBeneCli/".$nomeObjeto.'&';
            $a++;
        }
    }
    $audio= rtrim($audio, '&');

    verbose("AUDIOOOOOOOOOOO: ".$audio);

    if($audio){
        verbose('AAAAAAAAAAAAA: '.$a);

        if($a=='1'){
            $audio= "uraBeneCli/Novo2Sing&".$audio."&FroUsu/Novo3Sing";
        }else{
            $audio= "uraBeneCli/Novo2&".$audio."&FroUsu/Novo3";
        }

        //87
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'RETORNO', 'CLIENTE POSSUI CONTRATO');

        verbose("O CLIENTE POSSUI CONTRATOS");

        $opcao='';
        $opcao=background($audio,1);
        if($opcao== '-1'){hangup();break;}
        tracking_canal_ativo($canal, $ddr, $ticket, $indice);
        
        if($opcao==1){
            verbose("A OPCAO DIGITADA : ".$opcao);
            EmpCli_Seleciona_contrato($uniqueid, $origem, $identificador, $retorno);
        }else{
            verbose("B OPCAO DIGITADA : ".$opcao);
            inicializa_ambiente_novo_menu();
            M3_Emp_Deseja_Credenciar_2($uniqueid, $origem, $cpfcnpj);
        }
    }else{
        verbose("CLIENTE NAO POSSUI CONTRATO");

        inicializa_ambiente_novo_menu();
        M3_Emp_Deseja_Credenciar_2($uniqueid, $origem, $cpfcnpj);
    }
}

function M3_Emp_Deseja_Credenciar_2($uniqueid, $origem, $cpfcnpj){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    if(strlen($cpfcnpj)==11){
        verbose("CLIENTE DIGITOU CPF");
        //56
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'RETORNO', 'CPF DIGITOS VALIDOS');

        //59
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'PERGUNTAR LGPD');

        $lgpd='';
        //$lgpd= coletar_dados_usuario("uraBeneCli/09",1);
        $lgpd= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/09",1);
        if($lgpd== '-1'){hangup();break;}

        if($lgpd=='1'){
            verbose("CLIENTE CONCORDOU COM A LEI LGPD");
            //60
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'RESPOSTA', 'ACEITOU LGPD');

            inicializa_ambiente_novo_menu();
            informa_telefone($uniqueid, $origem, $cpfcnpj);

            /*
            $telefone='';
            //$telefone=coletar_dados_usuario("uraBeneCli/11",11);
            $telefone=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/11",11);
            if($telefone== '-1'){hangup();break;}

            //if($telefone=='TIMEOUT' || strlen($telefone)<=10 || $telefone==''){
                //verbose("CLIENTE NÃO DIGITOU O NÚMERO DE CONTATO CORRETAMENTE");
                //$telefone=$origem;
            //}

            verbose("NUMERO A SER UTILIZADO : ".$telefone);
            //61
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'CONTATO', $telefone);

            $categoria='Deseja ser cliente';
            //$flagEnt='cliente';
            $flagEnt='prosp_empresa';  //SOLICITACAO RENAN
            $origemSolicitacao='URA_BENEFICIO_CLIENTE';
            $subCat='Prospect - URA';
            $protocolo= api_ucc_protocolo_v2($categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            //62
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PROTOCOLO', $protocolo);

            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'SOLICITACAO CONCLUIDA COM SUCESSO');

            playback("uraBeneCli/18");
            falar_alfa($protocolo);

            $equipe1= 'INSIDE SALES';
            $sigla1='';
            $inside= api_horario_atendimento($equipe1, $sigla1);
            $insiDisp=$inside->{'atendimentoDisponivel'};
            verbose('INSIDE SALES DISPONIVEL : '.$insiDisp);
            
            $equipe2= 'ATENDIMENTO PRIMEIRO NIVEL';
            $sigla2='';
            $primNivel= api_horario_atendimento($equipe2, $sigla2);
            $priDisp=$primNivel->{'atendimentoDisponivel'};
            verbose('ATENDIMENTO PRIMEIRO NIVEL DISPONIVEL : '.$priDisp);
        
            if($insiDisp=='S'){
                verbose("ENCAMINHADO PARA O INSIDE SALES");
                inicializa_ambiente_novo_menu();
                encaminha_inside_sales($uniqueid, $origem);
            }elseif ($priDisp=='S' && $insiDisp=='N') {
                verbose("ENCAMINHADO PARA O ATENDIMENTO PRIMEIRO NIVEL");
                inicializa_ambiente_novo_menu();
                encaminha_primeiro_nivel($uniqueid, $origem);
            }else{
                verbose("CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO");
                playback("FroCli/19");
                playback("FroCli/final_1");
                playback("FroCli/final_2");
                playback("FroCli/final_3");
                hangup();
        
            }
            */

        }else{
            verbose("CLIENTE DISCORDOU COM A LEI LGPD");
            //60
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'RESPOSTA', 'NAO ACEITOU LGPD');

            playback("uraBeneCli/12");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }

    }elseif(strlen($cpfcnpj)==14){
        verbose("CLIENTE DIGITOU CNPJ");
        //56
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'RETORNO', 'CNPJ DIGITOS VALIDOS');

        inicializa_ambiente_novo_menu();
        informa_telefone($uniqueid, $origem, $cpfcnpj);

        /*
        $telefone='';
        //$telefone=coletar_dados_usuario("uraBeneCli/11",11);
        $telefone=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/11",11);
        if($telefone== '-1'){hangup();break;}     
        
        //if($telefone=='TIMEOUT' || strlen($telefone)<=10 || $telefone==''){
            //verbose("CLIENTE NÃO DIGITOU O NÚMERO DE CONTATO CORRETAMENTE");
            //$telefone=$origem;
        //}
        verbose("NUMERO A SER UTILIZADO : ".$telefone);

        //61
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'CONTATO', $telefone);

        $categoria='Deseja ser cliente';
        $flagEnt='cliente';
        $origemSolicitacao='URA_BENEFICIO_CLIENTE';
        $subCat='Prospect - URA';
        $protocolo= api_ucc_protocolo_v2($categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);

        //62
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PROTOCOLO', $protocolo);

        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'SOLICITACAO CONCLUIDA COM SUCESSO');

        playback("uraBeneCli/18");
        falar_alfa($protocolo);
        playback("uraBeneCli/10");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
        exit;
        break;
        */

    }else{
        verbose("CLIENTE INFORMOU DE FORMA INCORRETA");
        if(retentar_dado_invalido("PRINCIPAL","uraBeneCli/03","OPCAO INVALIDA")){
            //57
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'OPCAO INVALIDA(INFORME O CPF/CNPJ)');

            M3_Emp_Deseja_Credenciar($uniqueid, $origem);
        }else{
            //58
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(INFORME O CPF/CNPJ)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","uraBeneCli/04","OPCAO INVALIDA");  
        }
    }
}

function informa_telefone($uniqueid, $origem, $cpfcnpj){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $telefone='';
    //$telefone=coletar_dados_usuario("uraBeneCli/11",11);
    $telefone=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/11",11);
    if($telefone== '-1'){hangup();break;}     
        
    //if($telefone=='TIMEOUT' || strlen($telefone)<=10 || $telefone==''){
        //verbose("CLIENTE NÃO DIGITOU O NÚMERO DE CONTATO CORRETAMENTE");
        //$telefone=$origem;
    //}
    verbose("NUMERO A SER UTILIZADO : ".$telefone);

    //61
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'CONTATO FORNECIDO', $telefone);

    $categoria='Deseja ser cliente';
    $flagEnt='prosCliente';
    $origemSolicitacao='URA_BENEFICIO_CLIENTE';
    $subCat='Prospect - URA';

    $protocolo= api_ucc_protocolo_v2($categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
    verbose("PROTOCOLO GERADO : ".$protocolo);

    /*$prot=protocolo_mensagem($processId, $categoria, $cpfcnpj, '', $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);

    $protocolo=$prot->{'protocolo'};
    $mensagem=$prot->{'message'};
    verbose("PROTOCOLO GERADO : ".$protocolo);
    verbose("MENSAGEM GERADA : ".$mensagem);

    if($mensagem && srtlen($protocolo)>=5){
        $indice++;
        verbose("JA EXISTE PROTOCOLO GERADO");
        tracking($canal, $ddr, $ticket, $indice, 'DESEJA SER CLIENTE VALECARD', 'PROTOCOLO', 'JA EXISTE PROTOCOLO GERADO');
    }
*/
    //62
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PROTOCOLO', $protocolo);

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'DESEJA_CONTRATAR', 'PERCURSO', 'SOLICITACAO CONCLUIDA COM SUCESSO');

    playback("uraBeneCli/18");
    falar_alfa($protocolo);
    /*playback("uraBeneCli/10");
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
    hangup();
    exit;
    break;*/

    $equipe1= 'INSIDE SALES';
    $sigla1='';
    $inside= api_horario_atendimento($equipe1, $sigla1);
    $insiDisp=$inside->{'atendimentoDisponivel'};
    verbose('INSIDE SALES DISPONIVEL : '.$insiDisp);
    
    $equipe2= 'ATENDIMENTO PRIMEIRO NIVEL';
    $sigla2='';
    $primNivel= api_horario_atendimento($equipe2, $sigla2);
    $priDisp=$primNivel->{'atendimentoDisponivel'};
    verbose('ATENDIMENTO PRIMEIRO NIVEL DISPONIVEL : '.$priDisp);

    if($insiDisp=='S'){
        verbose("ENCAMINHADO PARA O INSIDE SALES");
        inicializa_ambiente_novo_menu();
        encaminha_inside_sales($uniqueid, $origem, $protocolo);
    }elseif ($priDisp=='S' && $insiDisp=='N') {
        verbose("ENCAMINHADO PARA O ATENDIMENTO PRIMEIRO NIVEL");
        inicializa_ambiente_novo_menu();
        encaminha_primeiro_nivel($uniqueid, $origem, $protocolo);
    }else{
        verbose("CLIENTE LIGOU FORA DO HORARIO DE ATENDIMENTO");
        playback("FroCli/60");
        playback("FroCli/final_1");
        playback("FroCli/final_2");
        playback("FroCli/final_3");
        encerra_com_tracking($canal, $ddr, $ticket, $indice, "VALIDAR DADOS","uraBeneCli/04","OPCAO INVALIDA");
        hangup();
    }
}

function encaminha_inside_sales($uniqueid, $origem, $protocolo){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    $fila='500';

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);
    tracking_canal_ativo($canal, $ddr, $ticket, $indice);
    inserirprotocolobanco($origem,$protocolo);
    dial_return('gw02-kontac32/'.$fila); // PENDENCIA : para onde?

    $uniqueId_kontac= get_uniqueId_kontac($origem, '32');
    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

    canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

    inicializa_ambiente_novo_menu();
    pesquisa_satisfacao($uniqueid, $origem);
    hangup();
}

function encaminha_primeiro_nivel($uniqueid, $origem, $protocolo){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    $fila='206';

    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ATENDIMENTO TRANSFERIDO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDA PARA A FILA : '.$fila);
    tracking_canal_ativo($canal, $ddr, $ticket, $indice);

    inserirprotocolobanco($origem,$protocolo);
    dial_return('gw02-kontac33/'.$fila); // PENDENCIA : para onde?

    $uniqueId_kontac= get_uniqueId_kontac($origem, '33');
    tracking($canal, $ddr, $ticket, $indice, 'PÓS ATENDIMENTO HUMANO', 'UNIQUEID KONTAC', $uniqueId_kontac);

    canal_ativo_pesquisa_satisfacao($canal, $ddr, $ticket, $indice);

    inicializa_ambiente_novo_menu();
    pesquisa_satisfacao($uniqueid, $origem);
    hangup();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //19
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'ALTERAR STATUS DO USUARIO OU ADQUIRIR NOVOS PRODUTOS OU ALTERAR SENHA CLIENTE OU FATURA, VENCIMENTO E VALOR OU MENU INICIAL OU FALAR COM ATENDENTE');

    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneCli/48",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/48",1);
    if($opcao == '-1'){hangup();break;exit;}
    //20
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'RESPOSTA', $opcao);

    switch ($opcao) {
        case '1':
            verbose("MENU ALTERAR STATUS DO USUARIO");
            inicializa_ambiente_novo_menu();
            Menu_altera_stts_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        break;
        
        case '2':
            verbose("MENU ADQUIRIR NOVOS PRODUTOS");
            inicializa_ambiente_novo_menu();
            Menu_novos_prod($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        break;

        case '3':
            verbose("MENU ALTERAR SENHA CLIENTE");
            inicializa_ambiente_novo_menu();
            Menu_altera_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        break;

        case '4':
            verbose("MENU FATURA VENCIMENTO E VALOR");
            inicializa_ambiente_novo_menu();
            Menu_FVV_valida_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        break;

        case '5':
            verbose("MENU INICIAL");
            inicializa_ambiente_novo_menu();
            Menu_Principal($uniqueid, $origem);
        break;

        case '9':
            verbose("FALAR COM ATENDENTE");
            if($horaAtual >= '08:00' && $horaAtual <= '20:40'){
                $flagEnt='cliente';
                $origemSolicitacao='URA_BENEFICIO_CLIENTE';
                $processId='WKF_Atendimentos';
                $protocolo= protocolo_v2_v2($processId, '', $cnpjcpf, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                //$protocolo= api_ucc_protocolo_v2('', $cnpjcpf, '', $flagEnt, $origemSolicitacao, '', $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);
                //21
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PROTOCOLO', $protocolo);

                playback("uraBeneCli/18");
                falar_alfa($protocolo);
                playback("uraBeneCli/19");
                $fila='214';

                //23
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'MENU USUARIO', 'PERCURSO', 'ATENDIMENTO TRANSFERIDO PARA A FILA: '.$fila);

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
            verbose("CLIENTE SELECIONOU OPCAO INVALIDA");
            if(retentar_dado_invalido("VALIDAR DADOS","uraBeneCli/03","OPCAO INVALIDA")){
                //24
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'OPCAO INVALIDA(ALTERAR STATUS DO USUARIO OU ADQUIRIR NOVOS PRODUTOS OU ALTERAR SENHA CLIENTE OU FATURA, VENCIMENTO E VALOR OU MENU INICIAL OU FALAR COM ATENDENTE)');

                M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
            }else{
                //25
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(ALTERAR STATUS DO USUARIO OU ADQUIRIR NOVOS PRODUTOS OU ALTERAR SENHA CLIENTE OU FATURA, VENCIMENTO E VALOR OU MENU INICIAL OU FALAR COM ATENDENTE)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "VALIDAR DADOS","uraBeneCli/04","OPCAO INVALIDA");
            }
        break;
    }

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_altera_stts_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //83
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'INFORMAR SENHA DO CLIENTE');

    $senha='';
    //$senha= coletar_dados_usuario("uraBeneCli/22",6);
    $senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/22",6);
    if($senha == '-1'){hangup();break;exit;}

    //84
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'SENHA', 'DADOS CONFIDENCIAIS');

    $entidade='CLI';
    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
    $validaSenha= api_ucc_valida_senha($uniqueid, $contratoSelecionado, $origem, $senha, $entidade);
    verbose("RETORNO DA SENHA DIGITADA : ".$validaSenha);

    if($validaSenha){
        verbose("SENHA VALIDADA COM SUCESSO");
        //105
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'SENHA VALIDA');

        inicializa_ambiente_novo_menu();
        Menu_altera_stts($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
    }else{
        verbose("SENHA NÃO VALIDADA PELA API");
        if(retentar_dado_invalido("Menu_altera_stts_senha","uraBeneCli/21","SENHA INVALIDA")){
            //105
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'SENHA INVALIDA');

            Menu_altera_stts_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        }else{
            //108
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_stts_senha","uraBeneCli/04","SENHA INVALIDA");
        }
    }
    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_altera_stts($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //85
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', '1 - ATIVAR CARTAO/ 2 - BLOQUEAR CARTAO');

    $status='';
    //$status= coletar_dados_usuario("uraBeneCli/54",1);
    $status= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/54",1);
    if($status == '-1'){hangup();break;exit;}

    //86
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'RESPOSTA', $status);

    if($status==1 || $status==2){
        verbose("NOVO STATUS DO CARTAO : ".$status);
        inicializa_ambiente_novo_menu();
        Menu_stts_cartao($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status);
    }else{
        playback("uraBeneCli/03");
        verbose("CLIENTE INFORMOU OPCAO INVALIDA : ".$status);
        if(retentar_dado_invalido("Menu_altera_stts","uraBeneCli/20","OPCAO INVALIDA")){
            //103
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'OPCAO INVALIDA(1 ATIVAR CARTAO, 2 BLOQUEAR CARTAO)');

           Menu_altera_stts($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha); 
        }else{
            //104
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(1 ATIVAR CARTAO, 2 BLOQUEAR CARTAO)');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_stts","uraBeneCli/04","OPCAO INVALIDA"); 
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_stts_cartao($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    if($status=='1'){
        //87
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'PERCURSO', 'INFORMAR NUMERO DO CARTAO DO CLIENTE');
    }else{
        //97
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'PERCURSO', 'INFORMAR NUMERO DO CARTAO DO CLIENTE');
    }

    $cartao='';
    //$cartao= coletar_dados_usuario("uraBeneCli/55",19);
    $cartao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/55",19);
    if($cartao == '-1'){hangup();break;exit;}

    if($status=='1'){
        //88
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'NUMERO DO CARTAO', $cartao);
    }else{
        //98
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'NUMERO DO CARTAO', $cartao);
    }

    if(strlen($cartao)>= 17 && strlen($cartao)<= 19){
        verbose("DIGITOU A QUATIDADE CORRETA DE DÍGITOS");
        $valida_cartao= valida_cartao($uniqueid, $origem, $cartao, '', '');
        verbose("CARTAO VALIDO : ".$valida_cartao->{'cartaoValido'});
        verbose("NUM CARTAO: ".$cartao);

        if($valida_cartao->{'cartaoValido'}=="S"){

            if($status ==1){
                //89
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'RETORNO', 'CARTAO VALIDO');

                $status='A';
                verbose("CLIENTE SOLICITOU ATIVACAO DO CARTAO : ".$status);
            }else{
                //99
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'RETORNO', 'CARTAO VALIDO');

                $status='B';
                verbose("CLIENTE SOLICITOU BLOQUEIO DO CARTAO : ".$status);
            }

            $ent='';
            $ent="CLI";
            $cartaoId= $valida_cartao->{'cartaoId'};
            $cart_alterado= altera_status_cartao($uniqueid, $origem, $cartaoId, $status, $ent);
            $altStatus= $cart_alterado->{'statusAlterado'};

            if($altStatus=="S"){
                verbose("ALTERACAO DE STATUS DO CARTAO APROVADA");
                if($status=='A'){
                    $subCat='Ativar Cartão - URA';
                }elseif($status=='B'){
                    $subCat='Bloquear Cartão - URA ';
                }else{
                    $subCat='ERRO NÃO TRATADO';
                }
                verbose("SUBCATEGORIA : ".$subCat);
                $processId='WKF_Atendimentos';
                $categoria='Alterar Status de Cartão';
                $flagEnt='cliente';
                $origemSolicitacao='URA_BENEFICIO_CLIENTE';
                $protocolo= protocolo_v2_v2($processId, $categoria, $cnpjCpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
                //$protocolo= api_ucc_protocolo_v2($categoria, $cnpjCpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
                verbose("PROTOCOLO GERADO : ".$protocolo);
                if($status =='A'){
                    //92
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'PROTOCOLO', $protocolo);

                    playback("uraBeneCli/57");
                    falar_alfa($protocolo);
                }else{
                    //102
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'PROTOCOLO', $protocolo);

                    playback("uraBeneCli/58");
                    falar_alfa($protocolo);
                }                
                
                inicializa_ambiente_novo_menu();
                Menu_alt_stts_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status);
            }else{
                verbose("API NÃO ALTEROU O STATUS : ".$altStatus);
                playback("uraBeneCli/56");
                if(retentar_dado_invalido("Menu_stts_cartao","uraBeneCli/20","ALTERACAO INVALIDADA PELA API"))Menu_stts_cartao($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_stts_cartao","uraBeneCli/04","ALTERACAO INVALIDADA PELA API");
                }
            }

        }else{
            verbose("CARTÃO NAO VALIDADO PELA API");
            playback("uraBeneCli/56");
            if(retentar_dado_invalido("Menu_stts_cartao","uraBeneCli/20","CARTAO INVALIDADO PELA API")){
                if($status ==1){
                    //89
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'RETORNO', 'CARTAO INVALIDO');
    
                    $status='A';
                    verbose("CLIENTE SOLICITOU ATIVACAO DO CARTAO : ".$status);
                }else{
                    //99
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'RETORNO', 'CARTAO INVALIDO');
    
                    $status='B';
                    verbose("CLIENTE SOLICITOU BLOQUEIO DO CARTAO : ".$status);
                }

                Menu_stts_cartao($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status);
            }else{
                if($status ==1){
                    //91
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(NUMERO DO CARTAO)');
    
                    $status='A';
                    verbose("CLIENTE SOLICITOU ATIVACAO DO CARTAO : ".$status);
                }else{
                    //101
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - BLOQUEAR ', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(NUMERO DO CARTAO)');
    
                    $status='B';
                    verbose("CLIENTE SOLICITOU BLOQUEIO DO CARTAO : ".$status);
                }
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(API NAO IDENTIFICOU)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_stts_cartao","uraBeneCli/04","CARTAO INVALIDADO PELA API");
            }
        }

    }else{
        verbose("CLIENTE INFORMOU DADOS INVALIDOS");
        playback("uraBeneCli/56");
        if(retentar_dado_invalido("Menu_stts_cartao","uraBeneCli/20","CARTAO INVALIDO")){
            //90
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'OPCAO INVALIDA(NUMERO DO CARTAO)');

            Menu_stts_cartao($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status); 
        }else{
            //91
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(NUMERO DO CARTAO)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_stts_cartao","uraBeneCli/04","CARTAO INVALIDO");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_alt_stts_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status){
    verbose("FINAL DA ALTERA STATUS USUARIO");
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //93
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR_BLOQUEAR', 'PERCURSO', '9 PARA NOVA TRANSACAO OU DESLIGUE');
    
    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneCli/59",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/59",1);
    if($opcao == '-1'){hangup();break;exit;}

    //94
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR_BLOQUEAR', 'RESPOSTA', $opcao);

    if($opcao==9){

        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
    }else{
        verbose("CLIENTE DIGITOU UMA OPCAO INVALIDA");
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("Menu_alt_stts_final","uraBeneCli/20","OPCAO INVALIDA")){
            //96
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR_BLOQUEAR', 'PERCURSO', 'OPCAO INVALIDA(9 NOVA TRANSACAO OU DESLIGUE)');
            
            Menu_alt_stts_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $status);
        }else{
            //95
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR STATUS USUARIO - ATIVAR_BLOQUEAR', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(9 NOVA TRANSACAO OU DESLIGUE)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_alt_stts_final","uraBeneCli/04","OPCAO INVALIDA");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_novos_prod($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado){
    verbose("ENTROU NO MENU NOVOS PRODUTOS");
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //121
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PERCURSO', 'INFORMAR NUMERO DO CNPJ/ CPF');

    $cpfcnpj= '';
    //$cpfcnpj= coletar_dados_usuario("uraBeneCli/08",14);
    $cpfcnpj= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/08",14);
    if($cpfcnpj== '-1'){hangup();break;}
    verbose("INFORMADO : ".$cpfcnpj);

    //122
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'NUMERO DO CARTAO', $cpfcnpj);


    if(strlen($cpfcnpj)==11){
        verbose("CLIENTE DIGITOU CPF");
        //123
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'RETORNO', 'CPF DIGITOS VALIDOS');

        //126
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PERCURSO', 'PERGUNTAR LGPD');

        $lgpd='';
        //$lgpd= coletar_dados_usuario("uraBeneCli/09",1);
        $lgpd= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/09",1);
        if($lgpd== '-1'){hangup();break;}

        if($lgpd=='1'){
            verbose("CLIENTE CONCORDOU COM A LEI LGPD");
            //127
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'RESPOSTA', 'ACEITOU LGPD');

            $telefone='';
            //$telefone=coletar_dados_usuario("uraBeneCli/11",11);
            $telefone=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/11",11);
            if($telefone== '-1'){hangup();break;}

            //128
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'CONTATO FORNECIDO', $telefone);

            //if($telefone=='' || $telefone=='TIMEOUT'){
                //$telefone=$origem;
            //}
            $categoria='Adquirir novos produtos';
            $flagEnt='cliente';
            $origemSolicitacao='URA_BENEFICIO_CLIENTE';
            $subCat='Adquirir novos produtos - URA';
            $protocolo= api_ucc_protocolo_v2($categoria, $cnpjCpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);

            //129
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PROTOCOLO', $protocolo);

            playback("uraBeneCli/60");
            falar_alfa($protocolo);
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();

        }else{
            verbose("CLIENTE DISCORDOU COM A LEI LGPD");
            //127
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'RESPOSTA', 'ACEITOU LGPD');

            playback("uraBeneCli/12");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
        }

    }elseif(strlen($cpfcnpj)==14){
        verbose("CLIENTE DIGITOU CNPJ");
        //123
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'RETORNO', 'CNPJ/ CPF VALIDO');

        $telefone='';
        //$telefone=coletar_dados_usuario("uraBeneCli/11",11);
        $telefone=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/11",11);
        if($telefone== '-1'){hangup();break;}

        //128
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'CONTATO FORNECIDO', $telefone);

        $categoria='Adquirir novos produtos';
        $flagEnt='cliente';
        $origemSolicitacao='URA_BENEFICIO_CLIENTE';
        $subCat='Adquirir novos produtos - URA';
        $protocolo= api_ucc_protocolo_v2($categoria, $cnpjCpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, $telefone, $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);

        //129
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PROTOCOLO', $protocolo);
        
        playback("uraBeneCli/60");
        falar_alfa($protocolo);
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();

    }else{
        verbose("QUANTIDADE DE DÍGITOS INVALIDA");
        if(retentar_dado_invalido("PRINCIPAL","uraBeneCli/03","OPCAO INVALIDA")){
            //125
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PERCURSO', 'OPCAO INVALIDA(CPF OU CNPJ)');

            Menu_novos_prod($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        }else{
            //124
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ADQUIRIR NOVOS PRODUTOS', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CPF OU CNPJ)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "PRINCIPAL","uraBeneCli/04","OPCAO INVALIDA"); 
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_altera_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //130
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'INFORMAR SENHA DO CLIENTE');

    $senha='';
    //$senha= coletar_dados_usuario("uraBeneCli/40",8);
    $senha= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/40",8);
    if($senha== '-1'){hangup();break;exit;}

    //131
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'SENHA', 'INFORMACOES CONFIDENCIAIS');

    $entidade='CLI';
    $senhaValidada= api_ucc_valida_senha($uniqueid, $contratoSelecionado, $origem, $senha, $entidade);
    verbose("RETORNO DA API : ".$senhaValidada);
    if($senhaValidada){
        verbose("API VALIDOU A SENHA");
        //105
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'SENHA VALIDA');

        inicializa_ambiente_novo_menu();
        Menu_altera_senha_2($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
    }else{
        verbose("SENHA NÃO VALIDADA PELA API");
        if(retentar_dado_invalido("Menu_altera_senha","uraBeneCli/21","SENHAS DIGITADAS NAO FOI VALIDADA PELA API")){
            //107
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'PERCURSO', 'SENHA INVALIDA');

            Menu_altera_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        }else{
            //108
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha","uraBeneCli/04","SENHAS DIGITADAS NAO FOI VALIDADA PELA API");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_altera_senha_2($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //132
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'INFORMAR NOVA SENHA DO CLIENTE');

    $novaSenha='';
    //$novaSenha=coletar_dados_usuario("uraBeneCli/61",8);
    $novaSenha=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/61",8);
    if($novaSenha== '-1'){hangup();break;exit;}

    //133
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'NOVA SENHA', 'INFORMACOES CONFIDENCIAIS');

    //134
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'INFORMAR NOVA SENHA DO CLIENTE');

    $novaSenhaDnv='';
    //$novaSenhaDnv=coletar_dados_usuario("uraBeneCli/62",8);
    $novaSenhaDnv=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/62",8);
    if($novaSenhaDnv== '-1'){hangup();break;exit;}

    //135
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'NOVA SENHA', 'INFORMACOES CONFIDENCIAIS');

    if($novaSenhaDnv == $senha || $novaSenha == $senha){
        verbose("SENHA NOVA E ANTIGA SÃO IGUAIS");
        if(retentar_dado_invalido("Menu_altera_senha","uraBeneCli/SenhasIguais","SENHA NOVA E ANTIGA SÃO IGUAIS")){
            //139
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'SENHA INVALIDA');

            Menu_altera_senha_2($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
        }else{
            //140
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA IGUAL ANTERIOR');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha","uraBeneCli/04","SENHA NOVA E ANTIGA SÃO IGUAIS");
        }
        
    }elseif($novaSenha==$novaSenhaDnv){
        verbose("SENHAS DIGITADAS SÃO IGUAIS");
        $entidade= 'CLI';
        if(!canal_ativo()) exit();
        $novaSenhaValida=api_ucc_altera_senha($uniqueid, $origem, $contratoSelecionado, $novaSenha, $entidade);

        if($novaSenhaValida){
            verbose("SENHA VALIDADA PELA API");
            //136
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RETORNO', 'SENHA APROVADA');
            $processId='WKF_Atendimentos';
            $categoria='Alterar Senha do Cartão';
            $flagEnt='cliente';
            $origemSolicitacao='URA_BENEFICIO_CLIENTE';
            $subCat='Alterar Senha - URA';
            verbose("CONTRATO UTILIZADO : ".$contratoSelecionado);
            $protocolo=protocolo_v2_v2($processId, $categoria, $cnpjcpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            //$protocolo=api_ucc_protocolo_v2($categoria, $cnpjcpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
            verbose("PROTOCOLO GERADO : ".$protocolo);
            //141
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PROTOCOLO', $protocolo);

            playback("uraBeneCli/43");
            falar_alfa($protocolo);

            inicializa_ambiente_novo_menu();
            Menu_altera_senha_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $novaSenha);
        }else{
            verbose("SENHA NÃO VALIDADA PELA API");
            if(retentar_dado_invalido("Menu_altera_senha","uraBeneCli/21","SENHA NAO VALIDADA PELA API")){
                //136
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RETORNO', 'SENHA REPROVADA');

                Menu_altera_senha_2($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
            }else{
                //138
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha","uraBeneCli/04","SENHA NAO VALIDADA PELA API");
            }
        }
    }else{
        verbose("SENHAS DIGITADAS SÃO DIFERENTES");
        if(retentar_dado_invalido("Menu_altera_senha","uraBeneCli/21","SENHAS DIGITADAS SAO DIFERENTES")){
            //137
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'SENHA INVALIDA');
            Menu_altera_senha_2($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
        }else{
            //138
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha","uraBeneCli/04","SENHAS DIGITADAS SÃO DIFERENTES");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_altera_senha_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $novaSenha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //142
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', '9 PARA NOVA TRANSACAO OU DESLIGUE');

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneCli/59",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/59",1);
    if($opcao == '-1'){hangup();break;exit;}

    //143
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'RESPOSTA', $opcao);

    if($opcao==9){
        verbose("OPCAO ESCOLHIDA : ".$opcao);
        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
    }else{
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("Menu_altera_senha_final","uraBeneCli/20","OPCAO INVALIDA")){
            //144
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'OPCAO INVALIDA(9 NOVA TRANSACAO OU DESLIGUE)');
            Menu_altera_senha_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $novaSenha);
        }else{
            //145
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'ALTERAR SENHA', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(9 NOVA TRANSACAO OU DESLIGUE)');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha_final","uraBeneCli/04","OPCAO INVALIDA");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_FVV_valida_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    //146
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PERCURSO', 'INFORMAR SENHA DO CLIENTE');

    $senha='';
    //$senha=coletar_dados_usuario("uraBeneCli/22",5);
    $senha=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/22",5);
    if($senha == '-1'){hangup();break;exit;}

    //147
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'SENHA', 'INFORMACOES CONFIDENCIAIS');

    $entidade='CLI';
    verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
    verbose("CNPJCPF : ".$cnpjcpf);
    $senhaValidada= api_ucc_valida_senha($uniqueid, $contratoSelecionado, $origem, $senha, $entidade);
    if($senhaValidada){
        verbose("SENHA VALIDADA : ".$senhaValidada);
        //105
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'SENHA VALIDA');
        
        inicializa_ambiente_novo_menu();
        Menu_fat_venc_val($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha);
    }else{
        if(retentar_dado_invalido("Menu_FVV_valida_senha","uraBeneCli/21","SENHA NAO VALIDADA PELA API")){
            //105
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'RETORNO', 'SENHA INVALIDA');

            Menu_FVV_valida_senha($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
        }else{
            //108
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'VALIDAR SENHA CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS SENHA INVALIDA');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_FVV_valida_senha","uraBeneCli/04","SENHA NAO VALIDADA PELA API");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_fat_venc_val($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $faturas=busca_faturas_cliente($cnpjcpf);
    if($faturas->{'possuiFaturaAtrasada'}=='S'){
        verbose("POSSUI FATURAS ATRASADAS");
        inicializa_ambiente_novo_menu();
        Menu_falar_val_datas($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);

    }elseif ($faturas->{'possuiFaturaAtrasada'}=='N') {
        verbose("NÃO POSSUI FATURAS ATRASADAS");
        playback("uraBeneCli/SemFat");
        inicializa_ambiente_novo_menu();
        Menu_fat_venc_val_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);
        
    }else{
        verbose("API NAO RETORNOU FATURAS");
        playback("uraBeneCli/21");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_falar_val_datas($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    verbose("ENTROU AQUI");
    $lista=$faturas->{'listaFatura'};
    $qntd_faturas= count($lista);
    verbose("QUANTIDADE DAS FATURAS : ".$qntd_faturas);

    for($fat=0; $fat<$qntd_faturas; $fat++){
        $lista1=$faturas->{'listaFatura'}[$fat];
        $vencimento= $lista1->{'vencimento'};
        $valor=$lista1->{'valor'};

        //148
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'RETORNO', 'VALOR: '.$valor.'/DATA: '.$vencimento);

        verbose("FALANDO VALOR DA FATURA : ".$valor);
        playback("uraBeneCli/63_1");
        falar_valor($valor);
        verbose("FALANDO DATA DE VENCIMENTO DA FATURA : ".$vencimento);
        playback("uraBeneCli/63_2");
        falar_data($vencimento);

        //148.2
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PERCURSO', 'DADOS INFORMADOS COM SUCESSO');
    }

    if($faturas->{'possuiFaturaAtrasada'}=='S'){
        verbose("CLIENTE POSSUI FATURAS VENCIDAS");
        //149
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'RETORNO', 'TEM FATURA VENCIDA');

        playback("uraBeneCli/64");
        inicializa_ambiente_novo_menu();
        Menu_fat_venc_val_final_alternativo($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);
        
    }else{
        verbose("CLIENTE NÃO POSSUI FATURAS VENCIDAS");
        //149
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'RETORNO', 'NAO TEM FATURA VENCIDA');

        playback("uraBeneCli/SemFat");
        inicializa_ambiente_novo_menu();
        Menu_fat_venc_val_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_fat_venc_val_final_alternativo($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    verbose("FINAL DA FATURA VENCIMENTO VALOR");
    //154
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PERCURSO', '9 PARA NOVA TRANSACAO OU DESLIGUE');
    
    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneCli/59",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/59",1);
    if($opcao == '-1'){hangup();break;exit;}
    //155
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'RESPOSTA', $opcao);

    if($opcao==9){
        verbose("OPCAO ESCOLHIDA : ".$opcao);
        $processId='WKF_Atendimentos';
        $categoria='Fatura, Vencimento e Valor';
        $flagEnt='cliente';
        $origemSolicitacao='URA_BENEFICIO_CLIENTE';
        $subCat='Fatura, Vencimento e Valor - URA';
        $protocolo= api_ucc_protocolo_v2($processId, $categoria, $cnpjcpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        //$protocolo= api_ucc_protocolo_v2($categoria, $cnpjcpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);

        //156
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PROTOCOLO', $protocolo);

        playback("uraBeneCli/18");
        falar_alfa($protocolo);
        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
    }else{
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("Menu_fat_venc_val_final","uraBeneCli/20","OPCAO INVALIDA")){
            //157
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PERCURSO', 'OPCAO INVALIDA(9 NOVA TRANSACAO OU DESLIGUE)');

            Menu_fat_venc_val_final_alternativo($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);
        }
        else{
            playback("uraBeneCli/04");
            //158
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(9 NOVA TRANSACAO OU DESLIGUE)');

            $categoria='Fatura, Vencimento e Valor';
            $flagEnt='cliente';
            $origemSolicitacao='URA_BENEFICIO_CLIENTE';
            $subCat='Fatura, Vencimento e Valor - URA';
            $protocolo= api_ucc_protocolo_v2($categoria, $cnpjcpf, $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '');
            verbose("PROTOCOLO GERADO : ".$protocolo);

            //159
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FATURA/ VENCIMENTO/ VALOR', 'PROTOCOLO', $protocolo);

            playback("uraBeneCli/18");
            falar_alfa($protocolo);
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();
            exit;
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function Menu_fat_venc_val_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    verbose("FINAL DA FATURA VENCIMENTO VALOR");
    
    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneCli/59",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/59",1);
    if($opcao == '-1'){hangup();break;exit;}

    if($opcao==9){
        verbose("OPCAO ESCOLHIDA : ".$opcao);
        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
    }else{
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("Menu_fat_venc_val_final","uraBeneCli/20","OPCAO INVALIDA"))Menu_fat_venc_val_final($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado, $senha, $faturas);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'EMPRESA CLIENTE', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_fat_venc_val_final","uraBeneCli/04","OPCAO INVALIDA");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M1_Perda_ou_roubo($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;


    $identificador= '';
    //$identificador= coletar_dados_usuario("uraBeneCli/07",14);
    $identificador= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/07",14);
    if($identificador == '-1'){hangup();break;}
    verbose("INFORMADO : ".$identificador);

    $retorno= validar_cnpj_cpf_cod($uniqueid, $origem, $identificador);

    if(strlen($identificador)<11 && $identificador!= 'TIMEOUT'){
        verbose("CODIGO INFORMADO : ".$identificador);
        if($retorno->{'clienteBeneficio'}== "N" && $retorno->{'codClienteValidado'}== "S"){
            playback("uraBeneCli/51");
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
            hangup();

        }elseif($retorno->{'codClienteValidado'}== "N"){
            verbose("CLIENTE NÃO VALIDADO");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/03","CLIENTE NÃO VALIDADO"))M1_Perda_ou_roubo($uniqueid, $origem);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","CLIENTE NÃO VALIDADO");
                }

        }elseif($retorno->{'clienteBeneficio'}== "S" && $retorno->{'codClienteValidado'}== "S"){
            verbose("ENCAMINHANDO PARA O MENU PERDA E ROUBO");
            $contratoSelecionado= $identificador;
            inicializa_ambiente_novo_menu();
            M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);

        }else{
            verbose("CLIENTE NÃO IDENTIFICADO NA BASE");
            playback("uraBeneCli/03");
            if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))M1_Perda_ou_roubo($uniqueid, $origem);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
            }
        }

    }elseif(strlen($identificador)==11 || strlen($identificador)==14){
        verbose("CLIENTE DIGITOU CNPJ OU CPF : ".$identificador);

        if($retorno->{'clienteBeneficio'}== "S" && $retorno->{'codClienteValidado'}){
            verbose("CLIENTE É BENEFICIO");
            inicializa_ambiente_novo_menu();
            PR_lista_opcoes($uniqueid, $origem, $retorno);

        }else{
            if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","CLIENTE NÃO É BENEFICIO"))M1_Perda_ou_roubo($uniqueid, $origem);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","CLIENTE NÃO É BENEFICIO");
            }
        }
    }else{
        if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","DADOS DIGITADOS INCORRETAMENTE"))M1_Perda_ou_roubo($uniqueid, $origem);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","DADOS DIGITADOS INCORRETAMENTE");
        }
    }
}

function PR_lista_opcoes($uniqueid, $origem, $retorno){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $contratos= $retorno->{'listaProdutos'};
    $audioDinamico='';

    foreach ($contratos as $key => $value){
        if($value->{'ctr01'} !=''){
            verbose("CONTRATOS : ".$key);
            $audioDinamico.= "uraBeneCli/".$key."&";
        }
    }
    
    if($audioDinamico==''){
        verbose("CLIENTE NÃO POSSUI NENHUM CONTRATO");
        if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/100","CLIENTE NÃO É BENEFICIO"))M1_Perda_ou_roubo($uniqueid, $origem);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","CLIENTE NÃO É BENEFICIO");
        }
    }
    
    verbose("AUDIO A SER REPRODUZIDO : ".$audioDinamico);
    
    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneCli/52",1);
    $opcao=background($audioDinamico, 1);
    if($opcao == '-1'){hangup();break;exit;}
    $cnpjcpf= $retorno->{'cpfCpnj'};

    switch ($opcao){
        case '1':
            verbose("ENTROU");
            $contratoSelecionado=$contratos->{'alimentacao'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
        break;

        case '2':
            $contratoSelecionado=$contratos->{'convenio'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }        
        break;
            
        case '3':
            $contratoSelecionado=$contratos->{'refeicao'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
                
            break;

        case '4':
            $contratoSelecionado=$contratos->{'bonus'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
        break;

        case '5':
            $contratoSelecionado=$contratos->{'farmacia'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
        break;

        case '6':
            $contratoSelecionado=$contratos->{'viagem'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }                    
        break;

        case '7':
            $contratoSelecionado=$contratos->{'combustivel'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("M1_Perda_ou_roubo","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_ou_roubo","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
        break;

        case '8':
            $contratoSelecionado=$contratos->{'valecardLogistica'}->{'ctr01'};
            if($contratoSelecionado){
                //$contratoSelecionado = preg_replace("/[^0-9]/", "",$contratoSelecionado);
                verbose("CONTRATO SELECIONADO : ".$contratoSelecionado);
                inicializa_ambiente_novo_menu();
                M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            }else{
                playback("uraBeneCli/03");
                if(retentar_dado_invalido("PR_lista_opcoes","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
                else {
                    $indice++;
                    tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                    encerra_com_tracking($canal, $ddr, $ticket, $indice, "PR_lista_opcoes","uraBeneCli/04","OPCAO INVALIDA");
                }
            }
        break;
            
        default:
            playback("uraBeneCli/03");
            if(retentar_dado_invalido("PR_lista_opcoes","uraBeneCli/20","OPCAO INVALIDA"))PR_lista_opcoes($uniqueid, $origem, $retorno);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "PR_lista_opcoes","uraBeneCli/04","OPCAO INVALIDA");
            }
        break;
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $a= valida_cartao($uniqueid, $origem, $contratoSelecionado, '', '');
    if($a->{'statusCartao'}=='A'){
        $senha='';
        //$senha=coletar_dados_usuario("uraBeneCli/22",5);
        $senha=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/22",5);
        if($senha == '-1'){hangup();break;exit;}

        $entidade='CLI';
        $senhaValidada= api_ucc_valida_senha($uniqueid, $contratoSelecionado, $origem, $senha, $entidade);
        if($senhaValidada){
            verbose("SENHA VALIDADA : ".$senhaValidada);

            inicializa_ambiente_novo_menu();
            M1_Perda_roubo_senha_val($uniqueid, $origem, $retorno, $contratoSelecionado, $senha);
        }else{
            if(retentar_dado_invalido("M1_Perda_roubo_senha","uraBeneCli/21","SENHA NAO VALIDADA PELA API"))M1_Perda_roubo_senha($uniqueid, $origem, $retorno, $contratoSelecionado);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(SENHA INVALIDA)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_roubo_senha","uraBeneCli/04","SENHA NAO VALIDADA PELA API");
            }
        }
    }else{
        verbose("CARTAO JA SE ENCONTRA BLOQUEADO OU SINISTRADO");
        playback("uraBeneCli/CartBloq");
        playback("uraBeneCli/04");
        playback("uraBeneCli/05");
        $indice++;
        tracking($canal, $ddr, $ticket, $indice, 'FINALIZACAO DA LIGACAO', 'PERCURSO', 'URA FINALIZOU A LIGACAO');
        hangup();
        exit;
    }
    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M1_Perda_roubo_senha_val($uniqueid, $origem, $retorno, $contratoSelecionado, $senha){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $cartao='';
    //$cartao= coletar_dados_usuario("uraBeneCli/55",19);
    $cartao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/55",19);
    if($cartao == '-1'){hangup();break;exit;}

    if(strlen($cartao)>=17){
        verbose("CLIENTE DIGITOU A QUANTIDADE CORRETA DE DIGITOS");
        $valida_cartao= valida_cartao($uniqueid, $origem, $cartao, '', '');
        verbose("CARTAO INFORMADO : ".$cartao);
        verbose("RETORNO DA API : ".$valida_cartao->{'cartaoValido'});
    
        if($valida_cartao->{'cartaoValido'}=="S"){
            verbose("CARTAO ".$cartao." VALIDADO PELA API");
            inicializa_ambiente_novo_menu();
            M1_Perda_roubo_cartao($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao);
        }else{
            if(retentar_dado_invalido("M1_Perda_roubo_senha_val","uraBeneCli/20","CARTAO NAO VALIDADA PELA API"))M1_Perda_roubo_senha_val($uniqueid, $origem, $retorno, $contratoSelecionado, $senha);
            else {
                $indice++;
                tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CARTAO NAO VALIDO PELA API)');

                encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_roubo_senha_val","uraBeneCli/04","CARTAO INVALIDO PELA API");
            }
        }
    }else{
        if(retentar_dado_invalido("M1_Perda_roubo_senha_val","uraBeneCli/20","CARTAO NAO VALIDADA PELA API"))M1_Perda_roubo_senha_val($uniqueid, $origem, $retorno, $contratoSelecionado, $senha);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CARTAO INVALIDO PELA API)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_roubo_senha_val","uraBeneCli/04","CARTAO NAO VALIDADA PELA API");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M1_Perda_roubo_cartao($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;
    
    $opcao='';
    //$opcao= coletar_dados_usuario("uraBeneCli/23",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/23",1);
    if($opcao == '-1'){hangup();break;exit;}

    if($opcao==1){
        verbose("CLIENTE SOLICITOU O CANCELAMENTO DO CARTAO");
        $ent= "CLI";
        $novo_status='C';
        $cartaoId= $valida_cartao->{'cartaoId'};
        $alt_status_cartao=altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent);

        $categoria='Cancelamento de Cartão';
        $flagEnt='cliente';
        $origemSolicitacao='URA_BENEFICIO_CLIENTE';
        $subCat='Perda ou Roubo - URA';
        $protocolo= api_ucc_protocolo_v2($categoria, '', $contratoSelecionado, $flagEnt, $origemSolicitacao, $subCat, $origem, '', $uniqueid);
        verbose("PROTOCOLO GERADO : ".$protocolo);

        verbose("RETORNO API ALTERA STATUS : ".$alt_status_cartao->{'statusAlterado'});
        verbose("NUMERO DO CARTAO : ".$cartao);
        playback("uraBeneCli/44_1");
        falar_alfa($cartao);
        playback("uraBeneCli/44_2");
        falar_alfa($protocolo);

        inicializa_ambiente_novo_menu();
        M1_Perda_roubo_cartao_final($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao);
    }elseif($opcao==2){
        verbose("CLIENTE CANCELOU A SOLICITACAO DO CANCELAMENTO");
        playback("uraBeneCli/04");

        inicializa_ambiente_novo_menu();
        M1_Perda_roubo_cartao_final($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao);
    }else{
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("M1_Perda_roubo_cartao","uraBeneCli/20","OPCAO INVALIDA"))M1_Perda_roubo_cartao($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(CARTAO INVALIDO PELA)');

            encerra_com_tracking($canal, $ddr, $ticket, $indice, "M1_Perda_roubo_cartao","uraBeneCli/04","CARTAO NAO VALIDADA PELA API");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function M1_Perda_roubo_cartao_final($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    $opcao='';
    //$opcao=coletar_dados_usuario("uraBeneCli/59",1);
    $opcao=coleta_dados2($canal, $ddr, $ticket, $indice,"uraBeneCli/59",1);
    if($opcao == '-1'){hangup();break;exit;}

    if($opcao==9){
        verbose("OPCAO ESCOLHIDA : ".$opcao);
        inicializa_ambiente_novo_menu();
        M3_EmpCli_validado($uniqueid, $origem, $cnpjcpf, $retorno, $contratoSelecionado);
    }else{
        playback("uraBeneCli/03");
        if(retentar_dado_invalido("Menu_altera_senha_final","uraBeneCli/20","OPCAO INVALIDA"))M1_Perda_roubo_cartao_final($uniqueid, $origem, $retorno, $contratoSelecionado, $senha, $cartao, $valida_cartao);
        else {
            $indice++;
            tracking($canal, $ddr, $ticket, $indice, 'PERDA OU ROUBO', 'PERCURSO', 'TENTATIVAS EXCEDIDAS(OPCAO INVALIDA)');
            
            encerra_com_tracking($canal, $ddr, $ticket, $indice, "Menu_altera_senha_final","uraBeneCli/04","OPCAO INVALIDA");
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//PESQUISA DE SATISFACAO

function pesquisa_satisfacao($uniqueid, $origem){
    global $canal, $ddr, $ticket, $indice, $horaAtual;

    tracking_canal_ativo($canal, $ddr, $ticket, $indice);

    //09
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'LIGACAO CONTINUADA');

    //10
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'ATENDIMENTO TRATOU A SOLICITACAO?');

    $opcao='';
    //$opcao= coletar_dados_usuario("Fraseologia/PS01",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"Fraseologia/PS01",1);
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
        playback("uraBeneCli/03");
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

    //14
    $indice++;
    tracking($canal, $ddr, $ticket, $indice, 'PESQUISA DE SATISFACAO', 'PERCURSO', 'INFORMAR PERGUNTA(AVALIACAO 1 A 5)');

    $opcao='';
    //$opcao= coletar_dados_usuario("Fraseologia/PS02",1);
    $opcao= coleta_dados2($canal, $ddr, $ticket, $indice,"Fraseologia/PS02",1);
    if($opcao == '-1'){hangup();break;exit;}

    if($opcao>='1' && $opcao<='5'){
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
        playback("uraBeneCli/03");
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

tracking($canal, $ddr, $ticket, $indice, 'INICIO', 'PERCURSO', 'LIGACAO DESLIGADA PELO CLIENTE');
return 0;
hangup();
break;
exit();
?>