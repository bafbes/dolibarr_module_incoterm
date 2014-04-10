<?php
class ActionsIncoterm
{ 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
     
    function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $langs, $db, $conf, $user;
		/*echo '<pre>';
		print_r($object);
		echo '</pre>';*/
		
        if($action == "validmodincoterm"){
        	//print_r($object);exit;
			if(isset($_REQUEST['incoterms']) && !empty($_REQUEST['incoterms'])){
				$db->query('UPDATE '.MAIN_DB_PREFIX.$object->table_element.' SET fk_incoterms = '.$_REQUEST['incoterms'].', location_incoterms = \''.$_REQUEST['location_incoterms'].'\' WHERE rowid = '.$object->id);
			}
		}
		elseif($action == "builddoc"){
			
			if(!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR',true);
			require_once('../custom/incoterm/config.php');
			require_once('../custom/incoterm/class/incoterm.class.php');

			TIncoterm::doActionsIncoterm($parameters, $object, $action, $hookmanager);
		}
	
        return 0;
    }
    
    function formObjectOptions ($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $conf;
		/*echo '<pre>';
		print_r($object);
		echo '</pre>';exit;*/
		
    	
		/*
		 * INCOTERMS 
		 */	
		if(in_array('propalcard',explode(':',$parameters['context'])) 
				|| in_array('ordercard',explode(':',$parameters['context'])) 
				|| in_array('invoicecard',explode(':',$parameters['context'])) 
				|| in_array('expeditioncard',explode(':',$parameters['context']))
				|| in_array('receptioncard',explode(':',$parameters['context']))
				|| in_array('thirdpartycard',explode(':',$parameters['context']))){
				
			/*
			 * INCOTERMS
			 */	
				if($action == "create"){
					
					//pre($_REQUEST,true);
					
					$sql = "SELECT fk_incoterms, location_incoterms FROM ".MAIN_DB_PREFIX."societe WHERE rowid = ".$_REQUEST['socid'];
					
					if(in_array('expeditioncard',explode(':',$parameters['context']))){
						$sql = "SELECT s.fk_incoterms 
								FROM ".MAIN_DB_PREFIX."societe as s
									LEFT JOIN ".MAIN_DB_PREFIX."commande as c ON (c.fk_soc = s.rowid)
								WHERE c.rowid = ".$_REQUEST['origin_id'];
					}
					if(isset($_REQUEST['origin']) && isset($_REQUEST['originid'])){
						$sql = "SELECT fk_incoterms, location_incoterms FROM ".MAIN_DB_PREFIX.$_REQUEST['origin']." WHERE rowid = ".$_REQUEST['originid'];
					}
					
					$resql = $db->query($sql);
										
					if($resql){
						$res = $db->fetch_object($resql);
						$id_incoterm = $res->fk_incoterms;
						$location_incoterms = $res->location_incoterms;
					}
					else 
						$id_incoterm = "";
					
					$sql = "SELECT rowid, code FROM ".MAIN_DB_PREFIX."c_incoterms ORDER BY rowid ASC";
					$resql = $db->query($sql);

					print '<tr><td>Incoterms</td>';
					print '<td colspan="2">';
					print '<select name="incoterms" class="flat" id="incoterms_id">';
					print '<option value="">&nbsp;</option>';

					while ($res = $db->fetch_object($resql)) {
						if($res->rowid == $id_incoterm){
							print '<option selected="selected" value="'.$res->rowid.'">'.$res->code.'</option>';
						}	
						else{
							print '<option value="'.$res->rowid.'">'.$res->code.'</option>';
						}
					}
					
					print '</select>';
					print '<input type="text" name="location_incoterms" value="'.$location_incoterms.'" />';
					print '</td></tr>';
				}
				elseif($action == "modincoterm"){
					
					$sql = "SELECT fk_incoterms, location_incoterms FROM ".MAIN_DB_PREFIX.$object->table_element." WHERE rowid = ".$object->id;
					$resql = $db->query($sql);

					if($resql){
						$res = $db->fetch_object($resql);
						$id_incoterm = $res->fk_incoterms;
						$location_incoterms = $res->location_incoterms;
					}
					else 
						$id_incoterm = "";

					$sql = "SELECT rowid, code FROM ".MAIN_DB_PREFIX."c_incoterms ORDER BY rowid ASC";
					$resql = $db->query($sql);
					$id_field = (in_array('thirdpartycard',explode(':',$parameters['context'])))? "socid" : "id";
					print '<tr><td>Incoterms</td>';
					print '<td colspan="2">';
					print '<form action="'.$_SERVER["PHP_SELF"].'?'.$id_field.'='.$object->id.'" method="post">';
					print '<input type="hidden" name="action" value="validmodincoterm" />';
					print '<select name="incoterms" class="flat" id="incoterms_id">';
					print '<option value="">&nbsp;</option>';

					while ($res = $db->fetch_object($resql)) {
						if($res->rowid == $id_incoterm)
							print '<option selected="selected" value="'.$res->rowid.'">'.$res->code.'</option>';
						else
							print '<option value="'.$res->rowid.'">'.$res->code.'</option>';
					}
					
					print '</select>';
					print '<input type="text" name="location_incoterms" value="'.$location_incoterms.'" />';
					print '<input class="button" type="submit" value="Modifier"></form></td></tr>';
				}
				elseif($action != "edit"){
					//pre($object, true);exit;
					$sql = "SELECT fk_incoterms, location_incoterms FROM ".MAIN_DB_PREFIX.$object->table_element." WHERE rowid = ".$object->id;

					$resql = $db->query($sql);
					if($resql){
						$res = $db->fetch_object($resql);
						$location_incoterms = $res->location_incoterms;

						$sql = "SELECT code FROM ".MAIN_DB_PREFIX."c_incoterms WHERE rowid = ".$res->fk_incoterms;
						$resql = $db->query($sql);
					}
					$id_field = (in_array('thirdpartycard',explode(':',$parameters['context'])))? "socid" : "id";
					print '<tr><td height="10"><table width="100%" class="nobordernopadding"><tbody><tr>';
					print '<td>Incoterms</td>';
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=modincoterm&'.$id_field.'='.$object->id.'">'
							.img_picto('Définir Incoterm', 'edit')
							.'</a></td>';
					PRINT '</tr></tbody></table></td>';
					print '<td colspan="3">';
					
					if($resql){
						$res = $db->fetch_object($resql);
						print $res->code.' - '.$location_incoterms;
					}
					
					print '</select></td></tr>';

				}
			
			
		}
			
        return 0;
    }

	function formAddObjectLine($parameters, &$object, &$action, $hookmanager){
		global $db,$user,$conf;
		
		
		return 0;
	}

 	function formEditProductOptions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user,$conf;
		
		
        return 0;
    }
}