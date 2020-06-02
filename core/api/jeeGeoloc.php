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
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'geoloc')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (geoloc)', __FILE__);
 die();
}

$content = file_get_contents('php://input');
$json = json_decode($content, true);
log::add('geoloc', 'debug', $content);

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
$value = init('value');
if (strpos($value, 'https://') !== false || strpos($value, 'http://') !== false) {
    $url = parse_url($value);
    parse_str($url['query'], $output);
    if (isset($output['q'])) {
        $value = $output['q'];
    }
    if (isset($output['ll'])) {
        $value = $output['ll'];
    }
}
$cmd->event($value);
foreach (cmd::byEqLogicId($cmd->getEqLogic_id()) as $cmd_other) {
    if ($cmd_other->getConfiguration('mode') == 'distance') {
        log::add('geoloc','debug','api distance sur '.$cmd_other->getName());
        $cmd_other->event($cmd_other->execute());
    } else if($cmd_other->getConfiguration('mode') == 'travelTime' ){
        log::add('geoloc','debug','api travelTime sur '.$cmd_other->getName());
        $cmd_other->event($cmd_other->execute());
    } else if($cmd_other->getConfiguration('mode') == 'travelDistance' ){
        log::add('geoloc','debug','api travelDistance  sur '.$cmd_other->getName());
        $cmd_other->event($cmd_other->execute());
    }
}
$cmd->getEqLogic()->refreshWidget();

return true;

?>
