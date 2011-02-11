if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    rcmail.register_command('plugin.password-save-without-validation', function() {
	    rcmail.gui_objects.passform.submit();
    }, true);
  })
}

$(document).ready(function($){
	$('input.button.mainaction').remove();
	$('.boxcontent').append('<p><input type="button" value="Salvar" id="save_button" class="button mainaction"></p>')

	$('#save_button').click(function(){
		rcmail.command('plugin.password-save-without-validation','',this);
	});
});
