<?php
/**
 * Mass Mailer main controller
 *
 * @package blesta
 * @subpackage blesta.plugins.mass_mailer
 * @copyright Copyright (c) 2010, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
class AdminMain extends AppController {

	/**
	 * Pre action
	 */
	public function preAction() {
		parent::preAction();

		// Override default view directory
		$this->view->view = "default";
		// Restore structure view
		$this->structure->setDefaultView(APPDIR);

		$this->requireLogin();

		Language::loadLang("admin_main", null, PLUGINDIR . "mass_mailer" . DS . "language" . DS);
	}

	/**
	 * Index
	 */
	public function index() {
		//Load Language
		Language::loadLang("mass_mailer_plugin", null, PLUGINDIR . "mass_mailer" . DS . "language" . DS);
		
		//Load Components
		if (!isset($this->Record))
		Loader::loadComponents($this, array("Record"));

		//Get Languages
		$client_settings = $this->Record->select()->from("client_settings")->where('key', '=', 'language')->fetchAll();
		foreach($client_settings as $setting){
			$lang_filter[$setting->value] = $setting->value;
		}
		$lang_filter = array_unique($lang_filter);
		if(!empty($_POST['from_name']) && count(explode('@', $_POST['from'])) > 0 && !empty($_POST['subject']) && !empty($_POST['mail']) && !empty($_POST['filter'])){
			//Load Components
			if (!isset($this->Email))
			Loader::loadComponents($this, array("Email"));
			if (!isset($this->Input))
			Loader::loadComponents($this, array("Input"));

			$client_settings = $this->Record->select()->from("client_settings")->where('value', '=', $_POST['filter'])->fetchAll();
			foreach($client_settings as $setting){
				$contacts = $this->Record->select()->from("contacts")->where('client_id', '=', $setting->client_id)->fetchAll();
				foreach($contacts as $contact){
					$replacements = array('{contact.first_name}' => $contact->first_name, '{contact.last_name}' => $contact->last_name, '{contact.title}' => $contact->title, '{contact.company}' => $contact->company, '{contact.email}' => $contact->email, '{contact.address1}' => $contact->address1, '{contact.address2}' => $contact->address2, '{contact.city}' => $contact->city, '{contact.state}' => $contact->state, '{contact.zip}' => $contact->zip);
					$headers = 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n".'From: '.$_POST['from_name'].' <'. $_POST['from'].'>'."\r\n".'X-Mailer: PHP/'.phpversion();
					$message = $_POST['mail'];
					foreach($replacements as $key => $value){
						$message = str_replace($key, $value, $message);
					}
					if(mail($contact->email, $_POST['subject'], $message, $headers)){
						$log[] = $contact->first_name.' '.$contact->last_name.'<'.$contact->email.'> - Send <br>';
					} else {
						$log[] = $contact->first_name.' '.$contact->last_name.' <'.$contact->email.'> - Failure <br>';
					}
				}
			}
			$this->set("log", $log);
		}
		$this->set("lang_filter", $lang_filter);
	}
}
?>
