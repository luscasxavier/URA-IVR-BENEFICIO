<?php

return array(
    //'user_login' => $user,
    'user_login' => $user,
    //'pass_login' => $password,
    'pass_login' => $password,
    //'server_api' => $server,
    'server_api' => $server,
    'url_login' => 'ura-vale-api/auth/login',
    'siglaUra' => 'beneficio',
    'copiar_audios_para_teste' => 'true',
    'extensao_audio' =>'.wav',
    'audio_lib' => '/var/lib/asterisk/sounds/',
    'audio_ura' => 'uraBeneCli/',
    'max_timeout' => '6000',
    'extensao_audio' =>'.wav',
    'max_tentativas' => '3',
    // URLS BENEFICIO
    'url_bene_valida_cnpjcpf' => 'ura-vale-api/services/apiUraBeneficio/API01ValidarCPFCNPJCodCliente',
    'url_bene_alt_status_cartao'=>'ura-vale-api/services/apiUraBeneficio/API02AlterarStatusCartao',
    'url_bene_valida_cartao'=>'ura-vale-api/services/apiUraBeneficio/API03ValidarCartao',
    'url_bene_valida_prod_cpf'=>'ura-vale-api/services/apiUraBeneficio/API04ValidarProdutosPeloCpf',
    'url_bene_busca_faturas_cli'=>'ura-vale-api/services/apiUraBeneficio/API05BuscarFaturasCliente',
    'url_bene_cancela_usu_cartao'=>'ura-vale-api/services/apiUraBeneficio/API06CancelarUsuarioCartao',
    'url_bene_nova_senha_usu'=>'ura-vale-api/services/apiUraBeneficio/API07GerarNovaSenhaUsuario',
    //'url_bene_valida_resp_pid'=>'',
    'url_bene_pergunta_pid'=>'ura-vale-api/services/apiUraBeneficio/API08PerguntasPID',
    'url_bene_valida_cvv_cartao'=>'ura-vale-api/services/apiUraBeneficio/API09ValidarCVVCartao',
    'url_api_valida_transacao_negada'=>'ura-vale-api/services/apiUraBeneficio/API11ValidarTransacaoNegadas',
    'url_bene_possui_cvv'=>'ura-vale-api/services/apiUraBeneficio/API10CartaoPossuiCVV',
    'url_api_busca_saldo_usu'=>'ura-vale-api/services/apiUraBeneficio/API12BuscaSaldoUsuario',
    'url_ucc_valida_senha'=>'ura-vale-api/services/uraComum/api05ValidarSenha',
    'url_ucc_abertura_ptcl_easy' => 'ura-vale-api/services/uraComum/api01AberturaProtocoloEasy',
    'url_ucc_altera_senha' => 'ura-vale-api/services/uraComum/api06AlterarSenha',
    'url_valida_cli_frota'=>'ura-vale-api/services/uraFrota/API05ValidarCPFCNPJCodClienteFrota',
    'url_api_distribui_valores'=>'ura-vale-api/services/uraFrota/API04DistribuirValores' ,
    'url_ucc_protocolo_v2'=>'ura-vale-api/services/uraComum/api01AberturaProtocoloEasyV2',
    'url_horario' => 'ura-vale-api/services/uraComum/api02ValidarHorarioAtendimento'
);
?>
