<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class playtts extends eqLogic {

	public static function dependancy_info() {
    $return = array();
    $return['log'] = 'playtts_dep';
    $cmd = "dpkg -l | grep mplayer";
    exec($cmd, $output, $return_var);
    if ($output[0] != "") {
			if (`which pico2wave`) {
			    $return['state'] = 'ok';
			} else {
	      $return['state'] = 'nok';
	    }
    } else {
      $return['state'] = 'nok';
    }
    return $return;
  }
  public static function dependancy_install() {
    passthru('/bin/bash ' . realpath(dirname(__FILE__)) . '/../../resources/install.sh > ' . realpath(dirname(__FILE__)) . '/../../resources/ ' . log::getPathToLog('playtts_dep') . ' 2>&1 &');
  }

	public function preUpdate() {
		if ($this->getConfiguration('maitreesclave') == '') {
			throw new Exception(__('Merci de remplir le type de lecteur',__FILE__));
		}
	}

	public function postUpdate() {
		$playttsCmd = playttsCmd::byEqLogicIdAndLogicalId($this->getId(),'tts');
		if (!is_object($playttsCmd)) {
			log::add('playtts', 'debug', 'Création de la commande TTS');
			$playttsCmd = new playttsCmd();
			$playttsCmd->setName(__('TTS', __FILE__));
			$playttsCmd->setEqLogic_id($this->id);
			$playttsCmd->setEqType('playtts');
			$playttsCmd->setLogicalId('tts');
			$playttsCmd->setType('action');
			$playttsCmd->setSubType('message');
			$playttsCmd->save();
		}
		$playttsCmd = playttsCmd::byEqLogicIdAndLogicalId($this->getId(),'play');
		if (!is_object($playttsCmd)) {
			log::add('playtts', 'debug', 'Création de la commande Play');
			$playttsCmd = new playttsCmd();
			$playttsCmd->setName(__('Lecture Fichier', __FILE__));
			$playttsCmd->setEqLogic_id($this->id);
			$playttsCmd->setEqType('playtts');
			$playttsCmd->setLogicalId('play');
			$playttsCmd->setType('action');
			$playttsCmd->setSubType('message');
			$playttsCmd->save();
		}

	}

	public function postSave() {

		if ($this->getConfiguration('maitreesclave') == 'deporte'){
			$ip=$this->getConfiguration('addressip');
			$port=$this->getConfiguration('portssh');
			$user=$this->getConfiguration('user');
			$pass=$this->getConfiguration('password');
			if (!$connection = ssh2_connect($ip,$port)) {
				log::add('playtts', 'error', 'connexion SSH KO');
			}else{
				if (!ssh2_auth_password($connection,$user,$pass)){
					log::add('playtts', 'error', 'Authentification SSH KO');
				}else{
					log::add('playtts', 'debug', 'Dépendances en SSH');
					ssh2_scp_send($connection, realpath(dirname(__FILE__)) . '/../../resources/install.sh', 'install_playtts.sh', 0755);
					$result = ssh2_exec('sudo bash install_playtts.sh');
					stream_set_blocking($result, true);
					$result = stream_get_contents($result);

					$closesession = ssh2_exec($connection, 'exit');
					stream_set_blocking($closesession, true);
					stream_get_contents($closesession);
				}
			}
		}
	}

	public function sendCommand( $id, $type, $option ) {
		log::add('playtts', 'debug', 'Lecture : ' . $type . ' ' . $option);
		$playtts = self::byId($id, 'playtts');
		if ($type == 'tts') {
			$hash = hash('md5', $option);
			$file = '/tmp/' . $hash . '.mp3';
		} else {
			$file = $option;
		}
		log::add('playtts', 'debug', 'File : ' .  $file);
		if ($playtts->getConfiguration('maitreesclave') == 'deporte'){
			$ip=$playtts->getConfiguration('addressip');
			$port=$playtts->getConfiguration('portssh');
			$user=$playtts->getConfiguration('user');
			$pass=$playtts->getConfiguration('password');
			if (!$connection = ssh2_connect($ip,$port)) {
				log::add('playtts', 'error', 'connexion SSH KO');
			}else{
				if (!ssh2_auth_password($connection,$user,$pass)){
					log::add('playtts', 'error', 'Authentification SSH KO');
				}else{
					log::add('playtts', 'debug', 'Commande par SSH');
					if ($type == 'tts') {
						$lang = $playtts->getConfiguration('lang');
						if ($lang == '') {
							$lang == 'fr-FR';
						}
						$pico = ssh2_exec("pico2wave -l " . $lang . " -w /tmp/voice.wav \"" . $option . "\"");
						$sox = ssh2_exec("sox /tmp/voice.wav -r 48k " . $file);
					}
					$result = ssh2_exec('mplayer ' . $playtts->getConfiguration('opt') . ' ' . $file);
					stream_set_blocking($result, true);
					$result = stream_get_contents($result);

					$closesession = ssh2_exec($connection, 'exit');
					stream_set_blocking($closesession, true);
					stream_get_contents($closesession);
				}
			}
		}else {
			if (!file_exists($file)) {
				if ($type == 'tts') {
					$lang = $playtts->getConfiguration('lang');
					if ($lang == '') {
						$lang == 'fr-FR';
					}
					exec("pico2wave -l " . $lang . " -w /tmp/voice.wav \"" . $option . "\"");
					exec("sox /tmp/voice.wav -r 48k " . $file);
				} else {
					log::add('playtts', 'error', 'Fichier inexistant');
					return;
				}
			}

			exec('mplayer ' . $playtts->getConfiguration('opt') . ' ' . $file);
		}
	}

}

class playttsCmd extends cmd {

	public function preSave() {
		if ($this->getSubtype() == 'message') {
			$this->setDisplay('title_disable', 1);
		}
	}

	public function execute($_options = null) {
		log::add('playtts', 'info', 'Commande recue : ' . $_options['message']);
		$eqLogic = $this->getEqLogic();
		playtts::sendCommand($eqLogic->getId(), $this->getLogicalId(), $_options['message']);
		return true;
	}
}

?>
