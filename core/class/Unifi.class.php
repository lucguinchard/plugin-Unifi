<?php

/*
 * This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
 * Copyright (c) 2018 NextDom.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../3rdparty/UniFi-API-client/Client.php';
require_once 'UnifiCmd.class.php';

class Unifi extends eqLogic {
	/*	 * *************************Attributs****************************** */


	/*	 * ***********************Methode static*************************** */


	public static function cron() {
		log::add(__CLASS__, 'debug', 'Appel du Cron');
		Unifi::searchAndSaveDeviceList();
	}

	public static function searchAndSaveDeviceList() {
		$controller_user = config::byKey('controller.user', __CLASS__);
		$controller_password = config::byKey('controller.password', __CLASS__);
		$controller_url = config::byKey('controller.url', __CLASS__);
		$controller_site = config::byKey('controller.site', __CLASS__);
		
		$unifi_connection = new UniFi_API\Client($controller_user, $controller_password, $controller_url, $controller_site);
		$isLogin = $unifi_connection->login();
		if(!$isLogin) {
			log::add(__CLASS__, 'error', 'Le contrôleur « ' . $controller_url . ' » est injoignable.');
			return;
		}
		if($isLogin != 1) {
			log::add(__CLASS__, 'error', 'La connection vers « ' . $controller_url . ' » est impossible avec « ' . $controller_user . ' ».');
			return;
		}
		$clients_array    = $unifi_connection->list_clients();
		$devices_array = $unifi_connection->list_devices();
		$device_name_array = $unifi_connection->list_device_name_mappings();
		$logoutresults     = $unifi_connection->logout();

		//log::add(__CLASS__, 'debug', 'client ' . print_r($device_name_array,true));
		$ipClientList = array();
		foreach ($clients_array as $client) {
			log::add(__CLASS__, 'debug', '[' . $client->ip . '] annalyse du client « ' . $client->name . ' ».');
			//log::add(__CLASS__, 'debug', 'client ' . print_r($client,true));
			$eqLogic = Unifi::byLogicalId($client->_id, __CLASS__);
			if (!is_object($eqLogic)) {
				$eqLogic = new Unifi();
				$eqLogic->setEqType_name(__CLASS__);
				$eqLogic->setLogicalId($client->_id);
				$eqLogic->setIsVisible(0);
				$eqLogic->setIsEnable(1);
			}
			if (!empty($client->name)) {
				$name = $client->name;
			} else {
				if (!empty($client->oui)) {
					$name = $client->oui;
				} else {
					if (!empty($client->ip)) {
						$name = $client->ip;
					} else {
						if (!empty($client->mac)) {
							$name = $client->mac;
						} else {
							log::add(__CLASS__, 'info', '[' . $client->ip . '] Problème avec  « ' . print_r($client,true) . ' ».');
						}
					}
				}
			}
			$eqLogic->setName($name);
			$eqLogic->setConfiguration('ip', $client->ip);
			$eqLogic->setConfiguration('model', $client->dev_id_override);
			$eqLogic->setConfiguration('type', 'client');
			$eqLogic->save();
			
			
			$fileUrl = "/plugins/" . __CLASS__ . "/core/img/" . $client->dev_id_override . ".png";
			$fileAlbumART = __DIR__ . '/../../../..' . $fileUrl;
			if (!(file_exists($fileAlbumART))) {
				log::add(__CLASS__, 'debug', 'file not exists ' . $fileAlbumART);
				$url = "https://static.ubnt.com/fingerprint/0/" . $client->dev_id_override . "_129x129.png";
				file_put_contents($fileAlbumART, file_get_contents($url));
			}

			$configurationNetwork['type'] = 'network';

			$network_active = $eqLogic->createCmd('active', 'info', 'binary', false, null, $configurationNetwork);
			$network_active->save();
			$network_active->event(1);

			$network_uptime = $eqLogic->createCmd('uptime', 'info', 'numeric', false, null, $configurationNetwork);
			$network_uptime->save();
			$network_uptime->event($client->uptime);

			$network_is_wired = $eqLogic->createCmd('is_wired', 'info', 'numeric', false, null, $configurationNetwork);
			$network_is_wired->save();
			$network_is_wired->event($client->is_wired);

			$network_mac = $eqLogic->createCmd('mac', 'info', 'string', false, null, $configurationNetwork);
			$network_mac->save();
			$network_mac->event($client->mac);

			$network_network = $eqLogic->createCmd('network', 'info', 'string', false, null, $configurationNetwork);
			$network_network->save();
			if(!$client->is_wired) {
				$network_network->event($client->essid);

				$network_satisfaction = $eqLogic->createCmd('satisfaction', 'info', 'numeric', false, null, $configurationNetwork);
				$network_satisfaction->save();
				$network_satisfaction->event($client->satisfaction);
			} else {
				$network_network->event($client->network);

				$network_sw_mac = $eqLogic->createCmd('sw_mac', 'info', 'string', false, null, $configurationNetwork);
				$network_sw_mac->save();
				$network_sw_mac->event($client->sw_mac);

				$network_sw_depth = $eqLogic->createCmd('sw_depth', 'info', 'numeric', false, null, $configurationNetwork);
				$network_sw_depth->save();
				$network_sw_depth->event($client->sw_depth);

				$network_sw_port = $eqLogic->createCmd('sw_port', 'info', 'numeric', false, null, $configurationNetwork);
				$network_sw_port->save();
				$network_sw_port->event($client->sw_port);
			}

			array_push($ipClientList, $client->ip);
		}

		foreach ($devices_array as $device) {
			//log::add(__CLASS__, 'debug', 'device ' . print_r($device,true));
			log::add(__CLASS__, 'debug', '[' . $device->ip . '] annalyse du device « ' . $device->name . ' ».');
			$eqLogic = Unifi::byLogicalId($device->_id, __CLASS__);
			if (!is_object($eqLogic)) {
				$eqLogic = new Unifi();
				$eqLogic->setEqType_name(__CLASS__);
				$eqLogic->setLogicalId($device->_id);
				$eqLogic->setIsVisible(0);
				$eqLogic->setIsEnable(1);
			}
			$eqLogic->setName($device->name);
			$eqLogic->setConfiguration('ip', $device->ip);
			$eqLogic->setConfiguration('model', $device->model);
			$eqLogic->setConfiguration('type', 'device');
			$eqLogic->save();
			$network_active = $eqLogic->createCmd('active', 'info', 'binary', false, null, $configurationNetwork);
			$network_active->save();
			$network_active->event(1);
			$network_mac = $eqLogic->createCmd('mac', 'info', 'string', false, null, $configurationNetwork);
			$network_mac->save();
			$network_mac->event($client->mac);
			array_push($ipClientList, $device->ip);
		}
		
		$eqLogicList = self::byType(__CLASS__);
		foreach ($eqLogicList as $eqLogicToDesactive) {
			$ipClient = $eqLogicToDesactive->getConfiguration('ip');
			if(!in_array($ipClient, $ipClientList)) {
				log::add(__CLASS__, 'debug', '[' . $ipClient . '] Le client « ' . $eqLogicToDesactive->getName() . ' » n’est pas actif.');
				$eqLogicToDesactive->checkAndUpdateCmd('active', 0);
				$eqLogicToDesactive->checkAndUpdateCmd('network', "");
				$eqLogicToDesactive->checkAndUpdateCmd('satisfaction', "");
				$eqLogicToDesactive->checkAndUpdateCmd('sw_mac', "");
				$eqLogicToDesactive->checkAndUpdateCmd('sw_depth', "");
				$eqLogicToDesactive->checkAndUpdateCmd('sw_port', "");
				$eqLogicToDesactive->checkAndUpdateCmd('uptime', 0);
			}
			$network_essid = $eqLogicToDesactive->createCmd('essid', 'info', 'string', false, null, $configurationNetwork);
			$network_essid->remove();
		}
	}

	/**
	public static function cron5() {
	}
	 */

	/*
	 * Fonction exécutée automatiquement toutes les heures par Jeedom
	  public static function cronHourly() {

	  }
	 */

	/*
	 * Fonction exécutée automatiquement tous les jours par Jeedom
	  public static function cronDaily() {

	  }
	 */


	/*	 * *********************Méthodes d'instance************************* */

	public function preInsert() {
		
	}

	public function postInsert() {
		
	}

	public function createCmd($name, $type = 'info', $subtype = 'string', $icon = false, $generic_type = null, $configurationList = [], $displayList = []) {
		$cmd = $this->getCmd(null, $name);
		if (!is_object($cmd)) {
			$cmd = new UnifiCmd();
			$cmd->setLogicalId($name);
			$cmd->setName(__($name, __FILE__));
		}
		$cmd->setType($type);
		$cmd->setSubType($subtype);
		$cmd->setGeneric_type($generic_type);
		if ($icon) {
			$cmd->setDisplay('icon', $icon);
		}
		foreach ($configurationList as $key => $value) {
			$cmd->setConfiguration($key, $value);
		}
		foreach ($displayList as $key => $value) {
			$cmd->setDisplay($key, $value);
		}
		$cmd->setEqLogic_id($this->getId());
		return $cmd;
	}

	public function preUpdate() {
		
	}

	public function postUpdate() {
		
	}

	public function preRemove() {
	}

	public function postRemove() {
		
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		/* ------------ Ajouter votre code ici ------------ */
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '#'] = str_replace(array("\'", "'"), array("'", "\'"), $cmd->execCmd());
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getLogicalId() == 'encours') {
				$replace['#thumbnail#'] = $cmd->getDisplay('icon');
			}
			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
		}

		foreach ($this->getCmd('action') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_html#'] = $cmd->toHtml();
			if (!empty($cmd->getDisplay('icon'))) {
				$replace['#' . $cmd->getLogicalId() . '_icon#'] = $cmd->getDisplay('icon');
			} else {
				$replace['#' . $cmd->getLogicalId() . '_icon#'] = "<i class='icon divers-vlc1' title='Veuillez mettre une icon à l’action : " . $cmd->getLogicalId() . "'></i>";
			}
		}
		$replace['#image#'] = $this->getImage();
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, __CLASS__, __CLASS__)));
	}

	public function getImage() {
		$type = $this->getConfiguration('model');
		if (isset($type) && $type != "") {
			$url = "plugins/" . __CLASS__ . "/core/img/" . $type . ".png";
			if (file_exists($url)) {
				return $url;
			}
		}
		return parent::getImage();
	}

	/*
	 * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
	  public static function postConfig_<Variable>() {
	  }
	 */

	/*
	 * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
	  public static function preConfig_<Variable>() {
	  }
	 */

	/**
	 * Non obligatoire
	 * Obtenir l'état du daemon
	 *
	 * @return [log] message de log
	 *        [state]  ok  Démarré
	 *                  nok Non démarré
	 *        [launchable] ok  Démarrable
	 *                      nok Non démarrable
	 *        [launchable_message] Cause de non démarrage
	 *        [auto]   0 Démarrage automatique désactivé
	 *                  1 Démarrage automatique activé
	 */
	 /**
	public static function deamon_info() {
	}
	*/

	/**
	 * Démarre le daemon
	 *
	 * @param Debug (par défault désactivé)
	 */
	/**
	public static function deamon_start($_debug = false) {
	}
	*/

	/**
	 * Démarre le daemon
	 *
	 * @param Debug (par défault désactivé)
	 */
	 /**
	public static function deamon_stop() {
	}
	*/
/**
	public static function socket_start() {
	}

	public static function socket_stop() {
	}
*/
	/*	 * **********************Getteur Setteur*************************** */
}
