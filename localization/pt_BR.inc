<?php

$labels = array();
$labels['forgotpassword'] = 'Esqueci minha senha';
$labels['passwordrecovery'] = 'Forgot Password';
$labels['email_subject'] = 'Instruções para gerar nova senha';
$labels['admin_alert_email_subject'] = 'Solicitação de troca de senha';
$labels['pwreminder'] = 'Password Reminder';
$labels['forgot_passwordenabled'] = 'Reminder enabled';
$labels['forgot_passwordisenabled'] = 'Reminder is enabled';
$labels['forgot_passworddisabled'] = 'Reminder is disabled';
$labels['forgot_passwordaddress'] = 'Send Password to';
$labels['forgot_passwordquestion'] = 'Secret Question';
$labels['forgot_passwordanswer'] = 'Secret Answer';
$labels['forgot_passwordanswerconfirm'] = 'Confirm Secret Answer';
$labels['forgot_passwordaddidentity'] = 'Please add an identity';
$labels['forgot_passwordadd'] = 'add';
$labels['forgot_passwordresetpw'] = 'Enviar';
$labels['forgot_passwordexit'] = 'Cancelar';
$labels['new_password'] = 'Nova senha';
$labels['new_password_confirmation'] = 'Confirmação';
$labels['no_alternative_email_warning'] = 'Cadastre um email alternativo.';
$labels['click_here'] = 'clique aqui';

$messages = array();
$messages['forgot_passwordquestionempty'] = 'Please enter your Secret Question';
$messages['forgot_passworduserempty'] = 'Por favor informe seu E-mail';
$messages['forgot_passwordanswerempty'] = 'Please enter your Secret Answer';
$messages['forgot_passwordanswernotmatch'] = 'Secret Answer does not match';
$messages['forgot_passwordactive'] = 'Password Reminder will be enabled';
$messages['forgot_passwordinactive'] = 'Password Reminder is disabled';
$messages['forgot_passwordusernotfound'] ='Usuário não encontrado';
$messages['forgot_passwordimcomplete'] ='Your Reminder Settings are incomplete. Please contact the Administrator';
$messages['forgot_passwordfailed'] = 'Secret Answer is incorrect.';
$messages['sendingfailed'] = 'Ocorreu um erro ao enviar o email com instruções para gerar uma nova senha. Por favor tente novamente mais tarde';
$messages['checkaccount'] = 'Foi enviado um email com instruções para gerar uma nova senha para seu email alternativo.';
$messages['senttoadmin'] = 'Sua conta não possui um email alternativo cadastrado, por isso foi enviado um email para o Administrador trocar sua senha.';
$messages['autobanned'] = 'Access is temporarilly locked (too much invalid login attempts). Please try later.';
$messages['nodeleteforgot_passwordaddress'] = 'This identity is linked to your Password reminder.';
$messages['invalidtoken'] = 'Token inválido.';
$messages['password_confirmation_invalid'] = 'Confirmação de senha inválida';
$messages['password_changed'] = 'Senha alterada com sucesso.';
$messages['password_not_changed'] = 'Ocorreu um erro ao alterar a senha. Por favor tente novamente mais tarde.';
$messages['notice_no_alternative_email_warning'] = 'Você não possui um email alternativo cadastrado, %s para cadastrar.';
$messages['alternative_email_updated'] = 'Seu email alternativo foi alterado.';
$messages['alternative_email_invalid'] = 'Informe um email alternativo válido.';

?>
