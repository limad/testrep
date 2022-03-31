<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugId = 'VaillantControl';
$plugin = plugin::byId($plugId);

sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$plugName=$plugin->getName();
?>



<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
		
		
  			<div class="cursor eqLogicAction logoDefault" id="bt_healthVaillantControl">
				<i class="fas fa-medkit" style=""></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
  
  
  			<div class="cursor eqLogicAction logoDefault" data-action="removeAll" id="bt_removeAll">
				<i class="fas fa-minus-circle" style="color: #FA5858;"></i>
				<br/>
				<span>{{Supprimer tous}}</span>
			</div>
		</div>
		
		<legend>{{Mes Pièces Netatmo Énergie}}</legend>
		
					<?php
			if (count($eqLogics) == 0) {
				echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>
                	{{Cliquez sur Configuration pour commencer !}}</span></center>";
			} 
			else {
              	echo '<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />';
				echo '<div class="eqLogicThumbnailContainer">';
				$sortEqLogs=[];
              	$Homes=[];
               	foreach(VaillantControl::byType($plugin->getId()) as $eqLogic) {
                    $type = $eqLogic->getConfiguration('type', '');
                    if($type == "Home"){
                        $Homes[] = $eqLogic;
                    }elseif($type == "NATherm1"){
                      	array_unshift($sortEqLogs, $eqLogic);
                    }else{
                      $sortEqLogs[] = $eqLogic;
                      	//array_push($sortEqLogs, $eqLogic);
                    }
                }
              	$eqLogics = array_merge($Homes, $sortEqLogs);
              	foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					$imgPath =  __DIR__  . '/../../core/img/room_'. $eqLogic->getConfiguration('type', '') . '.png';
					
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                  	if ( file_exists($imgPath) ) {
                      	echo '<img src="plugins/VaillantControl/core/img/room_'. $eqLogic->getConfiguration('type', '') . '.png"/>';
                   	} else {
						echo '<img src="plugins/VaillantControl/plugin_info/VaillantControl_icon.png" style="height : 100px"/>';
                      
					}
					echo '<br/>';
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
					
					
				}
			}
			
	?>	
		</div>
	</div>
	
	<div class="col-xs-12 eqLogic" style="display: none;">
	<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
      			<a class="btn btn-default btn-sm roundedLeft" id="bt_eqConfigRaw"><i class="fas fa-info">  </i>  </a>
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> Configuration avancée</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> Sauvegarder</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> Supprimer</a>
			</span>
		</div>
		
		
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay">
      			<i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
      			<i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" data-toggle="tab" aria-controls="profile" role="tab" >
      			<i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		
		<!--
		<li  role="presentation"><a href="#configureAdvanced" data-toggle="tab" ><i class="fas fa-cog" aria-hidden="true"></i> {{Avancée}}</a></li>
		-->
	   <?php
			
			?>
      
      <?php
			try {
				$isMaster = VaillantControl::$_isMaster;
				if ($isMaster == true) {
					?>
					<li role="presentation" id="LiAdvancedConfig">
                      <a href="#AdvancedConfig" data-toggle="tab" ><i class="fas fa-cog" aria-hidden="true"></i> {{Avancée}}</a>
                    </li>
					<?php
				}
			} catch (Exception $e) {
				
			}
			?>
		</ul>
		
		
		
		
		
		
		<div class="tab-content">
               <?php
			$eqLogic = VaillantControl::byId(init("id"));
			if (!is_object($eqLogic)) {
              $eqLogic = false;
            }
			?>
              



			<!-- *********** eqlogictab  ****-->
			<div class="tab-pane active" id="eqlogictab">
				<br/>
				<legend><i class="fas fa-tachometer-alt"></i> {{Général}}</legend>
				<div class="row">
					<div class="col-lg-9"><!--infos-->
						<form class="form-horizontal">
							<fieldset>
      						<!--*******************************  -->
								<div class="form-group">
									<label class="col-lg-3 control-label" >{{Equipements}}</label>
									<div class="col-lg-3">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;  " />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du thermostat}}" />
									</div>
								</div>
							<!--*******************************  -->
								 <div class="form-group">
                                    <label class="col-lg-3 control-label">{{Objet parent}}</label>
                                    <div class="col-lg-3">
                                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                            <option value="">{{Aucun}}</option>
                                            <?php
                                            $options = '';
                                            foreach ((jeeObject::buildTree(null, false)) as $object) {
                                                $options .= '<option value="' . $object->getId() . '">' 
                                                  			. str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) 
                                                  			. $object->getName() . '</option>';
                                            }
                                            echo $options;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                              
                                
                             <!--*******************************  -->
								 <div class="form-group" id="naEqCcategory">
                                    <label class="col-lg-3 control-label">{{Catégorie}}</label>
                                    <div class="col-lg-9">
                                        <?php
                                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                            echo '<label class="checkbox-inline">';
                                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                            echo '</label>';
                                        }
                                        ?>
                                    </div>
                                </div>
							<!--*******************************  -->
								<div class="form-group">
									<label class="col-lg-3 control-label">{{Activer}}</label>
									<div class="col-lg-3">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
                                          
							</fieldset>
						</form>
						<br>
						<!-- Informations  --> 
						<legend><i class="fas fa-info" aria-hidden="true"></i> {{Informations}}</legend>
						<form class="form-horizontal">
							<fieldset>
									<!--*******************************  -->
									<div class="form-group" id="ident">
										<label class="col-lg-3 control-label">{{Identifiant}}</label>
											<div class="col-lg-4">
												<input disabled  class="eqLogicAttr form-control"  data-l1key="logicalId"/>
											</div>
									</div> 
									
                                    
                                    <!--*******************************  -->
                                    <!--
                                              
                                        <div class="form-group" id="firmware" >
										<label class="col-lg-3 control-label">{{Firmware}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="firmware"/>
											</div>
											
									</div>
									-->
									<!--*******************************  -->						
									<div class="form-group" id="typeroom">
										<label class="col-lg-3 control-label">{{Type}}</label>
											<div class="col-lg-4">
												<input disabled  class="eqLogicAttr form-control"  
                                              		data-l1key="configuration" data-l2key="type" />
											</div>
									</div>
                                              
                                 <!--*******************************  --> 
									<div class="form-group" id="gatewayType" >
										<label class="col-lg-3 control-label">{{Type gateway}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="gatewayType"/>
											</div>
											
									</div>
                                  <!--*******************************  -->
                                  	<div class="form-group" id="firmware_version" >
										<label class="col-lg-3 control-label">{{Firmware gateway}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="firmware_version" />
											</div>
											
									</div>
                                  <!--*******************************  --> 
                                    <div class="form-group" id="boilerType" >
										<label class="col-lg-3 control-label">{{Type Chaudiere}}</label>
											<div class="col-lg-4">
												<input disabled class="eqLogicAttr form-control" data-l1key="configuration" 
                                              		data-l2key="boilerType" />
											</div>
											
									</div>
                                  <!--*******************************  -->  
                			
							</fieldset>
						</form>
				
				
				 
					</div>
					
					<div class="col-lg-3"><!--img-->
						<center>
									
							<span class="eqLogicAttr" data-l1key="configuration"  style="display:none;"></span>
							<img id="img_VaillantControlModel" src="plugins/VaillantControl/plugin_info/<?php echo $plugId;?>_icon.png" style="height : 200px"/>
                                      
                         </center>
                                          
                                          
                         <!--*******************************  --> 
                        <!-- Modules ******************************* 
                         <fieldset>
                         <legend><i class="fas fa-tasks" aria-hidden="true"></i> {{Modules}}</legend>
                          <form class="form-horizontal">                   
						<?php	
							if (is_object($eqLogic)){
                              	$cmd = $eqLogic->getCmd(null, 'eqmodules');
                              	if(is_object($cmd)){
                              		$cmdeqmodules = $cmd->execCmd();
                                    $eqmodules = json_decode($cmdeqmodules, true);
                                    $natype = ["NAPlug"=>"Relai","OTH"=>"OT-Relai","NRV"=>"Vanne", "NATherm1"=>"Thermostat","OTM"=>"Thermostat", ];
                                  echo '';
                                    foreach($eqmodules as $eqmodule){
                                      $eqmodule_type=$eqmodule['type'];
                                      $eqmodule_name=($eqmodule['name'] != "") ? '|'.$eqmodule['name'] : "";
                                      echo '<div class="form-group"  >';
                                          echo '<div class="col-sm-1">';
                                              echo '<img src="plugins/VaillantControl/core/img/'. $eqmodule_type . '.png"  style="height : 20px; padding-left: 10px;" />';
                                          echo '</div>';
                                          echo '<label class="col-sm-3 control-label">{{'.$natype[$eqmodule_type].'}}';

                                          echo '</label>';
                                          echo '<div class="col-sm-2">';
                                              echo '<span class="label label-info " >'.$eqmodule['id'].$eqmodule_name.'</span>';
                                          echo '</div>';
                                      echo '</div>'; 
                                  }
                                  echo '';
                                }
							}
						?>
                          </form> 
                        </fieldset>	
                          --> 
                          <!--*******************************  -->  
                        			<!--
									<label class="col-lg-2 control-label">{{Home}}</label>
									<div class="col-lg-4">
										<span class="eqLogicAttr label label-info " data-l1key="configuration" data-l2key="type"></span>
									</div>
                                    --> 
									
                        <!--*******************************  -->                   
					</div>
				</div>
				<!--row-->
			
					
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
			<br>	
				
			<!--Config durées par défaut-->
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fas fa-calendar" aria-hidden="true"></i> {{Configuration durées par défaut(min)}}</legend>
						<form class="form-horizontal">
						<div class="col-lg-7">
							
						<!--*******************************  --> 	
						<div class="form-group" >
								<label class="col-lg-3 control-label" >{{Mode Quick Veto}}
                                <sup><i class="fas fa-question-circle tooltips" title="Paramètre"></i></sup>
                                 </label>
									<div class="col-lg-3">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="qv_spTemperature" id="qv_spTemperature" placeholder="{{Consigne en C°}}"/>
									</div>
                                  <div class="col-lg-3">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="qv_duaration" id="qv_duaration" placeholder="{{Durée en minutes (60 par défaut)}}"/>
									</div>
							</div>
					<!--*******************************  --> 
							<div class="form-group"  >
								<label class="col-lg-3 control-label">{{Mode absent}}
                                <sup><i class="fas fa-question-circle tooltips" title="Paramètre Jeedom"></i></sup>
                                 </label>
									<div class="col-lg-3">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="away_spTemperature" id="away_spTemperature" placeholder="{{Consigne en C°}}" />
									</div>
                                  <div class="col-lg-3">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="away_duaration" id="away_duaration" placeholder="{{Durée en minutes}}" />
									</div>
                                  
							</div>   
						<!--*******************************  --> 				     
							<div class="form-group" >
								<label class="col-lg-3 control-label" >{{Consigne Mode Manuel}}
                                <sup><i class="fas fa-question-circle tooltips" title="Paramètre Netatmo"></i></sup>
                                 </label>
									<div class="col-lg-6">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="manual_spTemperature" id="manual_spTemperature" placeholder="{{Consigne en C°}}"/>
									</div>
							</div> 
						<!--*******************************  -->  
							<div class="form-group"  >
								<label class="col-lg-3 control-label">{{Mode Reduit}}
                                	<sup><i class="fas fa-question-circle tooltips" title="Paramètre Jeedom"></i></sup>
                                </label>
									<div class="col-lg-6">
										<input disabled type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="setback_spTemperature" id="setback_spTemperature" placeholder="{{Consigne en C°}}"/>
									</div>
							</div> 
						</div>
						<div class="col-lg-9"></div>
							

						
						</form>
					</fieldset>
				</form>
				
				<br>
				<form class="form-horizontal" id="form_otherConf">
					<fieldset>
						<legend><i class="fas fa-wrench" aria-hidden="true"></i> {{Configuration autres...}}</legend>
						<div class="col-lg-6">
                            <!--*******************************  -->
                          	<div class="form-group" id="is_Boostable">
								<label class="col-lg-4 control-label">{{Appartient au groupe Boost}}</label>
									<div class="col-lg-4">
										<label class="checkbox-inline">
                          					<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="is_Boostable" checked/>{{Coché pour Oui}}
										</label>
									</div>
							</div>
                          <!--*******************************  -->
                                          
                          <!--*******************************  -->
							<div class="form-group" id="areaheat" style="display:none">
                                <label class="col-lg-4 control-label" > {{Surface chaufée (m²)}}
								<sup><i class="fas fa-question-circle tooltips" title="Pour des fonctions futures..."></i></sup>
                                 
								</label>
								<div class="col-lg-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="areaheat" placeholder="{{}}" />
								</div>
							</div>
                            <!-- *******************************  

							<div class="form-group" id="tuileytpe">
								<label class="col-lg-4 control-label" > {{Type de la tuile}}
						<sup><i class="fas fa-question-circle tooltips" title="Tuile qui affichera les infos"></i></sup>
</label>
								<div class="col-lg-4">
								<select id="sel_tuile" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="eqtuile">
											<option value="default" selected="selected"> {{Default}}</option>
											<option value="custom" > {{Custom}}</option>
                                            <option value="core"> {{Core}}</option>
								</select>
								
								</div>
							</div> -->
							<!--*******************************  -->
                          	<div class="form-group" id="dashboardeqtuile">
								<label class="col-lg-4 control-label" > {{Type de la tuile Dashboard}}
						<sup><i class="fas fa-question-circle tooltips" title="Tuile Dashboard qui affichera les infos"></i></sup>
</label>
								<div class="col-lg-4">
								<select id="sel_tuiled" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="dashboardeqtuile">
											<option value="default" selected="selected"> {{Default}}</option>
											<option value="custom" > {{Custom}}</option>
                                            <option value="core"> {{Core}}</option>
								</select>
								
								</div>
							</div> 
							<!--*******************************  -->
                          	<!--*******************************  -->
                          	<div class="form-group" id="mobileeqtuile">
								<label class="col-lg-4 control-label" > {{Type de la tuile Mobile}}
						<sup><i class="fas fa-question-circle tooltips" title="Tuile Mobile qui affichera les infos"></i></sup>
</label>
								<div class="col-lg-4">
								<select id="sel_tuilem" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mobileeqtuile">
											<option value="default" selected="selected"> {{Default}}</option>
											<option value="custom" > {{Custom}}</option>
                                            <option value="core"> {{Core}}</option>
								</select>
								
								</div>
							</div> 
							<!--*******************************  -->
                          	<!--*******************************  -->
                          	<div class="form-group" id="showHomeMode">
								<label class="col-lg-4 control-label">{{Dupliquer les bouttons Mode}}</label>
									<div class="col-lg-4">
										<label class="checkbox-inline">
                          					<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="showHomeMode" checked/>{{Coché pour Oui}}
										</label>
									</div>
							</div>
                                          
                          <!--*******************************  -->
                          <div class="form-group" id="showPlannings">
								<label class="col-lg-4 control-label">{{Afficher le planning}}</label>
									<div class="col-lg-4">
										<label class="checkbox-inline">
                          					<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="showPlannings" checked/>{{Coché pour Oui}}
										</label>
									</div>
							</div>
                                          
                          <!--*******************************  -->
                          <div class="form-group" id="showRoomModtech">
                          		<label class="col-lg-4 control-label">{{Afficher le mode de la pièce}}</label>
									<div class="col-lg-4">
										<label class="checkbox-inline">
                          					<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="showRoomModtech" checked/>{{Coché pour Oui}}
										</label>
									</div>
                          </div>
                                          
                           
                                          
                                          
                                          
                                          
                          
                           <!--*******************************  -->

                            <br/>                 
                            <!--*******************************  -->   
								<!--
								<div class="form-group" id="adv_config1">
									<label class="col-lg-4 control-label"  ></label>
										<div class="col-lg-4">
											<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="advancedctrl" /><label>{{Controle avancé}} </label>
										</div>
								</div> 
								-->
						</div>
					
						<div class="col-lg-6"></div>
					</fieldset>
				</form>
			</div>
			<!-- *********** commandtab  ****-->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<legend>
					<center class="title_cmdtable">{{Tableau de commandes <?php echo ' - '.$plugName.': ';?>}}
						<span class="eqName"></span>
					</center>
				</legend>
				
				<legend><i class="fas fa-info-circle"></i>  {{Infos}}</legend>
					<!--
                          table table-bordered table-condensed
                          <table class="table tablesorter tablesorter-bootstrap tablesorter hasResizable table-striped hasFilters" id="table_update" style="margin-top: 5px;" role="grid"><colgroup class="tablesorter-colgroup"></colgroup>
							</table>
                          
                          -->
                          <table id="table_cmdi" class="table table-bordered table-condensed ">
							
							<thead>
								<tr>
									<th style="width: 40px;">Id</th>
									<th style="width: 280px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 220px;">{{Options}}</th>
									<th style="width: 80px;">{{Action}}</th>
									 
								</tr>
							</thead>
							<tbody></tbody>
						</table>

						<legend><i class="fas fa-list-alt"></i>  {{Actions}}</legend>
						<table id="table_cmda" class="table table-bordered table-condensed">
							
							<thead>
								<tr>
									<th style="width: 40px;">Id</th>
									<th style="width: 280px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 220px;">{{Options}}</th>
									<th style="width: 80px;">{{Action}}</th>
									 
								</tr>
							</thead>
							<tbody></tbody>
						</table>

				
					</div><!--fin *********** commandtab  ****-->
                          
<!-- *********** AdvancedConfig  ****-->                          
       	                  
<!-- *********** Fin AdvancedConfig  ****-->  	
                                  
                                  
                                  
                                  
<!-- *********** tab-pane configureAdvanced  ****-->
			<!-- *********** Fin configureAdvanced  ****-->   
         
	</div><!-- *********** fin div class="tab-content" ****-->
</div><!-- *********** fin col-xs-12 eqLogic ****-->
                                  
                                  
    <script>  
 

                            
                                  
                                  
    $("#form_otherConf").delegate(".listCmdInfo", 'click', function () {
    var el = $('.eqLogicAttr[data-l2key=' + $(this).attr('data-input') + ']');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        if (el.attr('data-concat') == 1) {
            el.atCaret('insert', result.human);
        } else {
            el.value(result.human);
        }
    });
});

</script>
<?php include_file('desktop', 'VaillantControl', 'js', 'VaillantControl');?>
<?php include_file('core', 'plugin.template', 'js');?>