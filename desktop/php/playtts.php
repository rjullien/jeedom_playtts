<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$eqLogics=eqLogic::byType('playtts');
sendVarToJS('eqType', 'playtts');
?>

<div class="row row-overflow">
    <div class="col-lg-2">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true,true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes PlayTTS}}
        </legend>
        <?php
        if (count($eqLogics) == 0) {
            echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore de playtts, cliquez sur Ajouter pour commencer}}</span></center>";
        } else {
            ?>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/playtts/doc/images/playtts_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>
    <div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <form class="form-horizontal">
            <fieldset>
                <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Nom de l'équipement playtts}}</label>
                    <div class="col-lg-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement playtts}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label" >{{Objet parent}}</label>
                    <div class="col-lg-3">
                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                            <option value="">{{Aucun}}</option>
                            <?php
                            foreach (object::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Catégorie}}</label>
                    <div class="col-lg-8">
                        <?php
                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                            echo '<label class="checkbox-inline">';
                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                            echo '</label>';
                        }
                        ?>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label" >{{Activer}}</label>
                    <div class="col-lg-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
                    </div>
                    <label class="col-lg-1 control-label" >{{Visible}}</label>
                    <div class="col-lg-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Options mplayer (optionnel)}}</label>
                    <div class="col-lg-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="opt"/>
                    </div>
                </div>
                <div class="form-group">
					<label class="col-md-2 control-label">{{Moteur TTS}}</label>
					<div class="col-md-3">
						<select id="moteurTTS" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="moteurTTS"
						onchange="if(this.selectedIndex == 1){ document.getElementById('url').style.display = 'block'; document.getElementById('lang').style.display = 'none';}
						else {document.getElementById('url').style.display = 'none'; document.getElementById('lang').style.display = 'block';}">
							<option value="gTTS">{{Google TTS}}</option>
							<option value="pico">{{Pico TTS}}</option>
							<option value="url">{{URL personnalisée}}</option>
						</select>
					</div>
                </div>
                <div id="url">
					<div class="form-group">
						<label class="col-lg-2 control-label">{{URL personnalisée}}</label>
						<div class="col-lg-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="url" type="text" placeholder="{{saisir l'adresse}}">
						</div>
					</div>
				</div>
				<div id="lang">
	                <div class="form-group">
	                    <label class="col-lg-2 control-label">{{Langue}}</label>
	                    <div class="col-lg-3">
	                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="lang">
	                            <option value="">{{Aucun}}</option>
	                            <option value="af">Afrikaans</option>
	                            <option value="sq">Albanian</option>
	                            <option value="ar">Arabic</option>
	                            <option value="hy">Armenian</option>
	                            <option value="ca">Catalan</option>
	                            <option value="zh-CN">Mandarin (simplified)</option>
	                            <option value="zh-TW">Mandarin (traditional)</option>
	                            <option value="hr">Croatian</option>
	                            <option value="cs">Czech</option>
	                            <option value="da">Danish</option>
	                            <option value="nl">Dutch</option>
	                            <option value="en">English</option>
	                            <option value="en-us">English (United States)</option>
	                            <option value="en-au">English (Australia)</option>
	                            <option value="eo">Esperanto</option>
	                            <option value="fi">Finnish</option>
	                            <option value="fr">French</option>
	                            <option value="de">German</option>
	                            <option value="el">Greek</option>
	                            <option value="ht">Haitian Creole</option>
	                            <option value="hi">Hindi</option>
	                            <option value="hu">Hungarian</option>
	                            <option value="is">Icelandic</option>
	                            <option value="id">Indonesian</option>
	                            <option value="it">Italian</option>
	                            <option value="ja">Japanese</option>
	                            <option value="ko">Korean</option>
	                            <option value="la">Latin</option>
	                            <option value="lv">Latvian</option>
	                            <option value="mk">Macedonian</option>
	                            <option value="no">Norwegian</option>
	                            <option value="pl">Polish</option>
	                            <option value="pt">Portuguese</option>
	                            <option value="ro">Romanian</option>
	                            <option value="ru">Russian</option>
	                            <option value="sr">Serbian</option>
	                            <option value="sk">Slovak</option>
	                            <option value="es">Spanish</option>
	                            <option value="sw">Swahili</option>
	                            <option value="sv">Swedish</option>
	                            <option value="ta">Tamil</option>
	                            <option value="th">Thai</option>
	                            <option value="tr">Turkish</option>
	                            <option value="vi">Vietnamese</option>
	                            <option value="cy">Welsh</option>

	                        </select>
	                    </div>
	                </div>
	            </div>
				<div class="form-group">
					<label class="col-md-2 control-label">{{Equipement local ou déporté ?}}</label>
					<div class="col-md-3">
						<select id="maitreesclave" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="maitreesclave"
						onchange="if(this.selectedIndex == 1) document.getElementById('deporte').style.display = 'block';
						else document.getElementById('deporte').style.display = 'none';">
							<option value="local">{{Local}}</option>
							<option value="deporte">{{Déporté}}</option>
						</select>
					</div>
                </div>
				<div id="deporte">
					<div class="form-group">
						<label class="col-md-3 control-label">{{Adresse IP}}</label>
						<div class="col-md-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addressip" type="text" placeholder="{{saisir l'adresse IP}}">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">{{Port SSH}}</label>
						<div class="col-md-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="portssh" type="text" placeholder="{{saisir le port SSH}}">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">{{Identifiant}}</label>
						<div class="col-md-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="user" type="text" placeholder="{{saisir le login}}">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">{{Mot de passe}}</label>
						<div class="col-md-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" type="password" placeholder="{{saisir le password}}">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">{{Chemin}}</label>
						<div class="col-md-3">
							<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="chemin" type="text" placeholder="{{saisir le chemin}}">
						</div>
					</div>
				</div>
            </fieldset>
        </form>



    <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>

<?php include_file('core', 'plugin.template', 'js'); ?>
