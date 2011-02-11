function forgot_password(){
  	if($('#rcmloginuser').val())
	{
	  document.location.href = "./?_task=settings&_action=plugin.forgot_password_reset&_username=" + escape($('#rcmloginuser').val());
	}
	else
	{
	  rcmail.display_message(rcmail.gettext('forgot_passworduserempty','forgot_password'),'error');
	}
}

$(document).ready(function($){

  $('#taskbar').append('<a class="home" id="forgot_password" href="javascript:forgot_password();">' + rcmail.gettext('forgotpassword','forgot_password') + '</a>');

});