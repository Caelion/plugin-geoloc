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

class geoloc extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function event() {
		$cmd = geolocCmd::byId(init('id'));
		if (!is_object($cmd)) {
			throw new Exception(__('Commande ID geoloc inconnu : ', __FILE__) . init('id'));
		}
		if ($cmd->getEqLogic()->getEqType_name() != 'geoloc') {
			throw new Exception(__('Cette commande n\'est pas de type geoloc : ', __FILE__) . init('id'));
		}
		if ($cmd->getConfiguration('mode') != 'dynamic') {
			throw new Exception(__('Cette commande de géoloc n\'est pas dynamique : ', __FILE__) . init('id'));
		}
		$cmd->event(init('value'));
		foreach ($cmd->getEqLogic()->getCmd('info') as $distance) {
			if ($distance->getConfiguration('mode') == 'distance' && ($distance->getConfiguration('from') == $cmd->getId() || $distance->getConfiguration('to') == $cmd->getId())) {
				$distance->event($distance->execute());
			}
		}
	}

	/*     * *********************Methode d'instance************************* */
}

class geolocCmd extends cmd {
	/*     * *************************Attributs****************************** */

/*     * ***********************Methode static*************************** */
	function get_driving_information($start, $finish, $raw = false) {
		if (strcmp($start, $finish) == 0) {
			$time = 0;
			if ($raw) {
				$time .= ' seconds';
			}
			return array('distance' => 0, 'time' => $time);
		}
		$start = urlencode($start);
		$finish = urlencode($finish);
		$distance = __('Inconnue', __FILE__);
		$time = __('Inconnue', __FILE__);
		$url = 'http://maps.googleapis.com/maps/api/directions/xml?origin=' . $start . '&destination=' . $finish . '&sensor=false';
		if ($data = file_get_contents($url)) {
			$xml = new SimpleXMLElement($data);
			if (isset($xml->route->leg->duration->value) AND (int) $xml->route->leg->duration->value > 0) {
				if ($raw) {
					$distance = (string) $xml->route->leg->distance->text;
					$time = (string) $xml->route->leg->duration->text;
				} else {
					$distance = (int) $xml->route->leg->distance->value / 1000;
					$distance = round($distance, 1);
					$time = (int) $xml->route->leg->duration->value;
					$time = floor(($time / 60) % 60);
				}
			} else {
				throw new Exception(__('Impossible de trouver une route', __FILE__));
			}
			return array('distance' => $distance, 'time' => $time);
		} else {
			throw new Exception(__('Impossible de resoudre l\'url', __FILE__));
		}
	}
	/*     * *********************Methode d'instance************************* */

	public function preSave() {
		switch ($this->getConfiguration('mode')) {
			case 'fixe':
				$this->setSubType('string');
				$this->setEventOnly(0);
				break;
			case 'dynamic':
				$this->setSubType('string');
				$this->setEventOnly(1);
				break;
			case 'distance':
				$this->setSubType('numeric');
				$this->setUnite('Km');
				$this->setEventOnly(0);
				break;
			case 'travelDistance':
				$this->setSubType('numeric');
				$this->setUnite('Km');
				$this->setEventOnly(0);
				break;
			case 'travelTime':
				$this->setSubType('numeric');
				$this->setUnite('min');
				$this->setEventOnly(0);
				break;
		}
	}

	function distance($lat1, $lng1, $lat2, $lng2) {
		$earth_radius = 6378.137; // Terre = sphère de 6378km de rayon
		$rlo1 = deg2rad($lng1);
		$rla1 = deg2rad($lat1);
		$rlo2 = deg2rad($lng2);
		$rla2 = deg2rad($lat2);
		$dlo = ($rlo2 - $rlo1) / 2;
		$dla = ($rla2 - $rla1) / 2;
		$a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
		$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
		return round(($earth_radius * $d), 2);
	}

	public function execute($_options = array()) {
		switch ($this->getConfiguration('mode')) {
			case 'fixe':
				$result = $this->getConfiguration('coordinate');
				foreach ($this->getEqLogic()->getCmd('info') as $cmd) {
					if ($cmd->getConfiguration('mode') == 'distance') {

					}
				}
				return $result;
			case 'distance':
				$from = cmd::byId($this->getConfiguration('from'));
				$to = cmd::byId($this->getConfiguration('to'));
				if (!is_object($from)) {
					throw new Exception(__('Commande point de départ introuvable : ', __FILE__) . $this->getConfiguration('from'));
				}
				if (!is_object($to)) {
					throw new Exception(__('Commande point d\'arrivé introuvable : ', __FILE__) . $this->getConfiguration('to'));
				}
				$to = explode(',', $to->execCmd(null, 0));
				$from = explode(',', $from->execCmd(null, 0));
				if (count($to) == 2 && count($from) == 2) {
					return self::distance($from[0], $from[1], $to[0], $to[1]);
				}
				throw new Exception(__('Erreur dans les coordonées from : ', __FILE__) . print_r($from, true) . __(' / to : ', __FILE__) . print_r($to, true));
			case 'travelTime':
				$from = cmd::byId($this->getConfiguration('from'));
				$to = cmd::byId($this->getConfiguration('to'));
				$result = self::get_driving_information($from->execCmd(null, 0), $to->execCmd(null, 0));
				return $result['time'];
			case 'travelDistance':
				$from = cmd::byId($this->getConfiguration('from'));
				$to = cmd::byId($this->getConfiguration('to'));
				$result = self::get_driving_information($from->execCmd(null, 0), $to->execCmd(null, 0));
				return $result['distance'];
		}
	}

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */
}

?>