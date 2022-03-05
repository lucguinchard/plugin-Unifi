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

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$pluginName = init('m');
$plugin = plugin::byId($pluginName);
sendVarToJS('eqType', $plugin->getId());
$eqLogicList = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="sync_network">
				<i class="fas fa-sync"></i>
				<br/>
				<span>{{Synchroniser}}</span>
			</div>
		</div>
		<legend><img style="width:40px" src="<?= $plugin->getPathImgIcon() ?>"/> {{Mes appareils}}</legend>
		<?php if (count($eqLogicList) == 0) { ?>
			<center>
				<span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n’avez pas encore d’appareil, cliquez sur configuration et cliquez sur synchroniser pour commencer}}</span>
			</center>
		<?php } else { ?>
			<div class="input-group" style="margin:5px;">
				<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
				<div class="input-group-btn">
					<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>
					<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
				</div>
			</div>
			<div class="panel panel-default">
				<h3 class="panel-title">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="" href="#Unifi_Device"><i class="fa fa-server"></i></i> Unifi Device</a>
				</h3>
				<div id="Unifi_Device" class="panel-collapse collapse in">
				<div class="eqLogicThumbnailContainer">
				<?php
				foreach ($eqLogicList as $eqLogic) {
					if($eqLogic->getConfiguration('type') != 'device') continue;
					$cmd_active = $eqLogic->getCmd(null, 'active');
					$opacity = (!$eqLogic->getIsEnable() || !$cmd_active->execCmd()) ? 'disableCard' : ''; 
					?>
					<div class="eqLogicDisplayCard cursor <?= $opacity ?>" data-eqLogic_id="<?= $eqLogic->getId() ?>">
						<img src="<?= $eqLogic->getImage() ?>"/>
						<br/>
						<span class="name"><?= $eqLogic->getHumanName(true, true) ?></span>
					</div>
				<?php } ?>
			</div>
			</div>
			</div>
			<div class="panel panel-default">
				<h3 class="panel-title">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="" href="#Unifi_Client"><i class="fa fa-laptop-code"></i> Unifi Client</a>
				</h3>
				<div id="Unifi_Client" class="panel-collapse collapse in">
				<div class="eqLogicThumbnailContainer">
				<?php
				foreach ($eqLogicList as $eqLogic) {
					if($eqLogic->getConfiguration('type') == 'device') continue;
					$cmd_active = $eqLogic->getCmd(null, 'active');
					$opacity = (!$eqLogic->getIsEnable() || !$cmd_active->execCmd()) ? 'disableCard' : ''; 
					$color = ($cmd_active->execCmd()) ? 'green' : 'red'; 
					$cmd_is_wired = $eqLogic->getCmd(null, 'is_wired');
					$cmd_experience = $eqLogic->getCmd(null, 'experience');
					if (is_object($cmd_experience)){
						$experience = $cmd_experience->execCmd();
					} else {
						$experience = "";
					}
					if (!is_object($cmd_is_wired) || $cmd_is_wired->execCmd()) { 
						$is_wired = '<i class="fas fa-ethernet"></i>';
						switch ($experience) {
							case 10000: $experience = "10 GbE"; break;
							case 1000: $experience = "GbE"; break;
							case 100: $experience = "FE"; break;
						}
					} else {
						$is_wired = '<i class="fas fa-wifi"></i> ';
						$experience .= " %";
					}
					$cmd_network = $eqLogic->getCmd(null, 'network');
					if (is_object($cmd_network)){
						$network = $cmd_network->execCmd();
					}
					?>
					<div class="eqLogicDisplayCard cursor <?= $opacity ?>" data-eqLogic_id="<?= $eqLogic->getId() ?>">
						<img src="<?= $eqLogic->getImage() ?>"/>
						<br/>
						<span class="displayTableRight">
							<span style="color:white;border-radius: 10%;padding: 0 2px;background-color: <?= $color ?>;font-size: x-small;"><?= $is_wired ?> <?= $network ?></span>
							<span style="color:white;border-radius: 10%;padding: 0 2px;background-color: blue;font-size: x-small;"><?= $experience ?></span>
						</span>
						<span class="name">
							<?= $eqLogic->getHumanName(true, true) ?>
						</span>
					</div>
				<?php } ?>
			</div>
			</div>
			</div>
		<?php } ?>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span></a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
				<a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i><span class="hidden-xs"> {{Supprimer}}</span></a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab"
				   data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
					<i class="fas fa-tachometer-alt"></i> {{Equipement}}
				</a>
			</li>
			<li role="presentation"><a href="#networktab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fas fa-ethernet"></i> {{Network}}</a>
			</li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="row">
					<div class="col-sm-3">
						<center>
							<img id="img_device" src="<?= $plugin->getPathImgIcon(); ?>" style="height : 200px;" />
							<div id="img_device_not_found">
								<p>{{Pas d’image de votre appareil ?}}</p>
								<p>{{Proposer une image}} <a href="https://github.com/lucguinchard/plugin-Unifi/issues/new?assignees=&labels=type%3AEnhancement&template=LOGO_DEVICE_EMPTY.md&title=L%E2%80%99image+de+mon+appareil+%60aaa%60+n%E2%80%99existe+pas." target="_blank">{{ici}}</a>.</p>
							</div>
						</center>
					</div>
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-6 control-label" for="name">{{Nom de l’équipement}}</label>
									<div class="col-sm-3">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id"
											   style="display : none;"/>
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" id="name"
											   placeholder="{{Nom de l’équipement}}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-6 control-label" for="sel_object">{{Objet parent}}</label>
									<div class="col-sm-3">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php foreach (jeeObject::all() as $object) { ?>
												<option value="<?= $object->getId() ?>"><?= $object->getName() ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-6 control-label"></label>
									<div class="col-sm-6">
										<label class="checkbox-inline" for="is-enable">
											<input type="checkbox" class="eqLogicAttr" data-l1key="isEnable"
												   checked="checked" id="is-enable"/>
											{{Activer}}
										</label>
										<label class="checkbox-inline" for="is-visible">
											<input type="checkbox" class="eqLogicAttr" data-l1key="isVisible"
												   checked="checked" id="is-visible"/>
											{{Visible}}
										</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-6 control-label" for="model">{{Model}}</label>
									<div class="col-sm-3">
										<input type="text" disabled="disabled" class="eqLogicAttr form-control" id="model"
											   data-l1key="configuration" data-l2key="model"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-6 control-label" for="ip">{{ip}}</label>
									<div class="col-sm-3">
										<input type="text" disabled="disabled" class="eqLogicAttr form-control" id="ip"
											   data-l1key="configuration" data-l2key="ip"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-6 control-label" for="lastCommunication">{{lastCommunication}}</label>
									<div class="col-sm-3">
										<input type="text" disabled="disabled" class="eqLogicAttr form-control" id="lastCommunication"
											   data-l1key="status" data-l2key="lastCommunication"/>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div id="networktab" role="tabpanel" class="tab-pane">
				<div class="table-responsive">
					<table id="table_network" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th style="width: 200px;">{{Nom}}</th>
								<th>{{Type}}</th>
								<th></th>
								<th style="width: 150px;">{{Action}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
include_file('desktop', $pluginName, 'js', $pluginName);
include_file('core', 'plugin.template', 'js');
