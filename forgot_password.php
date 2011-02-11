<?
/**
 * Forgot Password
 *
 * Plugin to reset an account password
 *
 * @version 1.0
 * @author Fabio Perrella and Thiago Coutinho (Locaweb)
 * @url https://github.com/saas-dev/roundcube-forgot_password
 */
class forgot_password extends rcube_plugin
{
  public $task = 'login|logout|settings|mail';

  function init() {
  	define('TOKEN_EXPIRATION_TIME_MIN',20);
    $rcmail = rcmail::get_instance();
    $this->add_texts('localization/');

    if($rcmail->task == 'mail') {
    	$this->include_stylesheet('css/forgot_password.css');
    	$this->add_hook('messages_list', array($this, 'show_warning_alternative_email'));
    	$this->add_hook('render_page', array($this, 'add_labels_to_mail_page'));
    }
    else {
	    if($rcmail->task == 'settings') {
	    	if($rcmail->action == 'plugin.password' || $rcmail->action == 'plugin.password-save-forgot_password') {
	    		$this->add_hook('render_page', array($this, 'add_field_alternative_email_to_form'));
	    	}
	    	$this->register_action('plugin.password-save-forgot_password', array($this, 'password_save'));
	    }
	    else{
	    	$this->include_script('js/forgot_password.js');
	    }


			$this->add_hook('render_page', array($this, 'add_labels_to_login_page'));
	  	$this->load_config('config.inc.php');

	    $this->add_hook('render_page', array($this, 'add_labels_to_login_page'));
			$this->add_hook('startup', array($this, 'forgot_password_reset'));
			$this->register_action('plugin.forgot_password_reset', array($this, 'forgot_password_redirect'));

			$this->add_hook('startup', array($this, 'new_password_form'));
			$this->register_action('plugin.new_password_form', array($this, 'new_password_form'));

			$this->add_hook('startup', array($this, 'new_password_do'));
			$this->register_action('plugin.new_password_do', array($this, 'new_password_do'));
    }
  }

  function add_field_alternative_email_to_form() {
  	$rcmail = rcmail::get_instance();
  	$sql_result = $rcmail->db->query('SELECT alternative_email FROM forgot_password ' .
      																' WHERE user_id = ? ', $rcmail->user->ID);
		$userrec = $rcmail->db->fetch_assoc($sql_result);

		//add input alternative_email. I didn't use include_script because I need to cancatenate $userrec['alternative_email']
		$js = '$(document).ready(function($){' .
		'$("#password-form table").prepend(\'<tr><td class="title"><label for="alternative_email">Email Secundário:</label></td>' .
		'<td><input type="text" autocomplete="off" size="20" id="alternative_email" name="_alternative_email" value="' . $userrec['alternative_email'] . '"></td></tr>\');' .
		'form_action = $("#password-form").attr("action");' .
		'form_action = form_action.replace("plugin.password-save","plugin.password-save-forgot_password");' .
		'$("#password-form").attr("action",form_action);' .
		'});';

		$rcmail->output->add_script($js);
		//disable password plugin's javascript validation
		$this->include_script('js/change_save_button.js');
  }

  function password_save() {
  	$rcmail = rcmail::get_instance();
  	$alternative_email = get_input_value('_alternative_email',RCUBE_INPUT_POST);

		if(preg_match('/.+@[^.]+\..+/Umi',$alternative_email)) {
			$rcmail->db->query("REPLACE INTO forgot_password(alternative_email, user_id) values(?,?)",$alternative_email,$rcmail->user->ID);
			$message = $this->gettext('alternative_email_updated','forgot_password');
			$rcmail->output->command('display_message', $message, 'confirmation');
		}
		else {
			$message = $this->gettext('alternative_email_invalid','forgot_password');
			$rcmail->output->command('display_message', $message, 'error');
		}

		$password_plugin = new password($this->api);
		if($_REQUEST['_curpasswd'] || $_REQUEST['_newpasswd'] || $_REQUEST['_confpasswd']) {
	  	$password_plugin->password_save();
		}
		else {
			//render password form
			$password_plugin->add_texts('localization/');
	    $this->register_handler('plugin.body', array($password_plugin, 'password_form'));
			rcmail_overwrite_action('plugin.password');
			$rcmail->output->send('plugin');
		}
  }

  function show_warning_alternative_email(){
  	$rcmail = rcmail::get_instance();
  	$sql_result = $rcmail->db->query('SELECT alternative_email FROM forgot_password where user_id=?',$rcmail->user->ID);
  	$userrec = $rcmail->db->fetch_assoc($sql_result);

  	if(!$userrec['alternative_email'] && !isset($_SESSION['show_warning_alternative_email'])){

	  	$link = "<a href='/?_task=settings&_action=plugin.password'>". $this->gettext('click_here','forgot_password') ."</a>";
	  	$message = sprintf($this->gettext('notice_no_alternative_email_warning','forgot_password'),$link);
	  	$rcmail->output->command('display_message', $message, 'notice');
	  	$_SESSION['show_warning_alternative_email'] = false;
  	}
  }

  function new_password_do($a){
  	if($a['action'] != 'plugin.new_password_do' || !isset($_SESSION['temp']))
      return $a;

    $rcmail = rcmail::get_instance();

    $new_password = get_input_value('_new_password',RCUBE_INPUT_POST);
    $new_password_confirmation = get_input_value('_new_password_confirmation',RCUBE_INPUT_POST);
    $token = get_input_value('_t',RCUBE_INPUT_POST);
    if($new_password && $new_password == $new_password_confirmation){
			$rcmail->db->query("UPDATE ".get_table_name('users').
					" SET password=? " .
					" WHERE user_id=(SELECT user_id FROM forgot_password WHERE token=?)",
					array($rcmail->encrypt($new_password), $token));

			if($rcmail->db->affected_rows()==1){
				$rcmail->db->query("UPDATE forgot_password set token=null, token_expiration=null WHERE token=?",$token);

				$message = $this->gettext('password_changed','forgot_password');
				$type = 'confirmation';
			}
			else{
				$message = $this->gettext('password_not_changed','forgot_password');
				$type = 'error';
			}

    	$rcmail->output->command('display_message', $message, $type);
	    $rcmail->output->send('login');
    }
    else{
    	$message = $this->gettext('password_confirmation_invalid','forgot_password');
    	$rcmail->output->command('display_message', $message, 'error');
	    $rcmail->output->send('forgot_password.new_password_form');
    }
  }

  function new_password_form($a){
  	if($a['action'] != 'plugin.new_password_form' || !isset($_SESSION['temp']))
      return $a;

  	$rcmail = rcmail::get_instance();
  	$sql_result = $rcmail->db->query("SELECT * FROM ".get_table_name('users')." u " .
      																" INNER JOIN forgot_password fp on u.user_id = fp.user_id " .
      																" WHERE fp.token=? and token_expiration >= now()", get_input_value('_t',RCUBE_INPUT_GET));
		$userrec = $rcmail->db->fetch_assoc($sql_result);
		if($userrec){
			$rcmail->output->send("forgot_password.new_password_form");
		}
		else{
			$message = $this->gettext('invalidtoken','forgot_password');
	    $type = 'error';

	    $rcmail->output->command('display_message', $message, 'error');
	    $rcmail->kill_session();
	    $rcmail->output->send('login');
		}
  }

  function forgot_password_reset($a) {
    if($a['action'] != "plugin.forgot_password_reset" || !isset($_SESSION['temp']))
      return $a;

    // kill remember_me cookies
    setcookie ('rememberme_user','',time()-3600);
    setcookie ('rememberme_pass','',time()-3600);

    $rcmail = rcmail::get_instance();

		//user must be user@domain
    $user = trim(urldecode($_GET['_username']));

    if($user) {
      // get user row
      $sql_result = $rcmail->db->query("SELECT u.user_id, fp.alternative_email, fp.token_expiration, fp.token_expiration < now() as token_expired " .
      																"	FROM ".get_table_name('users')." u " .
      																" INNER JOIN forgot_password fp on u.user_id = fp.user_id " .
      																" WHERE  u.username=?", $user);
      $userrec = $rcmail->db->fetch_assoc($sql_result);

      if(is_array($userrec) && $userrec['alternative_email']) {

      	if($userrec['token_expiration'] && !$userrec['token_expired']) {
      		$message = $this->gettext('checkaccount','forgot_password');
	        $type = 'confirmation';
      	}
      	else {
					if($this->send_email_with_token($userrec['user_id'], $userrec['alternative_email'])) {
		        $message = $this->gettext('checkaccount','forgot_password');
		        $type = 'confirmation';
		      }
		      else {
		        $message = $this->gettext('sendingfailed','forgot_password');
		        $type = 'error';
		      }
      	}
      }
      else{
      	$this->send_alert_to_admin($user);
      	$message = $this->gettext('senttoadmin','forgot_password');
	      $type = 'notice';
      }
    }
    else {
      $message = $this->gettext('userempty','forgot_password');
      $type = 'error';
    }

    $rcmail->output->command('display_message', $message, $type);
    $rcmail->kill_session();
    $_POST['_user'] = $user;
    $rcmail->output->send('login');
  }

  function add_labels_to_login_page($a) {

    if($a['template'] != "login")
      return $a;

    $rcmail = rcmail::get_instance();
    $rcmail->output->add_label(
    	'forgot_password.forgotpassword',
      'forgot_password.forgot_passworduserempty',
      'forgot_password.forgot_passwordusernotfound'
    );

    return $a;
  }

  function add_labels_to_mail_page($a) {

    $rcmail = rcmail::get_instance();
    $rcmail->output->add_label('forgot_password.no_alternative_email_warning');
    $rcmail->output->add_script('rcmail.message_time = 10000;');

    return $a;
  }

  function html($p) {

     $rcmail = rcmail::get_instance();
     $content = "<h1>asfasdfasdf" . taskbar . "</h1>";

     $rcmail->output->add_footer($content);

     return $p;
   }

	private function send_email_with_token($user_id, $alternative_email) {
		$rcmail = rcmail::get_instance();
		$token = md5($alternative_email.microtime());
		$sql = "UPDATE forgot_password " .
				" SET token='$token', token_expiration=now() + INTERVAL " . TOKEN_EXPIRATION_TIME_MIN . " MINUTE" .
				" WHERE user_id=$user_id";
		$rcmail->db->query($sql);

		$file = dirname(__FILE__)."/localization/{$rcmail->config->get('language')}/reset_pw_body.html";
		$link = "http://{$_SERVER['SERVER_NAME']}/?_task=settings&_action=plugin.new_password_form&_t=$token";
		$body = strtr(file_get_contents($file), array('[LINK]' => $link));
		$subject = $rcmail->gettext('email_subject','forgot_password');

		return $this->send_html_and_text_email(
																		$alternative_email,
																		$this->get_from_email($alternative_email),
																		$subject,
																		$body
																		);
	}

	private function send_alert_to_admin($user_requesting_new_password) {
		$rcmail = rcmail::get_instance();

		$file = dirname(__FILE__)."/localization/{$rcmail->config->get('language')}/alert_for_admin_to_reset_pw.html";
		$body = strtr(file_get_contents($file), array('[USER]' => $user_requesting_new_password));
		$subject = $rcmail->gettext('admin_alert_email_subject','forgot_password');

		return $this->send_html_and_text_email(
																		$rcmail->config->get('admin_email'),
																		$this->get_from_email($user_requesting_new_password),
																		$subject,
																		$body
																		);
	}

	private function get_from_email($email) {
		$parts = explode('@',$email);
		return 'no-reply@'.$parts[1];
	}

	private function send_html_and_text_email($to, $from, $subject, $body) {
		$rcmail = rcmail::get_instance();

		$ctb = md5(rand() . microtime());
		$headers  = "Return-Path: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: multipart/alternative; boundary=\"=_$ctb\"\r\n";
		$headers .= "Date: " . date('r', time()) . "\r\n";
		$headers .= "From: $from\r\n";
		$headers .= "To: $to\r\n";
		$headers .= "Subject: $subject\r\n";
		$headers .= "Reply-To: $from\r\n";

		$msg_body .= "Content-Type: multipart/alternative; boundary=\"=_$ctb\"\r\n\r\n";

		$txt_body  = "--=_$ctb";
		$txt_body .= "\r\n";
		$txt_body .= "Content-Transfer-Encoding: 7bit\r\n";
		$txt_body .= "Content-Type: text/plain; charset=" . RCMAIL_CHARSET . "\r\n";
		$LINE_LENGTH = $rcmail->config->get('line_length', 75);
		$h2t = new html2text($body, false, true, 0);
		$txt = rc_wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n");
		$txt = wordwrap($txt, 998, "\r\n", true);
		$txt_body .= "$txt\r\n";
		$txt_body .= "--=_$ctb";
		$txt_body .= "\r\n";

		$msg_body .= $txt_body;

		$msg_body .= "Content-Transfer-Encoding: quoted-printable\r\n";
		$msg_body .= "Content-Type: text/html; charset=" . RCMAIL_CHARSET . "\r\n\r\n";
		$msg_body .= str_replace("=","=3D",$body);
		$msg_body .= "\r\n\r\n";
		$msg_body .= "--=_$ctb--";
		$msg_body .= "\r\n\r\n";

		// send message
		if (!is_object($rcmail->smtp)) {
			$rcmail->smtp_init(true);
		}

		if($rcmail->config->get('smtp_pass') == "%p") {
			$rcmail->config->set('smtp_server', $rcmail->config->get('default_smtp_server'));
			$rcmail->config->set('smtp_user', $rcmail->config->get('default_smtp_user'));
			$rcmail->config->set('smtp_pass', $rcmail->config->get('default_smtp_pass'));
		}

		$rcmail->smtp->connect();
		if($rcmail->smtp->send_mail($from, $to, $headers, $msg_body)) {
			return true;
		}
		else {
			write_log('errors','response:' . print_r($rcmail->smtp->get_response(),true));
			write_log('errors','errors:' . print_r($rcmail->smtp->get_error(),true));
			return false;
		}
	}
}
?>
