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

	public static $_dico = array(
		'af'=>'af-ZA',//Afrikaans
		'sq'=>'sq-AL',//Albanian
		'ar'=>'ar-YE',//Arabic
		'hy'=>'hy-AM',//Armenian
		'ca'=>'ca-ES',//Catalan
		'zh-CN'=>'zh-CN',//Mandarin (simplified)
		'zh-TW'=>'zh-TW',//Mandarin (traditional)
		'hr'=>'hr-HR',//Croatian
		'cs'=>'cs-CZ',//Czech
		'da'=>'da-DK',//Danish
		'nl'=>'nl-NL',//Dutch
		'en'=>'en-GB',//English
		'en-us'=>'en-US',//English (United States)
		'en-au'=>'en-AU',//English (Australia)
		'eo'=>'eo',//Esperanto
		'fi'=>'fi-FI',//Finnish
		'fr'=>'fr-FR',//French
		'de'=>'de-DE',//German
		'el'=>'el-GR',//Greek
		'ht'=>'ht',//Haitian Creole
		'hi'=>'hi-IN',//Hindi
		'hu'=>'hu-HU',//Hungarian
		'is'=>'is-IS',//Icelandic
		'id'=>'id-ID',//Indonesian
		'it'=>'it-IT',//Italian
		'ja'=>'ja-JP',//Japanese
		'ko'=>'ko-KR',//Korean
		'la'=>'la',//Latin
		'lv'=>'lv-LV',//Latvian
		'mk'=>'mk-MK',//Macedonian
		'no'=>'nb-NO',//Norwegian
		'pl'=>'pl-PL',//Polish
		'pt'=>'pt-PT',//Portuguese
		'ro'=>'ro-RO',//Romanian
		'ru'=>'ru-RU',//Russian
		'sr'=>'sr-SP',//Serbian
		'sk'=>'sk-SK',//Slovak
		'es'=>'es-ES',//Spanish
		'sw'=>'sw-KE',//Swahili
		'sv'=>'sv-SE',//Swedish
		'ta'=>'ta-IN',//Tamil
		'th'=>'th-TH',//Thai
		'tr'=>'tr-TR',//Turkish
		'vi'=>'vi-VN',//Vietnamese
		'cy'=>'cy-GB',//Welsh
	);

	public function postUpdate() {
		$playttsCmd = $this->getCmd(null, 'parle');
		if (!is_object($playttsCmd)) {
			$playttsCmd = new playttsCmd();
		}
		$playttsCmd->setName(__('Parle', __FILE__));
		$playttsCmd->setLogicalId('parle');
		$playttsCmd->setEqLogic_id($this->getId());
		$playttsCmd->setType('action');
		$playttsCmd->setSubType('message');
		$playttsCmd->save();
	}

}

class playttsCmd extends cmd {

	public function execute($_options = null) {

		$playtts = $this->getEqLogic();
		if($playtts->getConfiguration('lang')==""){
			$playtts_lang = "fr";
		}else{
			$playtts_lang = $playtts->getConfiguration('lang');
		}
		if($playtts_moteurTTS=="pico"){
			$playtts_lang = self::$_dico[$playtts_lang];
		}
		$playtts_opt = $playtts->getConfiguration('opt');
		$playtts_moteurTTS = $playtts->getConfiguration('moteurTTS');
		if($playtts_moteurTTS=="url"){
			$playtts_url = $playtts->getConfiguration('url');
		}else if($playtts_moteurTTS=="pico") {
			$playtts_url = "pico";
		} else {
			$playtts_url = "";
		}
		if ($playtts->getConfiguration('maitreesclave') == 'deporte'){
			$playtts_path = $playtts->getConfiguration('chemin');
		}else {
			$playtts_path = realpath(dirname(__FILE__) . '/../../ressources');
		}
		$response = true;

		if (is_numeric($_options['title']) && $_options['title']>=0 && $_options['title']<=100){
			$volume=$_options['title'];
		} else {
			$volume=100;
		}
		if ($playtts_opt==''){
			$playtts_opt='-volume '.$volume;
		} else {
			$playtts_opt=$playtts_opt.' -volume '.$volume;
		}
		log::add('playtts', 'info', 'Debut de l action '.'/usr/bin/python ' . $playtts_path . '/tts.py -l '.$playtts_lang.' -o "'.$playtts_opt.'" -u "'.$playtts_url.'" -t "' . $_options['message'] . '" 2>&1');
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
					$result = ssh2_exec($connection, '/usr/bin/python ' . $playtts_path . '/tts.py -l '.$playtts_lang.' -o "'.$playtts_opt.'" -u "'.$playtts_url.'" -t "' . $_options['message'] . '" 2>&1');
					stream_set_blocking($result, true);
					$result = stream_get_contents($result);

					$closesession = ssh2_exec($connection, 'exit');
					stream_set_blocking($closesession, true);
					stream_get_contents($closesession);
				}
			}
		}else {
			$result = shell_exec('/usr/bin/python ' . $playtts_path . '/tts.py -l '.$playtts_lang.' -o "'.$playtts_opt.'" -u "'.$playtts_url.'" -t "' . $_options['message'] . '" 2>&1');
		}
		return $result;

		/*     * **********************Getteur Setteur*************************** */
	}
}
?>
