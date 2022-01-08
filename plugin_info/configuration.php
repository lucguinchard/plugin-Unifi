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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

include_file('core', 'authentification', 'php');

if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
	<fieldset>
		<div class="container">
			<h3><i class="fas fa-exchange-alt"></i> {{Connexion API}}</h3>
			<div class="form-group">
				<label class="col-lg-2 control-label">{{Nom d’utilisateur}}</label>
				<div class="col-lg-4">
					<input type="text" class="configKey form-control" data-l1key="controller.user" value=""/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">{{Mot de passe}}</label>
				<div class="col-lg-4">
					<input type="password" class="configKey form-control" data-l1key="controller.password"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">{{Url}}</label>
				<div class="col-lg-4">
					<input type="text" class="configKey form-control" data-l1key="controller.url" placeholder="https://host"/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">{{Site(s)}}<sup><i class="fas fa-question-circle" title="{{Vous pouvez indiquer plusieurs site en les séparant avec des virgules.}}"></i></sup></label>
				<div class="col-lg-4">
					<input type="text" class="configKey form-control" data-l1key="controller.site" placeholder="default si un seul site"/>
				</div>
			</div>
			<br/>
			<h3><i class="fas fa-coffee"></i> {{Offrez-un café}}</h3>
			<div class="form-group">
				<div>
					{{Ce plugin est entièrement gratuit et indépendant, Mais si cela vous enchante j’accepte les}} <a href="https://www.paypal.com/paypalme/lucguinchard" title="{{thé/café/bière}}" target="_blank"><i class="fas fa-coffee"></i></a>.
				</div>
			</div>
		</div>
	</fieldset>
	<br/>
	<br/>
</form>
