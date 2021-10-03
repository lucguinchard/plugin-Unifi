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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once 'Unifi.class.php';

class UnifiCmd extends cmd {
	/*	 * *************************Attributs****************************** */
	/*	 * ***********************Methode static*************************** */
	/*	 * *********************Methode d'instance************************* */
	/*
	 * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
	  public function dontRemoveCmd() {
	  return true;
	  }
	 */

	public function execute($_options = array()) {
		if ($this->getType() == 'info') {
			return;
		}
		$eqLogic = $this->getEqLogic();
		switch ($this->getLogicalId()) {
			case "test":
				break;
			default :
				log::add("Unifi", 'info', 'TODO: Créer la commande ' . $this->getLogicalId() . ' - ' . print_r($_options, true));
		}
	}

	/*	 * **********************Getteur Setteur*************************** */
}
