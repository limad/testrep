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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
$plugId = 'VaillantControl';
$plugin = plugin::byId('VaillantControl');
$is_wshook = config::byKey('wshook::enable', 'VaillantControl', 0);
$hasDaemon = $plugin->getHasOwnDeamon();

sendVarToJS('is_wshook', $is_wshook);
sendVarToJS('hasDaemon', $hasDaemon);



?>


<form class="form-horizontal">
    <fieldset>

        <!---->
   <div class="row">       
	<div class="col-md-12 col-sm-12">
  	<div id="div_alertPlugConf" class="jqAlert"></div>
  </div>
 </div>
  
  
 <div class="row">       
	<div class="droite col-md-6 col-sm-6">
  
    <div class="form-group">
        <label class="col-sm-6 control-label">{{Utilisateur}}
			<sup><i class="fas fa-question-circle tooltips" title="Nom d'Utilisateur Vaillant"></i></sup>
		</label>
        <div class="col-sm-6">
            <input type="text" class="configKey form-control" data-l1key="username" placeholder="Utilisateur"/>
              
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-6 control-label">{{Mot de passe}}
			<sup><i class="fas fa-question-circle tooltips" title="Mot de passe du compte"></i></sup>
		</label>
        <div class="col-sm-6">
            <input type="text" class="inputPassword configKey form-control" data-l1key="napassword" value="Mot de passe">
        </div>
    </div>
    <div class="form-group">
			<label class="col-sm-6 control-label">{{Type du dispositif}}
              	<sup><i class="fas fa-question-circle tooltips" title="Senso/Multimatic"></i></sup>
			</label>
            <div class="col-sm-6">
				<select class="configKey form-control" data-l1key="mVt_prefix">
					<option value="/tli/v1">{{Senso}}</option>
					<!--<option value="/v1">{{Multimatic}}</option>--> 
  				</select>
			</div>
		</div>	
    <!--        --> 
    <div class="form-group">
        <label class="col-sm-6 control-label">{{Synchroniser}}
			<sup><i class="fas fa-question-circle tooltips" title="Pour synchroniser les équipements Netatmo avec le plugin"></i></sup>
</label>
        <div class="col-sm-6">
        <a class="btn btn-success" id="bt_syncWithStation"><i class='fas fa-refresh'></i> {{Synchroniser mes équipements}}</a>
         <!-- <a class="btn btn-success" id="bt_Test"><i class='fas fa-refresh'></i> {{Btn Test}}</a>  -->    
              
        </div>
    </div>
  
          
   
    
    
    
    
    </div> <!--fin col-->
   <div class="gauche col-md-6 col-sm-6">
	<!--        
   
 --> 
              
              
     <!--        -->
     <div class="form-group">
        <label class="col-sm-5 control-label">{{Objets par défaut}}
			<sup><i class="fas fa-question-circle tooltips" title="Objet Jeedom oû les équipements seront créer"></i></sup>
		</label>
        <div class="col-sm-3">
            <select id="sel_object" class="inputPassword configKey form-control" data-l1key="defaultParentObject" value="">
            <option value="">{{Aucune}}</option>
			  <?php
				foreach (jeeObject::all() as $object) {
				  echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
				}
			  ?>
			</select>
        </div>
    </div>
	<div class="form-group">
            
                     
            
          
            
            
            
    </div>
    
     
	</div>
</div>	 <!--row-->
	
	
	
    
    
    
    
    

</fieldset>
</form>

<script>
      


      
      
$("input[data-l1key='wshook::enable']").on('change',function(){
        if ($(this).is(':checked')){
          	$("input[data-l1key='wbhook::enable']").prop("checked", false)
          	$(".configKey[data-l1key='wshook::enable']").val(true)
			$(".configKey[data-l1key='wbhook::enable']").val(false)
            console.log("wshook : checked ");
        }
	 	});
$("input[data-l1key='wbhook::enable']").on('change',function(){
        var locate = document.location.search;
  		if ($(this).is(':checked')){ 
			$("input[data-l1key='wshook::enable']").prop("checked", false)
            $(".configKey[data-l1key='wbhook::enable']").val(true)
			$(".configKey[data-l1key='wshook::enable']").val(false)
            console.log("wbhook : checked ");
        }
    });      
      
  

    $('#bt_syncWithStation').on('click', function (){	
      	$('#div_alertPlugConf').empty();
      	$('#div_alertPlugConf').removeClass('alert-warning');
      	$('#div_alertPlugConf').removeClass('alert-danger');
      	$('#div_alertPlugConf').removeClass('alert-success');
      	//$('#div_alertPlugConf').removeClass();
		if($('.configKey[data-l1key=username]').val() === ''){
            $('#div_alertPlugConf').showAlert({message: 'Renseignez votre "Email" SVP !' , level: 'warning'});
            $('#div_alertPlugConf').removeClass('alert-success');
            $('#div_alertPlugConf').addClass('alert-danger');
            return;
        }
      	else if($('.configKey[data-l1key=napassword]').val() === ''){
          	
            $('#div_alertPlugConf').showAlert({message: 'Renseignez votre "Mot de passe" SVP !' , level: 'warning'});
            $('#div_alertPlugConf').removeClass('alert-success');
            $('#div_alertPlugConf').addClass('alert-danger');
            return;
		}
		else{
            $('#div_alertPlugConf').removeClass('alert-success');
            $('#div_alertPlugConf').removeClass('alert-danger');
            $('#div_alertPlugConf').empty();
          	 $('#bt_savePluginConfig').trigger('click');
          
            //savePluginConfig();
            $.ajax({
            	type: "POST",
                url: "plugins/VaillantControl/core/ajax/VaillantControl.ajax.php",
                data: { action: "syncStations",},
                dataType: 'json',
                error: function (request, status, error) {
                     handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {//erreur api
                        if (data.result != 'bad request') {
                                        $('#div_alertPlugConf').showAlert({message: '{{Echec de connexion. Vérifier que les informations saisies sont correctes. Msg:: }}' + data.result, level: 'danger'});
                                        $('#div_alertPlugConf').removeClass('alert-success');
                                        $('#div_alertPlugConf').addClass('alert-danger');
                                    }else{ //pas de reponse api
                    		 $('#div_alertPlugConf').showAlert({message: '{{Echec de connexion. VVérifier la connectivité internet. No response. Msg:: }}' + data.result, level: 'danger'});
                             $('#div_alertPlugConf').removeClass('alert-success');
                             $('#div_alertPlugConf').addClass('alert-danger');
                          	 setTimeout(function() {window.location.reload();}, 3000);
						}
                        return ;
                    }
                  	var modal = $('#md_modal').is(':visible');
					if(modal){
						$('#div_alertPlugConf').addClass('alert-success');
                  		$('#div_alertPlugConf').append('Bravo!.. Synchronisation '  + ' - ' + data.result);
                      	setTimeout(function() {location.reload();}, 3000);
                      	$('#div_alert').addClass('alert-success');
                  		$('#div_alert').append(' Bravo!.. Synchronisation: '  + ' - ' + data.result);
                      	
					}else{
						$('#div_alert').addClass('alert-success');
                  		$('#div_alert').append(' Bravo!.. Synchronisation: '  + ' - ' + data.result);
					}
					
                }
			});
		}
	});

	$("input[data-l1key='functionality::cron5::enable']").on('change',function(){
        if ($(this).is(':checked')) 
			$("input[data-l1key='functionality::cron10::enable']").prop("checked", false)
	 });

    $("input[data-l1key='functionality::cron10::enable']").on('change',function(){
        if ($(this).is(':checked')) 
			$("input[data-l1key='functionality::cron5::enable']").prop("checked", false)
    });


	
</script>