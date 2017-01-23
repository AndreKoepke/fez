<?php

include_once "incoming.php";
include_once "sql.php";
require_once 'config.php';


class UserContext
{
	private $userState = null;
	private $sqlCon = null;


	public function __construct($id) {
		//Init SQL-Connection-Class
		$this->sqlCon = new SQLConnection();

		//Set UserState
		$stateClass = $this->sqlCon->getUserState($id)."State";
		$this->userState = new $stateClass($id);
    }


    public function processMessage($message)
    {
    	if (isset($message['text']))
    		$this->userState->processMessage($this, $message['chat']['id'], $message, $this->sqlCon);
    	else if (isset($message['location']))
    		$this->sqlCon->setLocation($message['chat']['id'], array('lat' => $message['location']['latitude'], 'lng' => $message['location']['longitude']));
    	else
    		apiRequest("sendMessage", array('chat_id' => $message['chat']['id'], "text" => 'Es können nur Texte-Nachrichten ausgewertet werden.'));
    }


    public function setUserState($id, $stateName)
    {
    	//Set UserState
		$stateClass = $this->sqlCon->getUserState($id)."State";
		$this->userState = new $stateClass($id);

		//Update SQL-Record
    	$this->sqlCon->setUserState($id, $stateName);
    }




	public function alertAll($name)
	{
	  //$states = array('idle', 'alerted', 'come', 'come_direct', 'na');
	  $this->sqlCon->setState('alert');
	  $allIDs = $this->sqlCon->getAllWithState('idle');
	  shell_exec("vcgencmd display_power 1");

	  $text = "ALARM von ".$name." ausgelöst!";

	  foreach($allIDs as $id) {
	        $this->sqlCon->setUserState($id['id'], "alerted");

	        apiRequestJson("sendMessage", array('chat_id' => $id['id'], "text" => $text, 'reply_markup' => array(
		        'keyboard' => array(array('Ja, zur Wache', 'Ja, zur EST', 'Nein')),
		        'one_time_keyboard' => true,
		        'resize_keyboard' => true)));
	  }
	}



	public function abspannen($name)
	{
	  $this->sqlCon->setState('idle');


	  $states = array('idle', 'alerted', 'come', 'come_direct', 'na');
	  $allIDs = $this->sqlCon->getAllWithStates($states);
	  
	  shell_exec("vcgencmd display_power 0");

	  $text = "Abspann von ".$name." gegeben!";


	  foreach($allIDs as $id) {
	        $this->sqlCon->setUserState($id['id'], "idle");
	        apiRequestJson("sendMessage", array('chat_id' => $id['id'], "text" => $text, 'reply_markup' => array(
	        'keyboard' => array(array('Alarm auslösen!')),
	        'one_time_keyboard' => true,
	        'resize_keyboard' => true)));
	  }
	}
	
	/*
	 * Schickt eine Nachricht an alle Benutzer, die den Registrierungsvorgang abgeschlossen haben
	 */
	public function informAll($text)
	{
		$states = array('idle', 'alerted', 'come', 'come_direct', 'na');
		$allIDs = $this->sqlCon->getAllWithStates($states);


		foreach($allIDs as $id) {
		    apiRequestJson("sendMessage", array('chat_id' => $id['id'], "text" => $text));
	  }
	}

}



interface UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon);
}




class newState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		if($sqlCon->insertNewUser($id))
          apiRequest("sendMessage", array('chat_id' => $id, "text" => 'Hallo und Willkommen zur kleinen FEZ der Freiwilligen Feuerwehr Groß Flottbek. Zum Fortfahren brauchen Sie den Einschreibeschlüssel.'));
        else
          apiRequest("sendMessage", array('chat_id' => $id, "text" => 'Fehler beim Anlegen Ihres Profiles.'));

        $context->setUserState($id, 'waitforkey');
	}
}


class waitforkeyState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		if($message['text'] === KEY)
        {
          apiRequest("sendMessage", array('chat_id' => $id, "text" => 'Korrekt. Wie ist dein Name?'));
          $context->setUserState($id, 'waitforname');
        }
        else
        {
          apiRequest("sendMessage", array('chat_id' => $id, "text" => 'Falsch. Kennen Sie den Schlüssel überhaupt?'));
        }
	}
}


class waitfornameState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		$name = $message['text'];
        $sqlCon->setUsername($id, $name);
        apiRequest("sendMessage", array('chat_id' => $id, "text" => 'Hallo '.$name.'. Welche Funktionen hast du (z.B. "Fahrer und PA")?'));
        $context->setUserState($id, 'waitforfunktion');
	}
}



class waitforfunktionState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		$funktion = $message['text'];
        $sqlCon->setFunktion($id, $funktion);

        apiRequestJson("sendMessage", array('chat_id' => $id, "text" => 'Ok, du bist nun in der Alarmschleife.', 'reply_markup' => array(
              'keyboard' => array(array('Alarm auslösen!')),
              'one_time_keyboard' => true,
              'resize_keyboard' => true)));
        $context->setUserState($id, 'idle');
	}
}


class idleState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		if ($message['text'] === "Alarm auslösen!")
			$context->alertAll($sqlCon->getUsername($id));
		else
			apiRequestJson("sendMessage", array('chat_id' => $id, "text" => 'Es kann nur ein Alarm ausgelöst werden.', 'reply_markup' => array(
              'keyboard' => array(array('Alarm auslösen!')),
              'one_time_keyboard' => true,
              'resize_keyboard' => true)));

        
	}


}



class alertedState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		$text = $message['text'];

		switch ($text) {
          case 'Ja, zur Wache':
            apiRequestJson("sendMessage", array('chat_id' => $id, "text" => 'Verstanden, du kommst zu Wache.', 'reply_markup' => array(
              'keyboard' => array(array('Abspann geben!')),
              'one_time_keyboard' => true,
              'resize_keyboard' => true)));
            $context->informAll($sqlCon->getUsername($id)." kommt.");
            $context->setUserState($id, 'come');
            break;

          case 'Ja, zur EST':
            apiRequestJson("sendMessage", array('chat_id' => $id, "text" => 'Verstanden, du kommst zur Einsatzstelle.', 'reply_markup' => array(
              'keyboard' => array(array('Abspann geben!')),
              'one_time_keyboard' => true,
              'resize_keyboard' => true)));
            $context->informAll($sqlCon->getUsername($id)." kommt direkt.");
            $context->setUserState($id, 'come_direct');
            break;
          
          case 'Nein':
            apiRequestJson("sendMessage", array('chat_id' => $id, "text" => 'Schade.', 'reply_markup' => array(
              'keyboard' => array(array('Abspann geben!')),
              'one_time_keyboard' => true,
              'resize_keyboard' => true,
              'remove_keyboard' => true)));
            $context->informAll($sqlCon->getUsername($id)." kommt NICHT.");
            $context->setUserState($id, 'na');
            break;

          default:
            apiRequestJson("sendMessage", array('chat_id' => $id, "text" => "Das konnte ich nicht aufnehmen. Bitte verwende einen der unteren Buttons.", 'reply_markup' => array(
	        'keyboard' => array(array('Ja, zur Wache', 'Ja, zur EST', 'Nein')),
	        'one_time_keyboard' => true,
	        'resize_keyboard' => true)));
        }
	}
}


class comeState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
        if ($message['text'] == 'Abspann geben!')
            $context->abspannen($sqlCon->getUsername($id));   
	}
}


class come_directState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
        if ($message['text'] == 'Abspann geben!')
            $context->abspannen($sqlCon->getUsername($id));   
	}
}


class naState implements UserStateInterface
{
	public function processMessage($context, $id, $message, $sqlCon)
	{
		
		error_log(var_dump($message));
        if ($message['text']== 'Abspann geben!')
            $context->abspannen($sqlCon->getUsername($id));   
	}
}

