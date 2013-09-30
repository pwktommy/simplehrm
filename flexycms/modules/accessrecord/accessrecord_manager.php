<?php
/*
 * Class   : employee_manager
 * Purpose : All employee related functionalities goes here
 */
class accessrecord_manager extends mod_manager {
#####################################################################################################
# Function Name : employee_manager                                                                  #
# Description   : This is a constructor						                    #
#					                                                            #
# Input         : Reference of smarty,input and output parameters                                   #
# Output	: Initiates mod manager and initialize object and business class for user manager   #
#####################################################################################################
	function accessrecord_manager (& $smarty, & $_output, & $_input) {
		if($_REQUEST['ce']!='0'){
	    		check_session();
	    	}else{
			//for autocomplete and fancybox
	    		if(!$_SESSION['id_user'] && $_REQUEST['for']=='auto'){
				//exit("<span class='fcolor'>".getmessage('COM_NO_SESSION')."</span>|abc::".getmessage('COM_NO_SESSION'));
				exit("<span class='fcolor'>".getmessage('COM_NO_SESSION')."</span>|abc::nosession");
			}elseif(!$_SESSION['id_user']){
	    			exit("nosession");
	    		}
	    	}
		$this->mod_manager($smarty, $_output, $_input,'accessrecord');
    // $this->obj_employee = new employee;
    // $this->employee_bl = new employee_bl;
 	}
#####################################################################################################
# Function Name : get_module_name (Predefined Function)                                             #
# Description   : return module name						                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No Output                                                                         #
#####################################################################################################
	function get_module_name() { 
		return 'accessrecord';
	}
#####################################################################################################
# Function Name : get_manager_name (Predefined Function)                                            #
# Description   : return manager name						                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No Output                                                                         #
#####################################################################################################
	function get_manager_name() {
		return 'accessrecord';
	}
#####################################################################################################
# Function Name : default(Predefined Function)    	                                            #
# Description   : Handle default request     					                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No Output                                                                         #
#####################################################################################################
	function _default() {
	
	}
#####################################################################################################
# Function Name : _accessrecordList                                                                     #
# Description   : Function to show employee list                 		                    #
#					                                                            #
# Input         : Takes certain conditions                                                          #
# Output	: Generates employeeList template or employeeSearch template                        #
#####################################################################################################
	function _accessrecordList(){
		$this->_output['tpl']='accessrecord/accessrecordList';
	}	
	
	
	
	
	
	
	
	

	
	function _getRecord(){
		GLOBAL $link;
		$tbl = 'site_device';
		$g_tbl = 'site_device_group';
		$map_tbl = 'site_grouping';
		$record_tbl = 'site_device_record';
		$in_tbl = 'site_device_in';
		
		$query = 'create table if not exists '.$record_tbl.' (record_date varchar(10) not null, content text not null) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
		$result = $link->query($query);
		
		$query = 'create table if not exists '.$in_tbl.' (in_date varchar(10) not null, content text not null) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
		$result = $link->query($query);
		include('manage.php');
								
		function cmpASC($a, $b){
			return strcmp($a['date'],$b['date']);
		}
		function cmpDESC($a, $b){
			return -1*strcmp($a['date'],$b['date']);
		}
		
		$url_arr = array();
		$inout_arr = array();
		
		$query = 'select * from '.$tbl.'';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_assoc($result)){
				$ip = $row['ip'];
				$port = $row['port'];
				if(!$port) $port = '80';
				array_push($url_arr, array('url'=>$ip.':'.$port, 'name'=>$row['name'], 'id'=>$row['id']));
				$inout_arr[$row['id']] = $row['id_g'];
			}
		}
			
		$result = false;
		if(sizeof($url_arr)>0){
				
			$user_arr = array();
			$listFieldArr = array('Card No', 'Photo', 'Staff ID', 'Staff Name', 'Date Time', 'Device Name');
			$result_arr = array();
			$user_in_arr = array();
			
			$u_query = 'select * from hrm__employee';
			$u_result = $link->query($u_query);
			if($u_result){
				while($u_row = mysqli_fetch_assoc($u_result)){
					$is_add = false;
					if($keywords){
						$name = $u_row['lastname'].' '.$u_row['firstname'];
						if(!(stripos($name, $keywords)===false) || !(stripos($u_row['id_employee'], $keywords)===false)){
							$is_add = true;
						}
					}else{
						$is_add = true;
					}
					
					if($is_add){
						$photo_url = '';
						if($u_row['avatar']){
							$photo_url = LBL_SITE_URL.'image/thumb4_search/avatar/'.$u_row['id_employee'].'_'.$u_row['avatar'];
						}else{
							$photo_url = LBL_SITE_URL.'templates/css_theme/img/avatar/search/hrm_'.($u_row['gender']=='M'?'male':'female').'.jpg';
						}
						$user_arr[$u_row['id_employee'].''] = array('card' => $u_row['card'], 'name' => ($u_row['lastname']?$u_row['lastname'].' ':'').$u_row['firstname'], 'photo' => $photo_url);
					}
				}
			}
			
			foreach($url_arr as $url_a){
				$data_arr = getAttendanceData(null,null,$url_a['url']);
				foreach($data_arr as $id => $arr){
					if(isset($user_arr[$id])){
						foreach($arr as $a){
							if(!isset($result_arr[$a[0]])) $result_arr[$a[0]] = array();
								array_push($result_arr[$a[0]], array(
									'id' => $id,
									'date' => $a[0].' '.$a[1],
									'device_name' => $url_a['name'],
									'd_id' => $url_a['id']
								));
						}
					}
				}
			}
			
			$result = false;
			if(sizeof($result_arr)>0){
				foreach($result_arr as $date => $data_arr){
					usort($data_arr, "cmpDESC");
					for($i=sizeof($data_arr)-1;$i>=0;$i--){
						$id = $data_arr[$i]['id'];
						$d_id = $data_arr[$i]['d_id'];
						if($inout_arr[$d_id]==1){
							$data_arr[$i]['in'] = 1;
						}else if($inout_arr[$d_id]==2){
							$data_arr[$i]['in'] = 2;
						}else{
						
							if(!isset($user_in_arr[$date][$id])){ 
								$user_in_arr[$date][$id] = 1;
							}else{
								$user_in_arr[$date][$id] = $user_in_arr[$date][$id]+1;
							}
							$data_arr[$i]['in'] = 0;
						}
					}
					$result_arr[$date] = $data_arr;
				}
				
				function validateValue($str){
					$str = str_replace('|','',$str);
					$str = str_replace(';','',$str);
					return $str;
				}
				
				
				//'Card No', 'Staff ID', 'Staff Name', 'IN/OUT', 'Date Time', 'Device Name'
				foreach($result_arr as $date => $data_arr){
					$data_str = '';
					$old_data_str = '';
					
					$record_arr = array();
					$has_record = false;
					$query = 'select * from '.$record_tbl.' where record_date="'.$date.'" ';
					if($result = $link->query($query)){
						if($row = mysqli_fetch_Array($result)){
							$old_data_str = $row['content'];
							$has_record = true;
						}
					}
					
					foreach($data_arr as $arr){
						if($data_str) $data_str .=';';
						$data_str .= $user_arr[$arr['id']]['card'].'|'.$arr['id'].'|'.validateValue($user_arr[$arr['id']]['name']).'|'.($arr['in']==1?'IN':($arr['in']==2?'OUT':'')).'|'.$arr['date'].'|'.$arr['device_name'].'|'.$arr['d_id'];
					}
					if($old_data_str){
						if($data_str) $data_str .=';';
						$data_str .= $old_data_str;
					}
					
					if(!$has_record){
						$query = 'insert into '.$record_tbl.' (record_date, content) values ("'.$date.'", "'.$data_str.'")';
						$result = $link->query($query);
					}else{
						$query = 'update '.$record_tbl.' set content="'.$data_str.'" where record_date="'.$date.'"';
						$result = $link->query($query);
					}
				}
				
				
				foreach($user_in_arr as $date => $id_arr){
					$str = '';
					$old_in_arr = array();
					$has_old_in = false;
					$query = 'select * from '.$in_tbl.' where in_date="'.$date.'" ';
					$result = $link->query($query);
					if($result){
						if($row = mysqli_fetch_assoc($result)){
							$in_str = explode(';',$row['content']);
							foreach($in_str as $c_str){
								$in_str_arr = explode('|',$c_str);
								if(sizeof($in_str_arr)==2){
									$old_in_arr[$in_str_arr[0]] = $in_str_arr[1];
								}
							}
							$has_old_in = true;
						}
					}
					foreach($id_arr as $id => $count){
						if($str) $str .= ';';
						$str .= $id.'|'.($count*1+(isset($old_in_arr[$id])?$old_in_arr[$id]*1:0));
					}
					
					
					if(!$has_old_in){
						$query = 'insert into '.$in_tbl.' (in_date, content) values ("'.$date.'", "'.$str.'")';
						$result = $link->query($query);
					}else{
						$query = 'update '.$in_tbl.' set content="'.$str.'" where in_date="'.$date.'"';
						$result = $link->query($query);
					}
				}
				
				foreach($url_arr as $url_a){
					clearRecord($url_a['url']);
				}
			}else{
				$msg = 'no_record';
			}
		}else{
			$msg = 'no_device';
		}
		
		
		?><div class="sml_box">
			<div class="top"></div>
			<div class="mdl">
				<div class="cont_hdr1 fltlft">
					<div class="cont_hdr_lft fltlft"></div>
					<div class="cont_hdr_mdl1 fltlft">
						<div class="fltlft">
							Get Access Record
						</div>
						<div class="clear"></div>
					</div>
					<div class="cont_hdr_rht fltlft">
					</div>
				</div>
				<div class="clrbth"></div>
				<div style="margin-left:20px">
					<div class="txtbg_top_sml fltlft"></div>
					<div class="txtbg_mdl_sml fltlft" style="font-size:20px;font-weight:bold;line-height:50px;"><?php
						if($result){
							echo 'Success';
						}else if($msg=='no_record'){
							echo 'No Record';
						}else if($msg=='no_device'){
							echo '<span style="color:red">Fail: No Device</span>';
						}else{
							echo '<span style="color:red">Fail</span>';
							
						}
					?></div>
					<div class="txtbg_mdl_sml fltlft">
						<input type="button" class="next login_btn" value="Back" onclick="window.location='<?php echo LBL_SITE_URL?>index.php/accessrecord/accessRecord'"/>
					</div>
					<div class="txtbg_btm_sml fltlft"></div>
					<div class="clrbth"></div>
				</div>
			</div>
				<div class="btm"></div>
		</div><?php
	}



	function _accessRecord(){
		GLOBAL $link;
		$tbl = 'site_device';
		$g_tbl = 'site_device_group';
		$map_tbl = 'site_grouping';
		$record_tbl = 'site_device_record';
		$in_tbl = 'site_device_in';
		include('manage.php');
		$group_id = $this->_input['group_id'];
		if(!$group_id) $group_id = -1;
		$keywords = $this->_input['keywords'];
		$date_from = $this->_input['date_from'];
		$date_to = $this->_input['date_to'];
		
		$is_export = $this->_input['is_export'];
		$is_search = $this->_input['is_search'];
		$search_group_id = $this->_input['search_group_id'];
		$search_keywords = $this->_input['search_keywords'];
								
		function cmpASC($a, $b){
			return strcmp($a['date'],$b['date']);
		}
		function cmpDESC($a, $b){
			return -1*strcmp($a['date'],$b['date']);
		}
		
		$result = false;
		if($is_export && $search_group_id){
			$url_arr = array();
			$inout_arr = array();
			require_once('./Classes/PHPExcel/IOFactory.php');
			$did_list = '';
			
			$query = 'select * from '.$g_tbl.' order by name asc, id asc';
			$result = $link->query($query);
			if($result){
				while($row = mysqli_fetch_array($result)){
					$id = $row['id'];
					if($group_id==$id || $group_id==-1){
						if($row['did_list']){
							if($did_list) $did_list .=',';
							$did_list .= $row['did_list'];
						}
					}
				}
			}
			
			if(!$did_list){
				$did_list_arr = array();
			}else{
				$did_list_arr = explode(',',$did_list);
			}
			if(sizeof($did_list_arr)>0 && $is_search){
				$is_valid_date = false;
				if(!$date_from && !$date_to){
					$is_valid_date = true;
				}else if(!$date_from){
					$date_from = $date_to;
					$is_valid_date = true;
				}else if(!$date_to){
					$date_to = date('Y-m-d');
					$is_valid_date = true;
				}else if($date_from<$date_to){
					$is_valid_date = true;
				}
				
				if($is_valid_date){
					
					$user_arr = array();
					$user_in_arr = array();
					$listFieldArr = array('Card No', 'Photo', 'Staff ID', 'Staff Name', 'IN/OUT', 'Date Time', 'Device Name');
					$result_arr = array();
					
					$u_query = 'select * from hrm__employee';
					$u_result = $link->query($u_query);
					if($u_result){
						while($u_row = mysqli_fetch_assoc($u_result)){
							$is_add = false;
							if($keywords){
								$name = $u_row['lastname'].' '.$u_row['firstname'];
								if(!(stripos($name, $keywords)===false) || !(stripos($u_row['id_employee'], $keywords)===false)){
									$is_add = true;
								}
							}else{
								$is_add = true;
							}
							
							if($is_add){
								$photo_url = '';
								if($u_row['avatar']){
									$photo_url = LBL_SITE_URL.'image/thumb4_search/avatar/'.$u_row['id_employee'].'_'.$u_row['avatar'];
								}else{
									$photo_url = LBL_SITE_URL.'templates/css_theme/img/avatar/search/hrm_'.($u_row['gender']=='M'?'male':'female').'.jpg';
								}
								$user_arr[$u_row['id_employee'].''] = array('card' => $u_row['card'], 'name' => $u_row['lastname'].' '.$u_row['firstname'], 'photo' => $photo_url);
							}
						}
					}
					
							$in_arr = array();		
							$query = 'select * from '.$in_tbl.' where in_date < "'.$date_from.'" ';
							$result = $link->query($query);
							if($result){
								while($row = mysqli_fetch_assoc($result)){
									$in_str = explode(';',$row['content']);
									foreach($in_str as $c_str){
										$in_str_arr = explode('|',$c_str);
										if(sizeof($in_str_arr)==2){
											if(!isset($in_arr[$in_str_arr[0]])) $in_arr[$in_str_arr[0]] = 0;
											$in_arr[$in_str_arr[0]] += $in_str_arr[1]*1;
										}
									}
								}
							}
							
					$ck_date_str = '';
					if(!$date_from && !$date_to){
						$query = 'select * from '.$record_tbl.' order by record_date desc';
					}else{
						$query = 'select * from '.$record_tbl.' where date(record_date) between "'.$date_from.'" and "'.$date_to.'" order by record_date desc';
					}
					$record_arr = array();
					
					if($result = $link->query($query)){
						while($row = mysqli_fetch_assoc($result)){
							$content_arr = explode(';',$row['content']);
							for($i=0;$i<sizeof($content_arr);$i++){
								$is_show = true;
								$c_arr = explode('|',$content_arr[$i]);
								if($keywords){
									if(stripos($c_arr[0], $keywords)===false && stripos($c_arr[1], $keywords)===false && stripos($c_arr[2], $keywords)===false) $is_show = false;
								}
								
								if($is_show){
									array_push($record_arr, array(
										'card' => $c_arr[0], 
										'id' => $c_arr[1], 
										'photo_url' => $user_arr[$c_arr[1]]['photo'], 
										'name' => $c_arr[2], 
										'inout' => $c_arr[3], 
										'date' => $c_arr[4], 
										'd_name' => $c_arr[5]));
								}
							}
						}
					}
					
					
					for($i=sizeof($record_arr)-1;$i>=0;$i--){
						if(!$record_arr[$i]['inout']){
							if(!isset($in_arr[$record_arr[$i]['id']])) $in_arr[$record_arr[$i]['id']] = 0;
							$in_arr[$record_arr[$i]['id']]++;
							$record_arr[$i]['inout'] = ($in_arr[$record_arr[$i]['id']]%2==1?'IN':'OUT');
						}
					}
					
					try{
				
						$file_path = 'search_attendance.csv';
						$listFieldArr = array('Card No', 'Staff ID', 'Staff Name', 'IN/OUT', 'Date Time', 'Device Name');

							$rowIndex = 1;
							$objPHPExcel = new PHPExcel();
							$colIndex = 0;
							foreach($listFieldArr as $fId){
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $fId);
								$colIndex++;
							}
							
							
							foreach($record_arr as $data_arr){
								$rowIndex++;
								$colIndex = 0;
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['card']);
								$colIndex++;
								
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['id']);
								$colIndex++;
								
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['name']);
								$colIndex++;
								
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['inout']);
								$colIndex++;
								
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['date']);
								$colIndex++;
								
								$chrIndex = (65+$colIndex);
								if($chrIndex>90) break;
								$objPHPExcel->getActiveSheet()->setCellValue(chr($chrIndex).$rowIndex, $data_arr['d_name']);
								$colIndex++;
							}
								
								$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
								$objWriter->setUseBOM(true);
								$objWriter->save($file_path );
						}catch(Exception $e){ }
					
						
				}else{
				}
			}
			
			
				
				
				?><div class="sml_box">
					<div class="top"></div>
					<div class="mdl">
						<div class="cont_hdr1 fltlft">
							<div class="cont_hdr_lft fltlft"></div>
							<div class="cont_hdr_mdl1 fltlft">
								<div class="fltlft">
									Access Record
								</div>
								<div class="clear"></div>
							</div>
							<div class="cont_hdr_rht fltlft">
							</div>
						</div>
						<div class="clrbth"></div>
						<div style="margin-left:20px">
							<div class="txtbg_top_sml fltlft"></div>
							<div class="txtbg_mdl_sml fltlft"><?php
							
								if($result){
								
									?><input type="button" class="next login_btn" value="Back" onclick="window.location='<?php echo LBL_SITE_URL?>index.php/accessrecord/accessRecord'"/><input type="button" value="Download" onclick="window.location='<?php echo LBL_SITE_URL.$file_path;?>'" class="login_btn"/><?php
									
								}else{
								
									?><div style="color:red">Fail</div>
									<input type="button" class="next login_btn" value="Back" onclick="window.location='<?php echo LBL_SITE_URL?>index.php/accessrecord/accessRecord'"/><?php
									
								}?>
							</div>
							<div class="txtbg_btm_sml fltlft"></div>
							<div class="clrbth"></div>
						</div>
					</div>
						<div class="btm"></div>
				</div><?php
			
			
			
			exit;
		}
		
		$url_arr = array();
		$inout_arr = array();
		
		?><div class="sml_box">
			<div class="top"></div>
			<div class="mdl">
				<div class="cont_hdr1 fltlft">
					<div class="cont_hdr_lft fltlft"></div>
					<div class="cont_hdr_mdl1 fltlft">
						<div class="fltlft">
							Access Record
						</div>
						<div class="fltrht">
                            <input type="button" value="Export CSV" onclick="exportCSV()" class="fltrht" />
                            <input type="button" value="Get Record" onclick="window.location='<?php echo LBL_SITE_URL?>index.php/accessrecord/getRecord'" class="fltrht" />
                        </div>
						<div class="clear"></div>
					</div>
					<div class="cont_hdr_rht fltlft">
					</div>
				</div>
				<div class="clrbth"></div>
				<div style="margin-left:20px">
						<div class="txtbg_top_sml fltlft"></div>
						<div class="txtbg_mdl_sml fltlft">
						<form id="submit_form" action="" method="post">
							<table class="listing_tbl">
								<tr>
									<td width="100">Device Group: </td>
									<td width="200"><select id="group_id" name="group_id" class="">
										<option value="-1" <?php echo ($group_id==-1?'selected="selected"':'')?>>All</option><?php
										
										$did_list = '';
										$query = 'select * from '.$g_tbl.' order by name asc, id asc';
										$result = $link->query($query);
										if($result){
											while($row = mysqli_fetch_array($result)){
												$id = $row['id'];
												$selected = ($group_id==$id?'selected="selected"':'');
												echo '<option value="'.$id.'" '.$selected.'>'.$row['name'].'</option>';
												
												if($group_id==$id || $group_id==-1){
													if($row['did_list']){
														if($did_list) $did_list .=',';
														$did_list .= $row['did_list'];
													}
												}
											}
										}
										
									?></select></td>
									<td width="80">Keywords: </td>
									<td width="200"><input type="text" name="keywords" id="keywords" value="<?php echo $keywords?>" placeholder="Card / Staff ID / Staff Name" /></td>
								</tr>
							</table>
							<table align="left" cellpadding="0" cellspacing="0" border="0" class="listing_tbl" width="100%">
								<tr>
									<td valign="middle" style="vertical-align:middle">Date&nbsp;</td>
									<td valign="middle" width="40" style="vertical-align:middle">from: </td>
									<td valign="middle" style="vertical-align:middle"><input type="text" id="date_from" name="date_from" value="<?php echo $date_from?>"/></td>
									<td valign="middle" style="vertical-align:middle">&nbsp;</td>
									<td valign="middle" width="30" style="vertical-align:middle">to: </td>
									<td valign="middle" style="vertical-align:middle"><input type="text" id="date_to" name="date_to" value="<?php echo $date_to?>" /></td>
								</tr>
							</table>
							<table class="listing_tbl" width="100%">
								<tr>
									<td style="padding-right:24px;"><input type="button" name="search" id="search"  value="Search" onclick="searchAccessRecord();" class="login_btn" style="float:right;"/></td>
								</tr>
							</table><?php
							if(!$did_list){
								$did_list_arr = array();
							}else{
								$did_list_arr = explode(',',$did_list);
							}
							if(sizeof($did_list_arr)>0 && $is_search){
								$is_valid_date = false;
								if(!$date_from && !$date_to){
									$is_valid_date = true;
								}else if(!$date_from){
									$date_from = $date_to;
									$is_valid_date = true;
								}else if(!$date_to){
									$date_to = date('Y-m-d');
									$is_valid_date = true;
								}else if($date_from<$date_to){
									$is_valid_date = true;
								}
								
								if($is_valid_date){
									
									$user_arr = array();
									$listFieldArr = array('Card No', 'Photo', 'Staff ID', 'Staff Name', 'IN/OUT', 'Date Time', 'Device Name');
									$result_arr = array();
									
									$u_query = 'select * from hrm__employee';
									if($keywords) $u_query = 'select * from hrm__employee where id_employee like "%'.$keywords.'%" or concat(lastname, firstname) like "%'.$keywords.'%" or card like "%'.$keywords.'%"';
									//echo $u_query.'<br>';
									$u_result = $link->query($u_query);
									if($u_result){
										while($u_row = mysqli_fetch_assoc($u_result)){
											$photo_url = '';
											if($u_row['avatar']){
												$photo_url = LBL_SITE_URL.'image/thumb4_search/avatar/'.$u_row['id_employee'].'_'.$u_row['avatar'];
											}else{
												$photo_url = LBL_SITE_URL.'templates/css_theme/img/avatar/search/hrm_'.($u_row['gender']=='M'?'male':'female').'.jpg';
											}
											$user_arr[$u_row['id_employee'].''] = array('card' => $u_row['card'], 'name' => $u_row['lastname'].' '.$u_row['firstname'], 'photo' => $photo_url);
										}
									}
									
							$in_arr = array();		
							$query = 'select * from '.$in_tbl.' where in_date < "'.$date_from.'" ';
							$result = $link->query($query);
							if($result){
								while($row = mysqli_fetch_assoc($result)){
									$in_str = explode(';',$row['content']);
									foreach($in_str as $c_str){
										$in_str_arr = explode('|',$c_str);
										if(sizeof($in_str_arr)==2){
											if(!isset($in_arr[$in_str_arr[0]])) $in_arr[$in_str_arr[0]] = 0;
											$in_arr[$in_str_arr[0]] += $in_str_arr[1]*1;
										}
									}
								}
							}
									
									$ck_date_str = '';
									if(!$date_from && !$date_to){
										$query = 'select * from '.$record_tbl.' order by record_date desc';
									}else{
										$query = 'select * from '.$record_tbl.' where date(record_date) between "'.$date_from.'" and "'.$date_to.'" order by record_date desc';
									}
									if($result = $link->query($query)){
										?><table align="left" cellpadding="0" cellspacing="0" border="0" align="center"  class="tbl_listing" style="margin-bottom:25px;margin-left:10px;">
										<tr height="40"><?php
										
											foreach($listFieldArr as $fId){
												echo '<th class="email ">'.$fId.'</th>';
											}
										
										?></tr><?php
										
										$record_arr = array();
										while($row = mysqli_fetch_assoc($result)){
											$content_arr = explode(';',$row['content']);
											for($i=0;$i<sizeof($content_arr);$i++){
												$is_show = true;
												$c_arr = explode('|',$content_arr[$i]);
												if(in_array($c_arr[6], $did_list_arr)){
													if($keywords){
														if(stripos($c_arr[0], $keywords)===false && stripos($c_arr[1], $keywords)===false && stripos($c_arr[2], $keywords)===false) $is_show = false;
													}
													
													if($is_show){
														array_push($record_arr, array(
															'card' => $c_arr[0], 
															'id' => $c_arr[1], 
															'photo_url' => $user_arr[$c_arr[1]]['photo'], 
															'name' => $c_arr[2], 
															'inout' => $c_arr[3], 
															'date' => $c_arr[4], 
															'd_name' => $c_arr[5]));
													}
												}
											}
										}
										
										for($i=sizeof($record_arr)-1;$i>=0;$i--){
											if(!$record_arr[$i]['inout']){
												if(!isset($in_arr[$record_arr[$i]['id']])) $in_arr[$record_arr[$i]['id']] = 0;
												$in_arr[$record_arr[$i]['id']]++;
												$record_arr[$i]['inout'] = ($in_arr[$record_arr[$i]['id']]%2==1?'IN':'OUT');
											}
										}
										
										$count = 0;
										foreach($record_arr as $data_arr){
											echo '<tr height="40" '.($count%2==1?'class="even"':'').'>
													<td align="left" valign="middle">'.$data_arr['card'].'</td>
													<td align="left" valign="middle" style="cursor:pointer;text-decoration:underline;" onclick="window.location=\''.LBL_SITE_URL.'index.php/employee/employeeDetail/id-'.$data_arr['id'].'\'"><img src="'.$data_arr['photo_url'].'" border="0" /></td>
													<td align="left" valign="middle">'.$data_arr['id'].'</td>
													<td align="left" valign="middle" style="cursor:pointer;text-decoration:underline;" onclick="window.location=\''.LBL_SITE_URL.'index.php/employee/employeeDetail/id-'.$data_arr['id'].'\'"><b>'.$data_arr['name'].'</b></td>
													<td align="left" valign="middle" width="50">'.$data_arr['inout'].'</td>
													<td align="left" valign="middle" width="150">'.$data_arr['date'].'</td>
													<td align="left" valign="middle">'.$data_arr['d_name'].'</td>
												</tr>';
											$count++;
										}
										
										?></table><?php
									}
									
										
								}else{
									?><span style="color:red">Invalid date range</span><?php
								}
							}
							
							?><input type="hidden" id="search_group_id" name="search_group_id" value="<?php echo $group_id?>"/><input type="hidden" name="search_keywords" value="<?php echo $keywords?>"/><input type="hidden" id="search_date_from" name="search_date_from" value="<?php echo $date_from?>"/><input type="hidden" id="search_date_to" name="search_date_to" value="<?php echo $date_to?>"/><input type="hidden" id="is_export" name="is_export" value=""/><input type="hidden" id="is_search" name="is_search" value="1"/></form></div>
						<div class="txtbg_btm_sml fltlft"></div>
						<div class="clrbth"></div>
				</div>
			</div>
			<div class="btm"></div>
		</div><script type="text/javascript">
		function searchAccessRecord(){
			//if($('#date_from').val()*1>=0 && $('#date_to').val()*1>=0){
				var f = document.getElementById('submit_form');
				f.submit();
			//}else{
			//	alert('Invalid searching date format');
			//}
		}
		function exportCSV(){
			if(!$('#group_id').val()){
				alert('Please select a Device Group');
				return 0;
			}
			$('#is_export').val(1);
			$('#search_group_id').val($('#group_id').val())
			var f = document.getElementById('submit_form');
			f.submit();
		}
		$(document).ready(function(){
			$('#date_from').datepicker({dateFormat:'yy-mm-dd',changeMonth:true,changeYear:true,yearRange:"-100:+0"});
			$('#date_to').datepicker({dateFormat:'yy-mm-dd',changeMonth:true,changeYear:true,yearRange:"-100:+0"});
		});
		</script><?php
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	//face
	function _faceAttendance(){
		GLOBAL $link;
		$a_tbl = 'api_attendance_tbl';
		
		$query = 'create table if not exists '.$a_tbl.' (pkey int(11) not null, user_id int(11) not null, post_date datetime not null, area_code char(50) not null, photo_id char(50) not null, location varchar(100) not null, photo_num char(50) not null, is_reg tinyint(1) not null, mac_addr char(50) not null, staff_name varchar(100) not null, ip char(50) not null, gp char(50) not null ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
		$result = $link->query($query);
		
		
		?><style>
		.sml_box .top	{
			background:transparent;
		}
		.sml_box .mdl	{
			background:transparent;
			border:1px solid #cccccc;
			background-color:white;
			width:1000px;
		}
		.sml_box .title_bg	{
			height:36px;
			line-height:30px;
			width:960px;
			border:1px solid #9bc7d5;
			background-color:#d2edf7;
		}
		.sml_box .cont_hdr_mdl1	{
			background:transparent;
		}
		.sml_box .txtbg_top_sml	{
			background:transparent;
		}
		.sml_box .txtbg_mdl_sml	{
			background:transparent;
			border:1px solid #dddddd;
			background-color:#f9f9f9;
			width:980px;
		}
		.sml_box .txtbg_btm_sml	{
			background:transparent;
		}
		.sml_box .btm	{
			background:transparent;
		}
		</style><div class="sml_box">
			<div class="top"></div>
			<div class="mdl">
				<div class="cont_hdr1 fltlft">
					<div class="title_bg">
						<div class="cont_hdr_mdl1" style="float:left;">
							<div class="fltlft">
								Face Attendance
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="clrbth"></div>
				<div style="margin-left:10px;">
						<div class="txtbg_top_sml fltlft"></div>
						<div class="txtbg_mdl_sml fltlft">
						<form id="submit_form" action="" method="post"><?php
									$listFieldArr = array( 'User ID', 'Date', 'Area Code', 'Photo ID', 'Location', 'Photo No.', 'MAC Addr.', 'Staff Name', 'IP', 'Roaster Group');
									
									$query = 'select * from '.$a_tbl.' order by pkey desc';
									if($result = $link->query($query)){
										?><script type="text/javascript" src="jquery.extraDim.js"></script><script type="text/javascript">
										function onDown_listTable(id){
											var url = 'api.php?pkey='+id;
											$.getJSON(url, function(data){
												var html = '';
												var count = 0;
												$.each(data, function(key, val) {
													html += '<table border="0" cellpadding="5" cellspacing="0" style="float:left"><tr><td width="200"><img src="../'+val+'" width="200"/></td></tr></table>';
													count++;
												});
												if(count>0){
													var h = 160*Math.ceil(count/3);
													if(h>640) h = 640;
													html = '<div style="width:655px;height:'+h+'px;overflow-y:scroll;border:10px solid #666666;background-color:#ffffff">'+html+'</div>';
													$.showExtraDim(html, true);
												}
											});
										}
										</script><table align="left" cellpadding="0" cellspacing="0" border="0" align="center"  class="tbl_listing" style="margin-bottom:25px;margin-left:10px;">
										<tr height="40"><?php
										
											foreach($listFieldArr as $fId){
												echo '<th class="email ">'.$fId.'</th>';
											}
										
										?></tr><?php
										
										$count = 0;
										while($row = mysqli_fetch_array($result)){
											echo '<tr height="40" '.($count%2==1?'class="even"':'').' onclick="onDown_listTable('.$row['pkey'].')" style="cursor:pointer">
													<td align="left" valign="middle">'.($row['user_id']?$row['user_id']:'-').'</td>
													<td align="left" valign="middle">'.($row['post_date']?$row['post_date']:'-').'</td>
													<td align="left" valign="middle">'.($row['area_code']?$row['area_code']:'-').'</td>
													<td align="left" valign="middle">'.($row['photo_id']?$row['photo_id']:'-').'</td>
													<td align="left" valign="middle">'.($row['location']?$row['location']:'-').'</td>
													<td align="left" valign="middle">'.($row['photo_num']?$row['photo_num']:'-').'</td>
													<td align="left" valign="middle">'.($row['mac_addr']?$row['mac_addr']:'-').'</td>
													<td align="left" valign="middle">'.($row['staff_name']?$row['staff_name']:'-').'</td>
													<td align="left" valign="middle">'.($row['ip']?$row['ip']:'-').'</td>
													<td align="left" valign="middle">'.($row['gp']?$row['gp']:'-').'</td>
												</tr>';
											$count++;
										}
										
										?></table><?php
									}
							
							?><input type="hidden" id="del_id" name="del_id" value="" /><input type="hidden" id="detail_id" name="detail_id" value="" /></form></div>
						<div class="txtbg_btm_sml fltlft"></div>
						<div class="clrbth"></div>
				</div>
			</div>
			<div class="btm"></div>
		</div><script type="text/javascript">
		</script><?php
	}
	
}
