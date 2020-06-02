<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('geoloc');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="icon nature-planet5"></i> {{Mes équipements de géolocalisation}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="eqLogicThumbnailContainer">
				<div class="cursor eqLogicAction logoPrimary" data-action="add"  >
					<i class="fas fa-plus-circle"></i>
					<br>
					<span >{{Ajouter}}</span>
				</div>
			</div>
<?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	echo '<br>';
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
<?php
foreach (jeeObject::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Appareil Ios}}</label>
							<div class="col-sm-9">
								<input type="checkbox" class="eqLogicAttr isIos" data-l1key="configuration" data-l2key="isIos" />
							</div>
						</div>
						<div class="form-group ios" style="display:none;">
							<label class="col-md-2 control-label">{{Login iCloud}}</label>
							<div class="col-md-3">
								<input type="text" id="username_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="username" placeholder="Login iCloud"/>
							</div>
						</div>
						<div class="form-group ios" style="display:none;">
							<label class="col-md-2 control-label">{{Password iCloud}}</label>
							<div class="col-md-3">
								<input type="password" id="password_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="password" placeholder="Password iCloud"/>
							</div>
						</div>
						<div class="form-group ios" style="display:none;">
							<label class="col-md-2 control-label">{{Device}}</label>
							<div class="col-md-1">
								<input type="text" id="device_id" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="device" placeholder="Device Id" disabled/>
							</div>
							<div class="col-md-2">
								<input type="text" id="device_name" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="device_name" placeholder="Device Name" disabled/>
							</div>
							<div class="col-md-2 ios" style="display:none">
								<select id="sel_device" class="eqLogicAttr configuration form-control" disabled></select>
							</div>
							<div class="col-md-3 ios" style="display:none">
								<a class="btn btn-default" id="searchDevices">{{Charger les devices}}</a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Ne pas utiliser le widget spécifique}}</label>
							<div class="col-sm-9">
							<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="noSpecifyWidget" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<br/>
				<div class="alert alert-info">Exemple d’URL à appeler avec tasker : <?php echo network::getNetworkAccess('external')?>/plugins/geoloc/core/api/jeeGeoloc.php?apikey=<?php echo  jeedom::getApiKey('geoloc');?>&id=#ID_CMD#&value=%LOCN</div>
				<a class="btn btn-success btn-sm pull-right cmdAction" data-action="add"><i class="fa fa-plus-circle"></i> {{Commandes}}</a>
				<a class="control-label pull-left" href="http://www.coordonnees-gps.fr/" target="_blank"><i class="icon nature-planet5"></i>{{Cliquez-ici pour retrouver vos coordonnées}}</a>
				<br/>
				<br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 50px;">#</th>
							<th style="width: 200px;">{{Nom}}</th>
							<th style="width: 200px;">{{Type}}</th>
							<th>{{Options}}</th>
							<th style="width: 100px;">{{Paramètres}}</th>
							<th style="width: 150px;"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'geoloc', 'js', 'geoloc');?>
<?php include_file('core', 'plugin.template', 'js');?>
