<?php
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

$configs = include('config_beneficio_cli.php');

//API DE TOKEN

function api_login_token(){ 

    global $configs;
    $usuario=$configs['user_login'];
    $senha =$configs['pass_login'];
    
    $url = $configs['server_api'].$configs['url_login'];
    $ch = curl_init($url);
    $data = array(
        'login' => $usuario,
        'password' => $senha
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 400);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $obj = json_decode($result);    

    if(property_exists($obj,'token')) return $obj->{'token'};
    else return 'error';
}


function api_horario_atendimento($equipe, $sigla){
    global $configs;
    $token=api_login_token();

    if($token != 'error'){//token retornado com sucesso
    $url = $configs['server_api'].$configs['url_horario'];
    $ch = curl_init($url);
    //echo($url)."\n";

    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        'equipe'=> $equipe,
        'siglaUra'=> $sigla
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);

    $obj = json_decode($result);
    //var_dump($obj);


    $horaInicio = $obj->{'horaInicio'};
    $horaFim = $obj->{'horaFim'};
    $diaSemanaInicio = $obj->{'diaSemanaInicio'};
    $diaSemanaFim = $obj->{'diaSemanaFim'};

    //Setando o time zone
    date_default_timezone_set('America/Sao_Paulo');
    //Definindo a hora e minuto atual
    $horaAtual = date('H:i');

    $data = date('Y-m-d');
    //Definindo o dia da semana atual como números
    $diaSemana_numero = date('w', strtotime($data));
    $diaSemana_numero = $diaSemana_numero +1;

    }

    //Variaveis para teste
    //$horaAtual = '20:00';
    //$diaSemana_numero = '7';

    return $obj;

    //Comparação com os horarios de atendimento
    /*if($horaAtual >= $horaInicio && $horaAtual <= $horaFim && $diaSemana_numero >= $diaSemanaInicio && $diaSemana_numero <= $diaSemanaFim){
        return true;
    }   else return false;*/
}

///////////////////////////////////////////////////////////////////////////////
//API 01 VALIDAR CODIGO/CNPJ/CPF DO CLIENTE
function validar_cnpj_cpf_cod($uniqueid, $origem, $digitado){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_valida_cnpjcpf'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    if(strlen($digitado)==14 ||strlen($digitado)==11){
        $cnpjcpf=$digitado;
        $codigo='';
    }else{
        $codigo=$digitado;
        $cnpjcpf='';
    }


    $data = array(
        "codCliente"=> $codigo,
        "cpfCnpj"=> $cnpjcpf,
        "ligacaoId"=> $uniqueid,
        "numeroOrigem"=> $origem
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);
    return $obj;

}

///////////////////////////////////////////////////////////////////////////////
//API 02 ALTERAR STATUS CARTAO
function altera_status_cartao($uniqueid, $origem, $cartaoId, $novo_status, $ent){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_alt_status_cartao'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cartaoId"=> $cartaoId,
        "entidade"=> $ent,
        "ligacaoId"=> $uniqueid,
        "novoStatusCartao"=> $novo_status,
        "numeroOrigem"=>$origem
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
//API 03 VALIDAR CARTAO
function valida_cartao($uniqueid, $origem, $identificador, $cnpj, $contrato){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_valida_cartao'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    if(strlen($identificador)>=17){
        $tipoIdentificacao='CARTAO';
    }else{
        $tipoIdentificacao='ID';
    }

    $data = array(

        'cnpjCliente'=> $cnpj,
        'contratoCliente'=> $contrato,
        'identificacao'=> $identificador,
        'ligacaoId'=> $uniqueid,
        'numeroOrigem'=> $origem,
        'tipoIdentificacao'=> $tipoIdentificacao,

    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //$c= $obj->{'cartaoValido'};

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj;    
}

///////////////////////////////////////////////////////////////////////////////
//API 04 VALIDA PROD PELO CPF
function valida_prod_cpf($uniqueid, $origem, $cpf){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_valida_prod_cpf'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cpf"=>$cpf,
        "ligacaoId"=>$uniqueid,
        "numeroOrigem"=>$origem
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
//API 05 BUSCA FATURAS CLI
function busca_faturas_cliente($cnpjcpf){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_busca_faturas_cli'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cpfCnpj"=>$cnpjcpf,
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj= json_decode($result);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    //var_dump($obj);

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
//API 05,2 VALIDAR CPF/CNPJ/COD CLIENTE FROTA
function valida_cli_frota($uniqueid, $origem, $informado){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_valida_cli_frota'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    if(strlen($informado)<11){
        $cod=$informado;
        $cpfCnpj='';
    }else{
        $cod='';
        $cpfCnpj=$informado;
    }

    $data = array(
        "ligacaoId"=>$uniqueid,
        "numeroOrigem"=>$origem,
        "codCliente"=>$cod,
        "cpfCnpj"=>$cpfCnpj
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj= json_decode($result);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    //var_dump($obj);
    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
//API 06 CANCELAR USU CARTAO
function cancela_usu_cartao($uniqueid, $origem, $ent, $perda_roubo, $cartaoId){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_cancela_usu_cartao'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cartaoId"=>$cartaoId,
        "entidade"=>$ent,
        "ligacaoId"=>$uniqueid,
        "numeroOrigem"=>$origem,
        "perdaRoubo"=> $perda_roubo
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj; 
}

///////////////////////////////////////////////////////////////////////////////
//API 07 GERAR NOVA SENHA USU
function nova_senha_usu($num_cartao){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_nova_senha_usu'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "numCartao"=>$num_cartao
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj; 
}

///////////////////////////////////////////////////////////////////////////////
//API 08 PERGUNTAS PID
function pergunta_pid($uniqueid, $origem, $cartaoId, $valor1, $valor2, $valor3){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_pergunta_pid'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "ligacaoId"=> $uniqueid,
        "numeroOrigem"=> $origem,
        "cartaoId"=> $cartaoId,
        "perguntaValor"=> [
	[
            "pergunta"=> 1,
            "valor"=> $valor1
        ],[
            "pergunta"=> 2,
            "valor"=> $valor2
        ],[
            "pergunta"=> 3,
            "valor"=> $valor3
        ]
	]
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj; 
}

///////////////////////////////////////////////////////////////////////////////
//API 09 VALIDAR CVV CARTAO
function valida_cvv_cartao($cvv, $cartaoId){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_valida_cvv_cartao'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cartaoId"=> $cartaoId, //FLOAT OU INT
        "codigoCvvCartao"=> $cvv
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);
    //echo $obj->{'cvvValidado'}."\n";

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
// API 10 POSSUI CVV
function possuiCvv($cartaoId, $uniqueId, $origem){
    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_bene_possui_cvv'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;

    $data = array(
        "cartaoId"=> $cartaoId,
        "ligacaoId"=> $uniqueId,
        "numeroOrigem"=> $origem
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);

    $obj = json_decode($result);
    //var_dump($obj);

    //if($obj->{'possuiContrato'} =='S') return true;
    //else return false;

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
//API 11 VALIDAR TRANSAÇÕES NEGADAS
function vld_trnscao_ngd($cartao){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_api_valida_transacao_negada'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        "cartao"=>$cartao
    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);    

    $obj = json_decode($result);

    //var_dump($obj);
    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
/// API 12 BUSCA SALDO USU
function busca_saldo_usu($cartaoId){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_api_busca_saldo_usu'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        "cartaoId"=>$cartaoId
    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);    

    $obj = json_decode($result);

    //var_dump($obj);

    //$limite= count($obj->{'saldos'});
    //echo $limite."\n";

    //$saldos= $obj->{'saldos'};
    //var_dump($saldos);

    //$Contadorlimites= count($obj->{'saldos'});
    //$limites= $obj->{'saldos'};

    //foreach ($limites as $value) {
    //    var_dump($value);
    //    echo $value->{'limite'}."\n";
    //    echo $value->{'saldo'}."\n";
    //}

    return $obj;
}

///////////////////////////////////////////////////////////////////////////////
// API DISTRIBUIR VALORES
function distribui_valores($uniqueid, $origem, $cartaoId){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_api_distribui_valores'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        "cartaoId"=>$cartaoId,
        "ligacaoId"=>$uniqueid,
        "numeroOrigem"=>$origem
    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);    

    $obj = json_decode($result);

    //var_dump($obj);
    return $obj;    
}

///////////////////////////////////////////////////////////////////////////////
// API UCC DE VALIDAR SENHA
function api_ucc_valida_senha($uniqueid, $cnpjcpf, $origem, $senha, $entidade){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_ucc_valida_senha'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: text/plain';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        'entidade'=>$entidade,
        'ligacaoId'=>$uniqueid,
        'login'=>$cnpjcpf,
        'numeroOrigem'=>$origem,
        'senha'=>$senha
    );
    
    $payload = json_encode($data);
    //echo $payload;
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    
    $obj = json_decode($result);

    //var_dump($obj);

    if($obj->{'senhaValidada'} == 'S') return $obj->{'senhaValidada'};
    else return false;

}

///////////////////////////////////////////////////////////////////////////////
// COMUM CONTROLER ABRIR PROTOCOLO NO EASY
function api_ucc_protocolo_easy($processId, $cnpjcpf, $origem){

    global $configs;
    $token=api_login_token();
    
    $url = $configs['server_api'].$configs['url_ucc_abertura_ptcl_easy'];
    //echo $url."\n";
    $ch = curl_init($url);


    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: text/plain';
    $headers[] = 'X-Auth-Token: '.$token;

    switch($processId){
        case 'WKF_Prospect':
        $entidade ='flag_entidade_pros_estab_oc';
        break;
        
        case 'WKF_PROTOCOLO_PAI':
        $entidade='flag_entidade_pros_clie_oc';
        break;

        default:
        $entidade='flag_entidade_pros_clie_oc';
        break;
    }

    $data = array(

        'processId'=>$processId,
        'origemSolicitacao'=>'URA',
        'empresa'=>'Agili',
        'cnpj'=>$cnpjcpf,
        'telefone'=>$origem,
        'flagEntidade'=>$entidade
    );

    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    //var_dump($result);

    curl_close($ch);    

    $obj = json_decode($result);
    //var_dump($obj);

    if(isset($obj ->{'PROTOCOLO_ATENDIMENTO'})) return $obj ->{'PROTOCOLO_ATENDIMENTO'};
    else return 0;
    //return '255548';
}

//////////////////////////////////////////////////////////////////////////////
//API UCC ALTERA SENHA 
function api_ucc_altera_senha($uniqueid, $origem, $cnpjcpf, $novaSenha, $entidade){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_ucc_altera_senha'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: text/plain';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        'entidade'=>$entidade,
        'ligacaoId'=>$uniqueid,
        'login'=>$cnpjcpf,
        'novaSenha'=>$novaSenha,
        'numeroOrigem'=>$origem
    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    
     $obj = json_decode($result);
    
    // var_dump($obj);

    if($obj->{'senhaAlterada'}=='S') return true;
    else return false;
}

//////////////////////////////////////////////////////////////////////////////
//API UCC PROTOCOLO EASY NOVA
function api_ucc_protocolo_v2($categoria, $cnpjCpf, $contrato_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, $telInfo, $ligacaoId){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_ucc_protocolo_v2'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        'processId'=>'WKF_Prospect',
        'empresa'=>'VALECARD',
        'origemSolicitacao'=>$origemSolicitacao,
        'flagEntidade'=>$flagEnt,
        'categoria'=>$categoria,
        'subcategoria'=>$subCat,
        'cnpj_cpf'=>$cnpjCpf,
        'contrato_cartao'=>$contrato_cartao,
        'telefone'=>$origem,
        'telefoneInformado'=>$telInfo,
        'ligacaoId'=>$ligacaoId

    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    
    $obj = json_decode($result);
    
    //var_dump($obj);

    if($obj->{'message'}=="Já Existe Solicitação Aberta com o CNPJ informado! ")return $obj->{'protocolo'};
    else return $obj ->{'protocolo'};
}

function protocolo_v2_v2($processId, $categoria, $cnpjCpf, $contrato_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, $telInfo, $ligacaoId){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_ucc_protocolo_v2'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        'processId'=>$processId,
        'empresa'=>'VALECARD',
        'origemSolicitacao'=>$origemSolicitacao,
        'flagEntidade'=>$flagEnt,
        'categoria'=>$categoria,
        'subcategoria'=>$subCat,
        'cnpj_cpf'=>$cnpjCpf,
        'contrato_cartao'=>$contrato_cartao,
        'telefone'=>$origem,
        'telefoneInformado'=>$telInfo,
        'ligacaoId'=>$ligacaoId

    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    
    $obj = json_decode($result);
    
    //var_dump($obj);

    if($obj->{'message'}=="Já Existe Solicitação Aberta com o CNPJ informado! ")return $obj->{'protocolo'};
    else return $obj ->{'protocolo'};
}

function protocolo_mensagem($processId, $categoria, $cnpjCpf, $contrato_cartao, $flagEnt, $origemSolicitacao, $subCat, $origem, $telInfo, $ligacaoId){
    global $configs;
    $token=api_login_token();
        
    $url = $configs['server_api'].$configs['url_ucc_protocolo_v2'];
    //echo $url."\n";
    $ch = curl_init($url);
    
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'X-Auth-Token: '.$token;
    
    $data = array(
        'processId'=>$processId,
        'empresa'=>'VALECARD',
        'origemSolicitacao'=>$origemSolicitacao,
        'flagEntidade'=>$flagEnt,
        'categoria'=>$categoria,
        'subcategoria'=>$subCat,
        'cnpj_cpf'=>$cnpjCpf,
        'contrato_cartao'=>$contrato_cartao,
        'telefone'=>$origem,
        'telefoneInformado'=>$telInfo,
        'ligacaoId'=>$ligacaoId

    );
    
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    //echo $result."\n";
    curl_close($ch);    
    $obj = json_decode($result);
    
    //var_dump($obj);

    return $obj;
}


function get_uniqueId_kontac($origem, $servidor){
    $url = 'http://valecard-'.$servidor.'.kontac.com.br/apitelek/apiretornouniqueid.php?telefone='.$origem;
    //echo $url."\n";

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST'
      
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);
    $obj = json_decode($response);

    //var_dump($obj);
    if($obj ->{'status'}=='success') return $obj ->{'retorno'};
    else return $obj ->{'message'};
}

?>
