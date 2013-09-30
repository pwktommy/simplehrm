<?php
/*
 * Class   : employee_manager
 * Purpose : All employee related functionalities goes here
 */
class employee_manager extends mod_manager {
#####################################################################################################
# Function Name : employee_manager                                                                  #
# Description   : This is a constructor						                    #
#					                                                            #
# Input         : Reference of smarty,input and output parameters                                   #
# Output	: Initiates mod manager and initialize object and business class for user manager   #
#####################################################################################################
	function employee_manager (& $smarty, & $_output, & $_input) {
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
		$this->mod_manager($smarty, $_output, $_input,'employee');
		$this->obj_employee = new employee;
		$this->employee_bl = new employee_bl;
 	}
#####################################################################################################
# Function Name : get_module_name (Predefined Function)                                             #
# Description   : return module name						                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No Output                                                                         #
#####################################################################################################
	function get_module_name() { 
		return 'employee';
	}
#####################################################################################################
# Function Name : get_manager_name (Predefined Function)                                            #
# Description   : return manager name						                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No Output                                                                         #
#####################################################################################################
	function get_manager_name() {
		return 'employee';
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
# Function Name : manager_error_handler                                                             #
# Description   : Function to handle error when choice not found		                    #
#					                                                            #
# Input         : No input                                                                          #
# Output	: Error handling template.                                                          #
#####################################################################################################
	function manager_error_handler() {
		$call = "_".$this->_input['choice'];
		if (function_exists($call)) {
			$call($this);
		} else {
			//Put your own error handling code here
			$this->_output['tpl'] ='default/error_handler';
		}
	}
#####################################################################################################
# Function Name : _welcomeEmployee                                                                  #
# Description   : Function to display Employee Welcome Message        		                    #
#					                                                            #
# Input         : No input                                                                          #
# Output	: Template for Employee Welcome Message                                             #
#####################################################################################################
	function _welcomeEmployee() {
		$_SESSION['wel_flag_emp'] = 0;
		$this->_output['tpl']='static/welcomeEmployee';
	}
#####################################################################################################
# Function Name : createRandomPassword                                                              #
# Description   : Function to create a random password          		                    #
#					                                                            #
# Input         : No input                                                                          #
# Output	: Generates a password of 8 alphanumerics                                           #
#####################################################################################################
	function createRandomPassword() {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		$i = 0;
		$pass = '' ;
		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}
#####################################################################################################
# Function Name : _employeeList                                                                     #
# Description   : Function to show employee list                 		                    #
#					                                                            #
# Input         : Takes certain conditions                                                          #
# Output	: Generates employeeList template or employeeSearch template                        #
#####################################################################################################
	function _employeeList(){

		$_SESSION['amsg']=$this->_input['msg'];
		$_SESSION['empstatus']='';
		if($this->_input['f']=='for_del'){
			$_SESSION['amsg']=$this->deleteEmployee($this->_input['keys']);
		}
		if($this->_input['qs']!=''){
			$this->_input['qstart']=$this->_input['qs'];
		}
		if(!$this->_input['chk']){
			$_SESSION['auto']='';
		}
		$_REQUEST['choice']='employeeList';
		$uri="index.php/page-employee-choice-employeeList";
		$cond="1 ";
		if($this->_input['emp_status']!='' || $_SESSION['auto']==1){
			$cond.=" AND terminate_date!='' AND terminate_date <= NOW() ";
			$uri.="-emp_status-3";
			$_SESSION['auto']=1;
		}else{
			$cond.=" AND IF(terminate_date!='',terminate_date > NOW(),1) ";
			$_SESSION['auto']=2;
		}
		if($this->_input['employee_name']!=''){
			$cond.=" AND CONCAT(firstname,' ',if(middlename!='NULL',CONCAT(middlename,' '),''),if(lastname!='NULL',lastname,'')) LIKE '%".$this->_input['employee_name']."%' ";
			$uri.="-employee_name-".$this->_input['employee_name'];
		}
		$sql=$this->employee_bl->getEmployeeListSql($cond);		
    // print $sql;
		$this->_output['sql'] = $sql;
		$this->_output['limit']= $GLOBALS['conf']['PAGINATE']['rec_per_page'];
		$this->_output['ajax']= "employeeList";		
		$this->_output['uri'] = $uri;
		$this->_output['type'] = "box";
		$this->_output['pg_header'] = "Employee List";
		$this->_output['sort_order'] = "DESC";
		$this->_output['sort_by'] = "id_employee";
		$this->_output['show']=$GLOBALS['conf']['PAGINATE']['show_page'];	
		$this->_output['field'] = array("name"=>array("Name",1),"job_title_name"=>array("Title",1),"joined_date"=>array("Release Date",1),"ad_account_id"=>array("Ad Account",1),"mobile_phone"=>array("Mobile",1),"emp_status"=>array("Status",0));
				
		$this->_output['res_terminate']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'employeeTerminationContract","terminate_date < NOW()")',"id_employee","id_employee",$err);

		if($this->_input['chk']){
			$this->employee_bl->page_listing($this,'employee/employeeList');
		}else{
			if($this->_input['Tchk']==''){
				$_SESSION['auto']='';
			}
			$this->employee_bl->page_listing($this,'employee/employeeSearch');
		}
	}	

#####################################################################################################
# Function Name : _addEmployee                                                                      #
# Description   : Function to give add employee interface              		                    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: Generates addEmployee template                                                    #
#####################################################################################################
	function _addEmployee(){
		GLOBAL $link;
		$g_arr = array();
		$tbl = 'site_device_group';
		$query = 'select * from '.$tbl.' ';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				$g_arr[$row['id']] = $row['name'];
			}
		}
		$this->_output['dg'] = $g_arr;
		
		$t_arr = array();
		$t_tbl = 'site_device_timezone';
		$query = 'select * from '.$t_tbl.' where type="0" or type="4" order by id asc';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				$t_arr[$row['id']] = $row['name'];
			}
		}
		$this->_output['tz'] = $t_arr;
		
		$t1_arr = array();
		$query = 'select * from '.$t_tbl.' where type="1" order by id asc';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				$t1_arr[$row['id']] = $row['name'];
			}
		}
		$this->_output['tz1'] = $t1_arr;
		
		$t2_arr = array();
		$query = 'select * from '.$t_tbl.' where type="2" order by id asc';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				$t2_arr[$row['id']] = $row['name'];
			}
		}
		$this->_output['tz2'] = $t2_arr;
		
		$this->_output['country']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'country","1")',"id_country","country_name",$err);
		
		$this->_output['division']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyDivision","1")',"id_division","div_name",$err);
		$empsts=$GLOBALS['conf']['EMPLOYMENT_STATUS'];
		unset($empsts[2]);
		$this->_output['flg']=$this->_input['flg'];
		$this->_output['emp_sts']=$empsts;
		$this->_output['tpl'] = "employee/addEmployee";
	}
    
#####################################################################################################
# Function Name : _showStateCity                                                                    #
# Description   : shows state or city dropdown list              		                    #
#					                                                            #
# Input         : flag to differentiate state or city                                               #
# Output	: Generates showStateCity template                                                  #
#####################################################################################################	
	function _showStateCity(){
		global $link;
		$nm=$this->_input['flg'];
		$id= $nm=="state" ? "id_country":"id_state";
		if(is_numeric($this->_input['id'])){
			$sql='CALL get_search_sql("'.TABLE_PREFIX.$nm.'","'.$id."='".$this->_input['id']."' ORDER BY ".$nm."_name ASC".'")';
		}else{
			$tmp_nm= $nm=="state" ? "country":"state";
			$tmp_res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.$tmp_nm.'","'.$tmp_nm."_name='".$this->_input['id']."' LIMIT 1".'")');
			$sql='CALL get_search_sql("'.TABLE_PREFIX.$nm.'","'.$id."='".$tmp_res[$id]."' ORDER BY ".$nm."_name ASC".'")';
			
		}
		$res=getindexrows($sql,"id_".$nm,$nm."_name",$err); 
		$this->_output['subcat_list']=$res;
		$this->_output['tpl']='employee/showSubcat';
	}
#####################################################################################################
# Function Name : _showDepartmentTeamJob                                                            #
# Description   : shows department or team or jobtitle dropdown list              		    #
#					                                                            #
# Input         : flag to differentiate department or team or jobtitle                              #
# Output	: Generates showSubcat template                                                     #
#####################################################################################################	
	function _showDepartmentTeamJob(){
		$nm=$this->_input['flg'];
		if($nm=="department" || $nm=="dpartment"){
			$sql='CALL get_search_sql("'.TABLE_PREFIX.'companyDepartment","'."id_division='".$this->_input['id']."' ORDER BY dept_name ASC".'")';
			$res=getindexrows($sql,"id_department","dept_name",$err); 
		}elseif($nm=="team" || $nm=="tm"){
			$sql='CALL get_search_sql("'.TABLE_PREFIX.'companyTeam","'."id_department='".$this->_input['id']."' ORDER BY team_name ASC".'")';
			$res=getindexrows($sql,"id_team","team_name",$err); 
		}elseif($nm=="job_title" || $nm=="jobtitle"){
			$sql='CALL get_search_sql("'.TABLE_PREFIX.'companyJobTitle","'."id_team='".$this->_input['id']."' ORDER BY job_title_name ASC".'")';
			$res=getindexrows($sql,"id_job_title","job_title_name",$err); 
		}

		$this->_output['subcat_list']=$res;
		$this->_output['tpl']='employee/showSubcat';
	}
#####################################################################################################
# Function Name : _insertEmployee                                                                   #
# Description   : Insert employee information                                    		    #
#					                                                            #
# Input         : Employee information                                                              #
# Output	: No output                                                                         #
#####################################################################################################	
	function _insertEmployee(){
		global $link;
		include_once('manage.php');
		$tbl = 'hrm__employee';
		$card_no = $this->_input['employee']['card'];
		$repeat_card_user_arr = array();
		$dg_id = $this->_input['dg'];
		$tg_id = $this->_input['tg'];
		$tz1 = $this->_input['tz1'];
		//$tz2 = $this->_input['tz2'];
		$tz2 = '0';
		$tz3 = $this->_input['tz3'];
		$is_update_device = $this->_input['is_update_device'];

		
		if($dg_id && $is_update_device){
			$tbl = 'site_device';
			$g_tbl = 'site_device_group';
			$did_list = '';
			$query = 'select * from '.$g_tbl.' where id="'.$dg_id.'"';
			$result = $link->query($query);
			if($result){
				if($row = mysqli_fetch_array($result)){
					$did_list = $row['did_list'];
				}
			}

			$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
			$device_ip_arr = array();
			$result = $link->query($query);
			if($result){
				while($row = mysqli_fetch_array($result)){
					if($row['ip']){
						$port = $row['port'];
						if(!$port) $port = '80';
						$fingerprint_device_ip = $row['ip'].':'.$port;
						if(!detectDeviceConection($fingerprint_device_ip)){
							print '<span style="color:red">'.getmessage('EMP_ADD_FAIL').'<br>Offline device existing in the group</span>';
							return 0;
						}
					}
				}
			}
		}

		if($card_no){
			$result = $link->query('select * from '.$tbl.' where card="'.$card_no.'" limit 0,1');
			if($result){
				if($row = mysqli_fetch_array($result)){
					$repeat_card_user_arr = $row;
				}
			}

			if(sizeof($repeat_card_user_arr)>0){
				print '<span style="color:red">'.getmessage('EMP_ADD_FAIL').': Card No. repeated: owned by '.$repeat_card_user_arr['lastname'].$repeat_card_user_arr['firstname'].'</span>';
				return 0;
			}
		}

		if($_SESSION['id_user']==$_SESSION['id_company']){
			$arr=$this->_input['employee'];
			$arr['username']=$arr['work_email'];
			if(isset($this->_input['dob']) && $this->_input['dob']!=''){
				$dob=explode("-",$this->_input['dob']);
				$arr['dob']=$dob[2]."-".$dob[1]."-".$dob[0];
			}
			if($this->_input['joined_date']!=''){
				$doj=explode("-",$this->_input['joined_date']);
				$arr['joined_date']=$doj[2]."-".$doj[1]."-".$doj[0];
			}
			$arr['id_company']=$_SESSION['id_user'];
			$arr['ip']=$_SERVER['REMOTE_ADDR'];
      $password = $this->createRandomPassword();
			$arr['password'] = md5($password);
			$confirm_code= md5(uniqid(rand(),true));
			$arr['random_num']=$confirm_code;
			$employee_id=$this->obj_employee->insert("employee",$arr,1);
			      
      print $employee_id>0?getmessage('EMP_ADD_SUCC'):getmessage('EMP_ADD_FAIL');
			// Mailing user password and username
			if($employee_id && !empty($arr['work_email'])){
				$activate_link=LBL_SITE_URL."index.php/user/checkUser/confirm-".$confirm_code;
			
				$from = $_SESSION['username'];
				$to = $arr['work_email'];
				$subject = "Account Activation";
				$info['activate_link'] = $activate_link;
				$info['firstname']=$arr['firstname'];
				$info['middlename']=$arr['middlename'];
				$info['lastname']=$arr['lastname'];
				$info['username']=$arr['username'];
				$info['password']=$password;
				$info['ad_account_id']=$arr['ad_account_id'];
				$info['ad_password']=$arr['ad_password'];
			
				$tpl= "employee/mailAccountActivate";
				$this->smarty->assign('sm',$info);
				$body = $this->smarty->fetch($this->smarty->add_theme_to_template($tpl));
				$msg=sendmail($to,$subject,$body,$from);// also u can pass  $cc,$bcc
			}
			// end
			$interval=$GLOBALS['conf']['BENEFIT']['default_validity'];
			if($employee_id && !empty($arr['work_email'])){
				$this->obj_employee->insertDefaultBenefits($employee_id,$interval);
				
				$salary['current_salary']=isset($arr['salary'])?$arr['salary']:'';
				$salary['currency']=isset($arr['currency'])?$arr['currency']:'';
				$salary['pay_frequency']=isset($arr['pay_frequency'])?$arr['pay_frequency']:'';
				$salary['id_employee']=$employee_id;
				$salary['ip']=@$_SERVER['REMOTE_ADDR'];
				$this->obj_employee->insert("employeeSalary",$salary,1);
				$this->mail4NotificationOfEvent("1",$arr['work_email']);
				print getmessage('EMP_ADD_SUCC');
			}
			
			
			/*
			** site_device_mapping
			** 
			** auto create table
			*/
			$tbl = 'site_device_mapping';
			$staff_id = $employee_id;
			if($staff_id){
				$query = "select id from ".$tbl." order by id desc limit 0,1";
				$result = $link->query($query);
				if($result){
					if($row = mysqli_fetch_array($result)){
						$lastPkey = ($row["id"]+1);
					}
				}
				if(!$lastPkey) $lastPkey = 1;
				
				$query = 'insert into '.$tbl.' (id, id_user, id_g, tz1, tz2, tz3) values ("'.$lastPkey.'", "'.$staff_id.'", "'.$dg_id.'", "'.$tz1.'", "'.$tz2.'", "'.$tz3.'")';
				$result = $link->query($query);
				
				
				/*
				** update device
				*/
				// detectDeviceConection($fingerprint_device_ip)
				$card_no = $arr['card'];
				if($card_no && $dg_id && $is_update_device){
					$tbl = 'site_device';
					$g_tbl = 'site_device_group';
					$did_list = '';
					$query = 'select * from '.$g_tbl.' where id="'.$dg_id.'"';
					$result = $link->query($query);
					if($result){
						if($row = mysqli_fetch_array($result)){
							$did_list = $row['did_list'];
						}
					}

					$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
					$device_ip_arr = array();
					$result = $link->query($query);
					if($result){
						while($row = mysqli_fetch_array($result)){
							if($row['ip']){
								$port = $row['port'];
								if(!$port) $port = '80';
								$fingerprint_device_ip = $row['ip'].':'.$port;
								array_push($device_ip_arr, $fingerprint_device_ip);
							}
						}
					}
					$has_fail = false;
					if(!$tg_id) $tg_id = '1';
					for($i=0;$i<sizeof($device_ip_arr);$i++){
						if(!addUser($staff_id, $card_no, $device_ip_arr[$i], $tg_id, $tz1, $tz2, $tz3)){
							$has_fail = true;
							break;
						}
					}
					if($has_fail){
						for($j=$i-1;$j>=0;$j--){
							deleteUserById($staff_id, $device_ip_arr[$j]);
						}
					}
				}
			}
			
		}
	}
#####################################################################################################
# Function Name : _autoCard                                                                 #
# Description   : It gives autocomplete result for card number                      		    #
#					                                                            #
# Input         : card number                                                                   #
# Output	: json data                                                       #
#####################################################################################################	
  function _autoCard(){
		if(!$_SESSION['id_user']){
			exit;
		}
		$result=array();
		$cond="";
		$sql=$this->employee_bl->autoCard($cond,$this->_input['q']);
		$data=getautorows($sql);
		foreach ($data as $list) {
		  $result[]=array('card_number'=>$list['card_num']);
		}
		echo json_encode($result);
  }

	
	
#####################################################################################################
# Function Name : _autoEmployeeName                                                                 #
# Description   : It gives autocomplete result for employee name                       		    #
#					                                                            #
# Input         : employee name                                                                     #
# Output	: Generates autoList template                                                       #
#####################################################################################################	
	function _autoEmployeeName(){
		if(!$_SESSION['id_user']){
			exit;
		}
		global $link;
		$cond="";
		if($_SESSION['auto']==1){
			$cond.=" AND terminate_date!='' AND terminate_date <= NOW() ";
		}else{
			$cond.=" AND IF(terminate_date!='',terminate_date > NOW(),1) ";
		}
		$sql=$this->employee_bl->autoEmpName($cond,$this->_input['q']);
		$data=getautorows($sql);
		$this->_output['data']=isset($data)?$data:'';
		$this->_output['flag']=2;
		$this->_output['tpl']='employee/autoList';
	}
#####################################################################################################
# Function Name : _checkEmail                                                                       #
# Description   : It checks duplicacy of employee email id.                      		    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No output                                                                         #
#####################################################################################################		
	function _checkEmail(){
		global $link;
		$sql='CALL get_search_sql("'.TABLE_PREFIX.'employee","'."work_email='".$this->_input['emp_email']."'".'")';
		$res=mysqli_num_rows($link->query($sql));
		ob_clean();
		print $res;
		exit;
	}
	
#####################################################################################################
# Function Name : _checkAccount id                                                                       #
# Description   : It checks duplicacy of employee Account id.                      		    #
#					                                                            #
# Input         : No Input                                                                          #
# Output	: No output                                                                         #
#####################################################################################################		
	function _checkAccountID(){
		global $link;
		$sql='CALL get_search_sql("'.TABLE_PREFIX.'employee","'."ad_account_id='".$this->_input['emp_email']."'".'")';
		$res=mysqli_num_rows($link->query($sql));
		ob_clean();
		print $res;
		exit;
	}
	function _checkCard(){
		global $link;
		$tbl = 'hrm__employee';
		$card_no = $this->_input['emp_email'];
		$id_emp = $this->_input['emp_id'];
		$repeat_card_user_arr = array();

		$result = $link->query('select * from '.$tbl.' where card="'.$card_no.'" '.($id_emp?'and id_employee<>"'.$id_emp.'"':'').' limit 0,1');
		if($result){
			if($row = mysqli_fetch_array($result)){
				$repeat_card_user_arr = $row;
			}
		}
		if(sizeof($repeat_card_user_arr)>0){
			print $repeat_card_user_arr['id_employee'];
		}else{

		}
		exit;
	}
#####################################################################################################
# Function Name : deleteEmployee                                                                    #
# Description   : It deletes selected employee.                                  		    #
#		                      	                                                            #
# Input         : Employee Ids                                                                      #
# Output	: return message                                                                    #
#####################################################################################################	
	function deleteEmployee($keys=''){
		if($_SESSION['id_company']){
			
			$sql="SELECT id_employee,avatar,cv,work_email,gender FROM ".TABLE_PREFIX."employee WHERE id_employee IN (".$keys.") AND id_company=".$_SESSION['id_company'];
			$res=getrows($sql,$err);
			
			global $link;
			$staff_id = $res[0]['id_employee'];
			
			$this->mail4NotificationOfEvent("3",$res);
			$uploadDir  = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_orig']."avatar/";
			$thumbnailDir = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_thumb']."avatar/";
			$thumbnail4searchDir = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_thumb4_search']."avatar/";
			$cvuploadDir = APP_ROOT."files/cv/";    
			
			foreach($res as $val){
				if($val['avatar']!=''){
					$pic_name=$val['id_employee']."_".$val['avatar'];
					@unlink($uploadDir.$pic_name);
					@unlink($thumbnailDir.$pic_name);
					@unlink($thumbnail4searchDir.$pic_name);		
				}
				if($val['cv']!=''){
					$cv_name=$val['id_employee']."_".$val['cv'];           
	         			@unlink($cvuploadDir.$cv_name);
	         		}
			}
			$r=$this->obj_employee->delete("employee"," id_employee IN (".$keys.") AND id_company=".$_SESSION['id_company']);
			//$r=$this->obj_employee->delete("employeeBenefits"," id_employee IN (".$keys.")");
			//$r=$this->obj_employee->delete("employeeContact"," id_employee IN (".$keys.")");
			//$r=$this->obj_employee->delete("employeeLeaveRequest"," id_employee IN (".$keys.")");
			//$r=$this->obj_employee->delete("employeeProperties"," id_employee IN (".$keys.")");
			//$r=$this->obj_employee->delete("employeeSalary"," id_employee IN (".$keys.")");
			//$r=$this->obj_employee->delete("employeeTerminationContract"," id_employee IN (".$keys.")");
			if($r){
				$fmsg = getmessage('EMP_REC_DEL_SUC');
			}else{
				$fmsg = getmessage('EMP_REC_DEL_FAIL');
			}
			
			
			
			
			
			
			/*
			** site_device_mapping
			** 
			** auto create table
			*/
			
			foreach($res as $val){
				$staff_id = $val['id_employee'];
				$tbl = 'site_device_mapping';
				$dg_id = '';
				if($staff_id){
					
					include_once('manage.php');
					$tbl = 'site_device';
					$map_tbl = 'site_device_mapping';
					$g_tbl = 'site_device_group';
					
					
					$query = 'select id_g from '.$map_tbl.' where id_user="'.$staff_id.'" limit 0,1';
				
					$result = $link->query($query);
					if($result){
						while($row = mysqli_fetch_array($result)){
							$dg_id = $row['id_g'];
						}
					}
				
					$query = 'delete from '.$map_tbl.' where id_user="'.$staff_id.'"';
					$result = $link->query($query);
					
					
					/*
					** update device
					*/
					if($dg_id){
						
						$did_list = '';
						$query = 'select * from '.$g_tbl.' where id="'.$dg_id.'"';
						$result = $link->query($query);
						if($result){
							if($row = mysqli_fetch_array($result)){
								$did_list = $row['did_list'];
							}
						}

						$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
				
						$result = $link->query($query);
						if($result){
							while($row = mysqli_fetch_array($result)){
								if($row['ip']){
									$port = $row['port'];
									if(!$port) $port = '80';
									$fingerprint_device_ip = $row['ip'].':'.$port;
									deleteUserById($staff_id, $fingerprint_device_ip);
								}
							}
						}
					}
					
				}
			}
		}
		return $fmsg;
	}
#####################################################################################################
# Function Name : _employeeDetail                                                                   #
# Description   : It gives employee personal information                              		    #
#		                      	                                                            #
# Input         : Employee Id                                                                       #
# Output	: personalInfo template                                                             #
#####################################################################################################	
	function _employeeDetail(){
		global $link;
		$id_employee = $this->_input['id'];
		$staff_id = $id_employee;
		include('manage.php');
		$tbl = 'site_device';
		$tg_id = '';
		
		$emp_gid = '';
		$tz1_id = '';
		$tz2_id = '';
		$tz3_id = '';
		$m_tbl = 'site_device_mapping';
		$query = 'select * from '.$m_tbl.' where id_user="'.$staff_id.'"';
		$result = $link->query($query);
		if($result){
			if($row = mysqli_fetch_array($result)){
				$emp_gid = $row['id_g'];
				$tz1_id = $row['tz1'];
				$tz2_id = $row['tz2'];
				$tz3_id = $row['tz3'];
			}
		}
		
		$g_arr = array();
		$g_tbl = 'site_device_group';
		$query = 'select * from '.$g_tbl.' ';
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				$g_arr[$row['id']] = $row['name'];
				if($emp_gid==$row['id']){
					$did_list = $row['did_list'];
					$did_list = explode(',',$did_list);
					if(sizeof($did_list)>0){
						$q = 'select * from '.$tbl.' where id="'.$did_list[0].'" ';
						$re = $link->query($q);
						if($re){
							if($ro = mysqli_fetch_assoc($re)){
								$ip = $ro['ip'];
								$port = $ro['port'];
								if(!$port) $port = '80';
								$arr = getUserData($ip.':'.$port);
								$tg_id = str_replace('group','',$arr[$staff_id][3]);
							}
						}
					}
				}
			}
		}
		$this->_output['dg'] = $g_arr;
		$this->_output['dg_id'] = $emp_gid;
		$this->_output['dg_val'] = $g_arr[$emp_gid];
		
		$tg_arr = array(1=>'Group 1', 2=>'Group 2', 3=>'Group 3', 4=>'Group 4', 5=>'Group 5');
		$this->_output['tg'] = $tg_arr;
		$this->_output['tg_id'] = $tg_id;
		$this->_output['tg_val'] = $tg_arr[$tg_id];
		
		$t_tbl = 'site_device_timezone';
		$query = 'select * from '.$t_tbl.' order by id asc';
		$tz1_val = '';
		$tz2_val = '';
		$tz3_val = '';
		$tz_arr = array();
		$tz1_arr = array();
		$tz2_arr = array();
		$result = $link->query($query);
		if($result){
			while($row = mysqli_fetch_array($result)){
				if($row['type']==0 || $row['type']==4) $tz_arr[$row['id']] = $row['name'];
				if($row['type']==1) $tz1_arr[$row['id']] = $row['name'];
				if($row['type']==2) $tz2_arr[$row['id']] = $row['name'];
				if($row['id']==$tz1_id) $tz1_val = $row['name'];
				if($row['id']==$tz2_id) $tz2_val = $row['name'];
				if($row['id']==$tz3_id) $tz3_val = $row['name'];
			}
		}
		$this->_output['tz'] = $tz_arr;
		$this->_output['tz1'] = $tz1_arr;
		$this->_output['tz2'] = $tz2_arr;
		$this->_output['tz1_id'] = $tz1_id;
		$this->_output['tz2_id'] = $tz2_id;
		$this->_output['tz3_id'] = $tz3_id;
		$this->_output['tz1_val'] = $tz1_val;
		$this->_output['tz2_val'] = $tz2_val;
		$this->_output['tz3_val'] = $tz3_val;

		
		$res_s=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'employee","'."id_employee=".$id_employee."  LIMIT 1".'")');
		$this->_output['emp_status_val'] = $res_s['emp_status'];
		
		$res=$this->employee_bl->getEmployeeRecord($id_employee);
		if(!$res){
			$_SESSION['raise_message']['global']="Sorry system does not find any record for this employee";
			redirect(LBL_SITE_URL."index.php/employee/employeeList");
		}
		$this->_output['res']=$res;
		$this->_output['country']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'country","'."".'")','id_country','country_name',$err);
		
		if($res['country']){
			$tmp_res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'country","'."country_name='".$res['country']."' LIMIT 1".'")');
			
			$this->_output['state']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'state","'."id_country=".$tmp_res['id_country'].'")','id_state','state_name',$err);
			
		}
		if($res['state']){
			$tmp_res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'state","'."state_name='".$res['state']."' LIMIT 1".'")');
			
			$this->_output['city']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'city","'."id_state=".$tmp_res['id_state'].'")','id_city','city_name',$err);
			
		}
		if($_SESSION['id_company']){
			$this->obj_employee->updateModifyDate("view");
		}
		$this->_output['tpl']="employee/personalInfo";



			
	}
#####################################################################################################
# Function Name : _updateEmployee                                                                   #
# Description   : It updatess employee personal information                              	    #
#		                      	                                                            #
# Input         : Employee Id  and new personal information                                         #
# Output	: redirect to employeeDetail function                                               #
#####################################################################################################	
	function _updateEmployee(){
		include('manage.php');
		$employee=$this->_input['employee'];
		$update_id = $this->_input['update_id'];
		
		global $link;
		$tbl = 'hrm__employee';
		$card_no = $this->_input['employee']['card'];
		$repeat_card_user_arr = array();

		$dg_id = $this->_input['dg'];
		$tg_id = $this->_input['tg'];
		$tz1 = $this->_input['tz1'];
		//$tz2 = $this->_input['tz2'];
		$tz2 = '0';
		$tz3 = $this->_input['tz3'];
		$is_update_device = $this->_input['is_update_device'];
		
		if($dg_id && $is_update_device){
			$tbl = 'site_device';
			$g_tbl = 'site_device_group';
			$did_list = '';
			$query = 'select * from '.$g_tbl.' where id="'.$dg_id.'"';
			$result = $link->query($query);
			if($result){
				if($row = mysqli_fetch_array($result)){
					$did_list = $row['did_list'];
				}
			}

			$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
			$device_ip_arr = array();
			$result = $link->query($query);
			if($result){
				while($row = mysqli_fetch_array($result)){
					if($row['ip']){
						$port = $row['port'];
						if(!$port) $port = '80';
						$fingerprint_device_ip = $row['ip'].':'.$port;
						if(!detectDeviceConection($fingerprint_device_ip)){
							print '<div style="padding-top:100px"><span style="color:red">'.getmessage('COM_UPD_FAIL').': Offline device existing in the group</span></div>';
							return 0;
						}
					}
				}
			}
		}

		if($card_no){
			$result = $link->query('select * from '.$tbl.' where card="'.$card_no.'" and id_employee<>"'.$update_id.'" limit 0,1');
			if($result){
				if($row = mysqli_fetch_array($result)){
					$repeat_card_user_arr = $row;
				}
			}

			if(sizeof($repeat_card_user_arr)>0){
				print '<div style="padding-top:100px"><span style="color:red">'.getmessage('COM_UPD_FAIL').': Card No. repeated: owned by '.$repeat_card_user_arr['lastname'].$repeat_card_user_arr['firstname'].'</span></div>';
				return 0;
			}
		}


		$staff_id = '';
		$staff_id = $update_id;
		$employee['username']=$employee['work_email'];
		if(isset($this->_input['dob']) && $this->_input['dob']!=''){
			$dob=explode("-",$this->_input['dob']);
			$employee['dob'] = $dob[2]."-".$dob[1]."-".$dob[0];
		}
		$time=strtotime("now");
		if($_FILES['upload_cv']['name']){
			$employee['cv']=$time."_".convert_me($_FILES['upload_cv']['name']);
		}
		
			if($this->_input['joined_date']!=''){
				$doj=explode("-",$this->_input['joined_date']);
				$employee['joined_date']=$doj[2]."-".$doj[1]."-".$doj[0];
			}
		$r=$this->obj_employee->update("employee",$employee,"id_employee='".$_SESSION['cur_emp_id']."' ".$cond." LIMIT 1");
		// To upload a cv
		$cv['name']=$employee['cv'];
		$cv['tmp_name']=$_FILES['upload_cv']['tmp_name'];
		if ($_FILES['upload_cv']['name']){
			$r=$this->uploadCv($cv,$_SESSION['cur_emp_id'],'cv',trim($this->_input['prev_cv']));
		}
		// End
		if($r){
			$_SESSION['raise_message']['global']=getmessage('COM_UPD_SUCC');
		}else{
			$_SESSION['raise_message']['global']=getmessage('COM_UPD_FAIL');
		}
		$this->obj_employee->updateModifyDate();
		$this->mail4NotificationOfEvent("2","Updated employee detail page");
		
		
		
		
		
		
		/*
		** site_device_mapping
		** 
		** auto create table
		*/
		$tbl = 'site_device_mapping';
		if($staff_id){
			$old_card = '';
			$old_gid = '';
			$has_old_gid = false;
			$query = 'select * from '.$tbl.' where id_user="'.$update_id.'"';
			$result = $link->query($query);
			if($result){
				if($row = mysqli_fetch_array($result)){
					 $old_gid = $row['id_g'];
					 $has_old_gid = true;
				}
			}
			
			if($has_old_gid){
				$query = 'update '.$tbl.' set id_g="'.$dg_id.'", id_user="'.$staff_id.'", tz1="'.$tz1.'", tz2="'.$tz2.'", tz3="'.$tz3.'" where id_user="'.$update_id.'"';
				$result = $link->query($query);
			}else{
				$query = "select id from ".$tbl." order by id desc limit 0,1";
				$result = $link->query($query);
				if($result){
					if($row = mysqli_fetch_array($result)){
						$lastPkey = ($row["id"]+1);
					}
				}
				if(!$lastPkey) $lastPkey = 1;
				
				$query = 'insert into '.$tbl.' (id, id_user, id_g, tz1, tz2, tz3) values ("'.$lastPkey.'", "'.$staff_id.'", "'.$dg_id.'", "'.$tz1.'", "'.$tz2.'", "'.$tz3.'")';
				$result = $link->query($query);
			}
			
			/*
			** update device
			*/
			include_once('manage.php');
			$tbl = 'site_device';
			if($is_update_device){
				//if($old_gid ){
					/*
						$g_tbl = 'site_device_group';
						$did_list = '';
						$query = 'select * from '.$g_tbl.' where id="'.$old_gid.'"';
						$result = $link->query($query);
						if($result){
							if($row = mysqli_fetch_array($result)){
								$did_list = $row['did_list'];
							}
						}
						$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
					$result = $link->query($query);
					if($result){
						while($row = mysqli_fetch_array($result)){
							if($row['ip']){
								$port = $row['port'];
								if(!$port) $port = '80';
								$fingerprint_device_ip = $row['ip'].':'.$port;
								deleteUserById($update_id, $fingerprint_device_ip);
							}
						}
					}
*/
					$query = 'select * from '.$tbl.'';
					$result = $link->query($query);
					if($result){
						while($row = mysqli_fetch_array($result)){
							if($row['ip']){
								$port = $row['port'];
								if(!$port) $port = '80';
								$fingerprint_device_ip = $row['ip'].':'.$port;
								deleteUserById($update_id, $fingerprint_device_ip);
							}
						}
					}
				//}
				
				$card_no = $employee['card'];
				if($card_no && $dg_id){
					
					$g_tbl = 'site_device_group';
					$did_list = '';
					$query = 'select * from '.$g_tbl.' where id="'.$dg_id.'"';
					$result = $link->query($query);
					if($result){
						if($row = mysqli_fetch_array($result)){
							$did_list = $row['did_list'];
						}
					}

					$query = 'select * from '.$tbl.' where id in ('.$did_list.')';
					if(!$tg_id) $tg_id = '1';
					$result = $link->query($query);
					if($result){
						while($row = mysqli_fetch_array($result)){
							if($row['ip']){
								$port = $row['port'];
								if(!$port) $port = '80';
								$fingerprint_device_ip = $row['ip'].':'.$port;
								addUser($staff_id, $card_no, $fingerprint_device_ip, $tg_id, $tz1, $tz2, $tz3);
							}
						}
					}
				}
			}
		}
		
		
		if($_SESSION['id_company']){
			redirect(LBL_SITE_URL."index.php/employee/employeeDetail/id-".$_SESSION['cur_emp_id']);
		}else{
			redirect(LBL_SITE_URL."index.php/employee/employeeDetail");
		}
	}

	
#####################################################################################################
# Function Name : uploadCv                                                                          #
# Description   : It upload a cv under employee personal information                          	    #
#		                      	                                                            #
# Input         : cv name,id_employee,location,previous cv name                                     #
# Output	: return updating status                                                            #
#####################################################################################################	
	function uploadCv($cv,$id,$loc,$prev_cv=''){ 
		$err='';
		if($cv['name'] && $id){
		    $uploadDir = APP_ROOT."files/".$loc."/";               
		    $fname= $id."_".$cv['name'];
		    $file_tmp=$cv['tmp_name'];
		    if($prev_cv!=''){
			    $prename= $id."_".$prev_cv;			   
			    @unlink($uploadDir.$prename);
		    }
		    $err=copy($file_tmp, $uploadDir.$fname);     
		}
		return $err;
	}
#####################################################################################################
# Function Name : _removeCv                                                                         #
# Description   : It remove cv under employee personal information                           	    #
#		                      	                                                            #
# Input         : cv name,id_employee                                                               #
# Output	: No Output                                                                         #
#####################################################################################################
	function _removeCv(){
		 $uploadDir = APP_ROOT."files/cv/";               
	         $fname= $_SESSION['cur_emp_id']."_".trim($this->_input['cvname']);
	         @unlink($uploadDir.$fname);
	         $employee['cv']='';
	         $r=$this->obj_employee->update("employee",$employee,"id_employee='".$_SESSION['cur_emp_id']."' LIMIT 1");
	         $this->mail4NotificationOfEvent("2","Removed Cv");
		 $this->obj_employee->updateModifyDate();
	         ob_clean();
	         if($r){
	         	print "1";
	         	$_SESSION['raise_message']['global']=getmessage('EMP_REMOVE_SUCC');
		 }else{
			print "0";
			$_SESSION['raise_message']['global']=getmessage('EMP_REMOVE_FAIL');
		 }
		 exit;
	}
#####################################################################################################
# Function Name : _downloadCv                                                                       #
# Description   : It download cv under employee personal information                           	    #
#		                      	                                                            #
# Input         : cv name                                                                           #
# Output	: No Output                                                                         #
#####################################################################################################
	function _downloadCv(){ 
		if($_SESSION['id_company']){
			$id=$_SESSION['cur_emp_id'];
		}else{
			$id=$_SESSION['id_employee'];
		}
		$dir=APP_ROOT."files/cv/".$id."_";
		$file_name=trim($this->_input['cv']);
		$this->downloadFile($dir,$file_name);
	}
#####################################################################################################
# Function Name : _terminateEmp                                                                     #
# Description   : It gives an ui to terminate employee                                   	    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: terminate template                                                                #
#####################################################################################################
	function _terminateEmp(){
		global $link;
		if($_SESSION['id_company']){
			$sql='CALL get_search_sql("'.TABLE_PREFIX.'employeeTerminationContract","'."id_employee='".$this->_input['id']."' LIMIT 1".'")';
			$this->_output['flag']=$this->_input['flag'];
			$this->_output['terminate_res']=getsingleindexrow($sql);
			
			$sql='CALL get_search_sql("'.TABLE_PREFIX.'employee","'."id_employee='".$this->_input['id']."' LIMIT 1".'")';
			$this->_output['emp_res']=getsingleindexrow($sql);
			$this->_output['tpl']="employee/terminate";
		}
	}
#####################################################################################################
# Function Name : _updateTermination                                                                #
# Description   : It updates employee termination                                       	    #
#		                      	                                                            #
# Input         : form data                                                                         #
# Output	: Gives leftPan template to reflect updation on browser                             #
#####################################################################################################
	function _updateTermination(){
		$id_terminate=trim($this->_input['id_terminate']);
		//$id_employee=trim($this->_input['id_employee']);
		$terminate_date=trim($this->_input['terminate_date']);
		if($_SESSION['id_company']){
			$terminate=$this->_input['terminate'];
			if($terminate_date!=''){
				$tmp_terminate_dt=explode("-",$terminate_date);
				$terminate['terminate_date']=$tmp_terminate_dt[2]."-".$tmp_terminate_dt[1]."-".$tmp_terminate_dt[0];
			}
			if($id_terminate!=''){
				$r=$this->obj_employee->update("employeeTerminationContract",$terminate,"id_terminate=".$id_terminate." LIMIT 1");
			}else{
				$terminate['id_employee']=$_SESSION['cur_emp_id'];
				$r=$this->obj_employee->insert("employeeTerminationContract",$terminate,1);
			}
		}
		$res_personal=$this->employee_bl->getEmployeeRecord($_SESSION['cur_emp_id']);
		if($r){
			$this->obj_employee->updateModifyDate();
			$msg=getmessage('EMP_SAVE_SUCC');
		}else{
			$msg=getmessage('EMP_SAVE_FAIL');
		}
		print "<script>callmsg('".$msg."')</script>";
		$this->_output['tpl']="employee/leftPan";
	}
#####################################################################################################
# Function Name : _editPhoto                                                                        #
# Description   : It gives an ui to edit profile picture                                       	    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: avatar template                                                                   #
#####################################################################################################
	function _editPhoto(){
		$this->unlinkFiles();
		$this->_output['avatar_res']=$this->employee_bl->getAvatarData();
		$this->_output['tpl']="employee/avatar";
	}
#####################################################################################################
# Function Name : _updateAvatar                                                                     #
# Description   : It updates Profile Picture                                                        #
#		                      	                                                            #
# Input         : File name                                                                         #
# Output	: No Output                                                                         #
#####################################################################################################
	function _updateAvatar(){ 
		$prev_img=trim($this->_input['prev_img']);
		$hid_image=trim($this->_input['hid_image']);
		//$id_employee=$this->_input['update_id'];
		if($prev_img!=''){
			$uploadDir  = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_orig']."avatar/";
			$thumbnailDir = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_thumb']."avatar/";
  			$uploadDir_prev  = APP_ROOT.$GLOBALS['conf']['IMAGE']['preview_orig'];
			
			$thumb_height = $GLOBALS['conf']['IMAGE']['thumb_height'];
			$thumb_width = $GLOBALS['conf']['IMAGE']['thumb_width'];
			
			$img_name=explode('_',$prev_img);
			array_shift($img_name);
			$new_img=implode('_',$img_name);
			$fname = $_SESSION['cur_emp_id']."_".$new_img;
			
			if($hid_image!=''){
				@unlink($uploadDir.$hid_image);
				@unlink($thumbnailDir.$hid_image);
				@unlink($thumbnail4searchDir.$hid_image);				
			}
			
			copy($uploadDir_prev.$prev_img, $uploadDir.$fname);
			$this->r = new thumbnail_manager($uploadDir.$fname,$thumbnailDir.$fname);
			$this->r->get_container_thumb($thumb_height,$thumb_width,0,0);
			
			// For searching case or autocomplete case
			$thumbnail4searchDir = APP_ROOT.$GLOBALS['conf']['IMAGE']['image_thumb4_search']."avatar/";
			$thumb4_search_height = $GLOBALS['conf']['IMAGE']['thumb4_search_height'];
			$thumb4_search_width = $GLOBALS['conf']['IMAGE']['thumb4_search_width'];
			
			$this->r = new thumbnail_manager($uploadDir.$fname,$thumbnail4searchDir.$fname);
			$this->r->get_container_thumb($thumb4_search_height,$thumb4_search_width,0,0);
			// end
			$update['avatar']=$new_img;
			$this->obj_employee->update("employee",$update,"id_employee='".$_SESSION['cur_emp_id']."' ".$cond);
			$this->obj_employee->updateModifyDate();
	        	$this->mail4NotificationOfEvent("2","Updated profile picture");
			$msg="Successfully changed new profile picture.";
			ob_clean();
			print $fname."::".$msg;
		}
	}
#####################################################################################################
# Function Name : _editJobProfile                                                                   #
# Description   : It gives an ui to edit Job profile                                       	    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: changeJobProfile template                                                         #
#####################################################################################################
	function _editJobProfile(){
		global $link;
		$res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'employee","'."id_employee=".$_SESSION['cur_emp_id']." AND id_company=".$_SESSION['id_company']." LIMIT 1".'")');
		$this->_output['res_emp']=$res;
		
    $this->_output['division']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyDivision","'."".'")',"id_division","div_name",$err);
    
    if($res['division']){
      $this->_output['department']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyDepartment","'."id_division=".$res['division'].'")',"id_department","dept_name",$err);
    }
    
    if($res['department']){
      $this->_output['team']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyTeam","'."id_department=".$res['department'].'")',"id_team","team_name",$err);
    }
    // if($res['team']){
    //   $this->_output['job_title']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyJobTitle","'."id_team=".$res['team'].'")',"id_job_title","job_title_name",$err);
    // }
		$empsts=$GLOBALS['conf']['EMPLOYMENT_STATUS'];
    // unset($empsts[2]);
		$this->_output['emp_sts']=$empsts;		
		$this->_output['tpl']="employee/changeJobProfile";
	}
#####################################################################################################
# Function Name : _updateJobProfile                                                                 #
# Description   : It updates Job profile                                                      	    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: Gives leftPan template to reflect updation on browser                             #
#####################################################################################################
	function _updateJobProfile(){
		$employee=$this->_input['emp'];
		$r=$this->obj_employee->update("employee",$employee,"id_employee='".$_SESSION['cur_emp_id']."' ".$cond." LIMIT 1");
		if($r){
			$msg=getmessage('COM_UPD_SUCC');
		}else{
			$msg=getmessage('COM_UPD_FAIL');
		}
		$this->obj_employee->updateModifyDate();
		$res_personal=$this->employee_bl->getEmployeeRecord($_SESSION['cur_emp_id']);
		$this->_output['flag']=$this->_input['flag'];
		print "<script>callmsg('".$msg."');</script>";
		$this->_output['tpl']="employee/leftPan";
	}
#####################################################################################################
# Function Name : _emergencyDetail                                                                  #
# Description   : It gives emergency detail                                                 	    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: emergencyInfo template       					                    #
#####################################################################################################
	function _emergencyDetail(){
		global $link;
		$id_employee=$this->_input['id'];
		$res_personal=$this->employee_bl->getEmployeeRecord($id_employee);
		if(!$res_personal){
			$_SESSION['raise_message']['global']="This doesn't mean to our site.";
			redirect(LBL_SITE_URL);
		}
		$this->_output['res_personal']=$res_personal;
		
		$sql_emergency='CALL get_search_sql("'.TABLE_PREFIX.'employeeContact","'."id_employee='".$_SESSION['cur_emp_id']."' LIMIT 1".'")';
		
		$res=getsingleindexrow($sql_emergency);
		$this->_output['res']=$res;
		$this->_output['country']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'country","'."".'")','id_country','country_name',$err);
		
		if($res['country']){
			$tmp_res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'country","'."country_name='".$res['country']."' LIMIT 1".'")');
			$this->_output['state']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'state","'."id_country=".$tmp_res['id_country'].'")','id_state','state_name',$err);
		}
		
		if($res['state']){
			$tmp_res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'state","'."state_name='".$res['state']."' LIMIT 1".'")');
			$this->_output['city']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'city","'."id_state=".$tmp_res['id_state'].'")','id_city','city_name',$err);
		}
		$this->_output['tpl']="employee/emergencyInfo";
	}
#####################################################################################################
# Function Name : _emergencyDetail                                                                  #
# Description   : It updates emergency detail                                                 	    #
#		                      	                                                            #
# Input         : id_contact                                                                        #
# Output	: redirect to emergencyDetail function 				                    #
#####################################################################################################
	function _updateEmergency(){
		$id_contact=$this->_input['update_contact_id'];
		//$id_employee=$this->_input['update_id'];
		$emergency=$this->_input['employee'];
		if(trim($this->_input['update_contact_id'])!=''){
			$r=$this->obj_employee->update("employeeContact",$emergency,"id_contact='".$id_contact."' AND id_employee='".$_SESSION['cur_emp_id']."' LIMIT 1");
		}else{
			$emergency['id_employee']=$_SESSION['cur_emp_id'];
			$emergency['ip']=$_SERVER['REMOTE_ADDR'];
			$r=$this->obj_employee->insert("employeeContact",$emergency,1);
		}
		if($r){
			$this->obj_employee->updateModifyDate();
			$_SESSION['raise_message']['global']=getmessage('COM_UPD_SUCC');
		}else{
			$_SESSION['raise_message']['global']=getmessage('COM_UPD_FAIL');
		}
	        $this->mail4NotificationOfEvent("2","Updated emergency detail");
		if($_SESSION['id_company']){
			redirect(LBL_SITE_URL."index.php/employee/emergencyDetail/id-".$_SESSION['cur_emp_id']);
		}else{
			redirect(LBL_SITE_URL."index.php/employee/emergencyDetail");
		}
	}
#####################################################################################################
# Function Name : _salaryDetail                                                                     #
# Description   : It gives all previous and current salary                                    	    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: salary template                				                    #
#####################################################################################################
	function _salaryDetail($id=''){
		if($id!=''){
			$id_employee=$id;
		}else{
			$id_employee=$this->_input['id'];
		}
		$res_personal=$this->employee_bl->getEmployeeRecord($id_employee);
		if(!$res_personal){
			$_SESSION['raise_message']['global']="This doesn't mean to our site.";
			redirect(LBL_SITE_URL);
		}
		$this->_output['res_personal']=$res_personal;
		
		$sql_salary='CALL get_search_sql("'.TABLE_PREFIX.'employeeSalary","'."id_employee='".$_SESSION['cur_emp_id']."' ORDER BY id_salary DESC".'")';
		
		$res=getrows($sql_salary,$err);
		$this->_output['list']=$res;
		$this->_output['tpl']="employee/salary";
	}
#####################################################################################################
# Function Name : _addSalary                                                                        #
# Description   : It gives an interface to add salary under an employee                       	    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: addSalary template                				                    #
#####################################################################################################
	function _addSalary(){
		if($_SESSION['id_company']){
			$this->_output['id']=$this->_input['id'];
			$this->_output['tpl']="employee/addSalary";
		}else{
			redirect(LBL_SITE_URL);
		}
	}
#####################################################################################################
# Function Name : _insertSalary                                                                     #
# Description   : It inserts salary information							    #
#		                      	                                                            #
# Input         : Salary information                                                                #
# Output	: call to salaryDetail function       				                    #
#####################################################################################################
	function _insertSalary(){
		global $link;
		if($_SESSION['id_company']){
			$salary=$this->_input['salary'];
			//$id_employee=$salary['id_employee'];
			$salary['ip']=$_SERVER['REMOTE_ADDR'];
			$prevsal_sql='CALL get_search_sql("'.TABLE_PREFIX.'employeeSalary","'."id_employee='".$_SESSION['cur_emp_id']."' ORDER BY id_salary DESC LIMIT 1".'")';
			$prevsal_res=getsingleindexrow($prevsal_sql);
			if($prevsal_res){
				$salary['previous_salary']=$prevsal_res['current_salary'];
			}else{
				$salary['previous_salary']='0';
			}
			$r=$this->obj_employee->insert("employeeSalary",$salary,1);
			if($r){
				$upd_salary['salary']=$salary['current_salary'];
				$upd_salary['currency']=$salary['currency'];
				$upd_salary['pay_frequency']=$salary['pay_frequency'];
				$this->obj_employee->update("employee",$upd_salary,"id_employee=".$_SESSION['cur_emp_id']);
				$msg = getmessage('COM_INS_SUCC');
			}else{
				$msg = getmessage('COM_INS_FAIL');
			}
			$this->obj_employee->updateModifyDate();
			ob_clean();
			print "<script>callmsg('".$msg."');</script>";
			$this->_salaryDetail($_SESSION['cur_emp_id']);
		}else{
			redirect(LBL_SITE_URL);
		}
	}
#####################################################################################################
# Function Name : _benefits                                                                         #
# Description   : Get all eligibled benefits of an employee					    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: benefits template             				                    #

#####################################################################################################
	function _benefits($id=''){
		if($id!=''){
			$id_employee=$id;
		}else{
			$id_employee=$this->_input['id'];
		}
		$res_personal=$this->employee_bl->getEmployeeRecord($id_employee);
		if(!$res_personal){
			$_SESSION['raise_message']['global']="This doesn't mean to our site.";
			redirect(LBL_SITE_URL);
		}
		$this->_output['res_personal']=$res_personal;

		$sql_benefits=get_search_sql("employeeBenefits EB,".TABLE_PREFIX."companyBenefits CB"," EB.id_benefits=CB.id_benefits AND EB.id_employee='".$_SESSION['cur_emp_id']."' ORDER BY EB.id_emp_benefits DESC");

		$res=getrows($sql_benefits,$err);
		if(!$res){
			$interval=$GLOBALS['conf']['BENEFIT']['default_validity'];
			$this->obj_employee->insertDefaultBenefits($_SESSION['cur_emp_id'],$interval);		
		}
		$this->_output['list']=$res;
		$this->_output['tpl']="employee/benefits";
	}
#####################################################################################################
# Function Name : _assignBenefit                                                                    #
# Description   : Give an ui to assign benefit to an employee					    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: editBenefit template             				                    #
#####################################################################################################
function _assignBenefit(){
		if($_SESSION['id_company']){
			$this->_output['id']=$this->_input['id'];

			$res=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'employeeBenefits","'."id_employee=".$_SESSION['cur_emp_id'].'")','id_benefits','id_benefits',$err);
            $benefitids=implode(",",$res);
            if(!empty($benefitids))
            {
            $benefitids="AND id_benefits NOT IN (".$benefitids.")";
            }
            else
            {
            $benefitids='';
            }
			$this->_output['benefits']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyBenefits","'."id_company=".$_SESSION['id_company']." AND is_set!=1 $benefitids".'")','id_benefits','benefit_name',$err);
			$this->_output['flag']=1;
			$this->_output['tpl']="employee/editBenefit";
		}else{
			redirect(LBL_SITE_URL);
		}
	}
#####################################################################################################
# Function Name : _insertBenefit                                                                    #
# Description   : Insert assigned benefit under an employee					    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: call _benefits function             				                    #
#####################################################################################################
	function _insertBenefit(){
		if($_SESSION['id_company']){
			$benefit=$this->_input['benefit'];
			$benefit['id_employee']=$this->_input['id_employee'];
			//$id_employee=$benefit['id_employee'];
			$tmp_ft_dt=explode(" to ",trim($this->_input['from_to']));
			$tmp_from=explode("-",trim($tmp_ft_dt[0]));
			$benefit['from_date']=$tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
			$tmp_to=explode("-",trim($tmp_ft_dt[1]));
			$benefit['to_date']=$tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
			$r=$this->obj_employee->insert("employeeBenefits",$benefit);
			if($r){
				$this->obj_employee->updateModifyDate();
				$msg = getmessage('EMP_ASSIGN_SUCC');
			}else{
				$msg = getmessage('EMP_ASSIGN_FAIL');
			}
			ob_clean();
			print "<script>callmsg('".$msg."');</script>";
			$this->_benefits($_SESSION['cur_emp_id']);
		}
	}
#####################################################################################################
# Function Name : _editBenefit                                                                      #
# Description   : Gives an ui to edit benefit under an employee					    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: editBenefit template             				                    #
#####################################################################################################
	function _editBenefit(){
		global $link;
		if($_SESSION['id_company']){
			//$this->_output['id']=$this->_input['id'];
			$sql=get_search_sql("employeeBenefits Eb,".TABLE_PREFIX."companyBenefits Cb"," Eb.id_benefits=Cb.id_benefits AND Eb.id_emp_benefits='".$this->_input['id_emp_benefits']."' AND Cb.id_company='".$_SESSION['emp_company']."' LIMIT 1");
			$this->_output['res']=getsingleindexrow($sql);
			$this->_output['benefits']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyBenefits","'."id_company=".$_SESSION['id_company']." AND is_set!=1".'")','id_benefits','benefit_name',$err);
			
			$this->_output['tpl']="employee/editBenefit";
		}else{
			redirect(LBL_SITE_URL);
		}	
	}
#####################################################################################################
# Function Name : _updateBenefit                                                                    #
# Description   : Updates benefit under an employee						    #
#		                      	                                                            #
# Input         : id_emp_benefits                                                                   #
# Output	: call _benefits function             				                    #
#####################################################################################################
	function _updateBenefit(){
		if($_SESSION['id_company']){
			$benefit=$this->_input['benefit'];
			//$id_employee=$benefit['id_employee'];
			$tmp_ft_dt=explode(" to ",trim($this->_input['from_to']));

			$tmp_from=explode("-",trim($tmp_ft_dt[0]));
			$benefit['from_date']=$tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
			$tmp_to=explode("-",trim($tmp_ft_dt[1]));
			$benefit['to_date']=$tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
			
			$r=$this->obj_employee->update("employeeBenefits",$benefit,"id_emp_benefits=".$this->_input['id_emp_benefits']);
			if($r){
				$this->obj_employee->updateModifyDate();
				$msg=getmessage('COM_UPD_SUCC');
			}else{
				$msg=getmessage('COM_UPD_FAIL');
			}
			ob_clean();
			print "<script>callmsg('".$msg."');</script>";
			$this->_benefits($_SESSION['cur_emp_id']);
		}else{
			redirect(LBL_SITE_URL);
		}
	}
#####################################################################################################
# Function Name : _deleteBenefit                                                                    #
# Description   : Deletes benefit(s) under an employee						    #
#		                      	                                                            #
# Input         : id_emp_benefits                                                                   #
# Output	: call _benefits function             				                    #
#####################################################################################################
	function _deleteBenefit(){
		if($_SESSION['id_company']){
			//$id_employee=$this->_input['id_employee'];
			$r=$this->obj_employee->delete("employeeBenefits"," id_emp_benefits=".$this->_input['id_emp_benefits']." AND id_employee='".$_SESSION['cur_emp_id']."' LIMIT 1");
			if($r){
				$msg=getmessage('COM_DEL_SUCC');
			}else{
				$msg=getmessage('COM_DEL_FAIL');
			}
			$this->obj_employee->updateModifyDate();
			ob_clean();
			print "<script>callmsg('".$msg."');</script>";
			$this->_benefits($_SESSION['cur_emp_id']);
		}
	}
#####################################################################################################
# Function Name : _assignedProperty                                                                 #
# Description   : Gives list of all assign property under an employee				    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: assignedProperty template            				                    #
#####################################################################################################
	function _assignedProperty($id=''){
		if($id!=''){
			$id_employee=$id;
		}else{
			$id_employee=$this->_input['id'];
		}
		$res_personal=$this->employee_bl->getEmployeeRecord($id_employee);
		if(!$res_personal){
			$_SESSION['raise_message']['global']="This doesn't mean to our site.";
			redirect(LBL_SITE_URL);
		}
      //  print_r($res_personal);
    	$this->_output['res_personal']=$res_personal;

		$sql_property='CALL get_search_sql("'.TABLE_PREFIX.'employeeProperties","'."id_employee='".$_SESSION['cur_emp_id']."' ORDER BY id_cproperty DESC".'")';
		
		$res=getrows($sql_property,$err);
		$this->_output['list']=$res;
		$this->_output['tpl']="employee/assignedProperty";
	}
#####################################################################################################
# Function Name : _addProperty                                                                      #
# Description   : Gives an ui to assign property under an employee				    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: addProperty template            				                    #
#####################################################################################################
	function _addProperty(){
		if($_SESSION['id_company']){
			$this->_output['id']=$this->_input['id'];
			$this->_output['property_type']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'propertyType","'."id_company=".$_SESSION['id_company'].'")',"id_property_type","type_name",$err);
			
			$this->_output['tpl']="employee/addProperty";
		}else{
			redirect(LBL_SITE_URL);
		}
	}
#####################################################################################################
# Function Name : _insertProperty                                                                   #
# Description   : Insert assigned property under an employee					    #
#		                      	                                                            #
# Input         : id_employee                                                                       #
# Output	: call to _assignedProperty function				                    #
#####################################################################################################
	function _insertProperty(){
		if($_SESSION['id_company']){
			$property=$this->_input['property'];
			//$id_employee=$property['id_employee'];
			
			$check=$this->employee_bl->checkPropertyExistance($property['id_property_type'],$property['property_name'],$property['serial_no']);
			if($check){
				ob_clean();
				exit("1");				
			}
			$property['no_of_items']='1';
			$property['ip']=$_SERVER['REMOTE_ADDR'];
			$r=$this->obj_employee->insert("employeeProperties",$property,1);
			if($r){
				$this->obj_employee->updateModifyDate();
				$msg = getmessage('COM_INS_SUCC');
			}else{
				$msg = getmessage('COM_INS_FAIL');
			}
			ob_clean();
			print "<script>callmsg('".$msg."');</script>";
			$this->_assignedProperty($_SESSION['cur_emp_id']);
		}
	}
#####################################################################################################
# Function Name : _deleteProperty                                                                   #
# Description   : Delete assigned property under an employee					    #
#		                      	                                                            #
# Input         : id_cproperty                                                                      #
# Output	: call to _assignedProperty function 				                    #
#####################################################################################################
	function _deleteProperty(){
		if($_SESSION['id_company']){
			//$id_employee=$this->_input['id_employee'];
			$this->obj_employee->delete("employeeProperties"," id_cproperty=".$this->_input['id_cproperty']." AND id_employee='".$_SESSION['cur_emp_id']."'");
			$this->obj_employee->updateModifyDate();
			ob_clean();
			$msg=getmessage('COM_DEL_SUCC');
			print "<script>callmsg('".$msg."');</script>";
			$this->_assignedProperty($_SESSION['cur_emp_id']);
		}
	}
#####################################################################################################
# Function Name : _leaveRequest                                                                     #
# Description   : List of employee leave request         					    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: leaveRequestList or leaveRequestSearch template according to condition            #
#####################################################################################################
	function _leaveRequest($leavestatus='',$flag='',$msg=''){
		if($flag==''){
			$leavestatus=$this->_input['leavestatus'];
		}
		$this->_output['res_personal']=$res_personal;
		$cond="1 ";
		
		if($_SESSION['id_company']){
			$cond.= " AND id_company=".$_SESSION['id_company'];
		}else if($_SESSION['id_employee']){
			$cond.= " AND id_employee=".$_SESSION['id_employee'];
		}else{
			redirect(LBL_SITE_URL);	
		}
		// for different message
		if($this->_input['msg']!=''){
			$_SESSION['amsg']=$this->_input['msg'];
		}elseif($msg){
			$_SESSION['amsg']=$msg;
		}else{
			$_SESSION['amsg']="";
		}
		// end
		if($this->_input['f']=='for_del'){
			$_SESSION['amsg']=$this->deleteLeave($this->_input['keys']);
		}
		if($this->_input['qs']!=''){
			$this->_input['qstart']=$this->_input['qs'];
		}
		$_REQUEST['choice']='leaveRequest';
		
		$uri="index.php/page-employee-choice-leaveRequest";
		// for filter on leave status
		$leavestatus=trim($leavestatus,',');
		if($leavestatus!='' && $_SESSION['id_company']){
			$search_cond=" AND leave_status IN (".$leavestatus.")";
			$uri.="-leavestatus-".$leavestatus;		

		}elseif(!$this->_input['chk'] && $_SESSION['id_company']){
			$search_cond=" AND leave_status IN (1)";
			$uri.="-leavestatus-1";		
		}
		
		$sql=get_search_sql("employeeLeaveRequest",$cond.$search_cond);
		//print $sql;
		$this->_output['sql'] = $sql;
		$this->_output['limit']= $GLOBALS['conf']['PAGINATE']['rec_per_page'];
		$this->_output['ajax']= "leaveRequest";		
		$this->_output['uri'] = $uri;
		$this->_output['type'] = "box";
		$this->_output['pg_header'] = "Leave Request List";
		$this->_output['sort_order'] = "DESC";
		$this->_output['sort_by'] = "id_leave_req";
		$this->_output['show']=$GLOBALS['conf']['PAGINATE']['show_page'];	
		$this->_output['field'] = array("name"=>array("Name",1));
		
		$this->_output['leave_type_res']=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyLeaveType","'."".'")',"id_leave_type","leave_type",$err);
		
		$this->_output['status']=$GLOBALS['conf']['LEAVE_STATUS'];
		
		$this->_output['employee']=$this->employee_bl->getEmployees($cond);
		
		if($this->_input['chk']){
			$arr = explode(",",$leavestatus);
			$this->_output['leavestatus']= array_combine($arr,$arr);
			$this->employee_bl->page_listing($this,'employee/leaveRequestList');
		}else{
			$this->_output['leavestatus']= array("1"=>"1");
			$_SESSION['amsg']='';
			$this->employee_bl->page_listing($this,'employee/leaveRequestSearch');
		}
	}
#####################################################################################################
# Function Name : _addLeave                	                                                    #
# Description   : Give an ui to apply leave	         					    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: addLeave template							            #
#####################################################################################################
	function _addLeave(){
		$leave_type_sql='CALL get_search_sql("'.TABLE_PREFIX.'companyLeaveType","'."".'")';
		$this->_output['leave_type_res']=getindexrows($leave_type_sql,"id_leave_type","leave_type");
	//	print_r($this->_output['leave_type_res']);
		$this->_output['status']=$GLOBALS['conf']['LEAVE_STATUS'];
		$this->_output['tpl']="employee/addLeave";
	}
#####################################################################################################
# Function Name : _insertLeave                	                                                    #
# Description   : Insert an employee     	         					    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: call to leaveRequest function 					            #
#####################################################################################################
	function _insertLeave(){
		global $link;
		$leave=$this->_input['leave'];
		$leave['id_employee']=$_SESSION['id_employee'];
		$res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'employee","'."id_employee=".$_SESSION['id_employee']." LIMIT 1".'")');
		
		$leave['id_company']=$res['id_company'];
		$leave['leave_status']=1;
		$date=explode("to",$this->_input['leave_date']);
		$leave['from_date']=convertodate(trim($date[0]),'dd-mm-yy','yyyy-mm-dd');
		$leave['to_date']=convertodate(trim($date[1]),'dd-mm-yy','yyyy-mm-dd');
		$id=$this->obj_employee->insert("employeeLeaveRequest",$leave);
		// Mailing leave request details to company admin
		if($id){
			$res_company=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'company","'."id_company=".$res['id_company']." LIMIT 1".'")');
			
			$to = $res_company['email_id'];
			
			$from = $_SESSION['username'];
			$subject = "Leave Request";
			
			$info=$leave;
			$info['username']=$arr['username'];
		
			$tpl= "employee/mailLeaveRequest";
			$this->smarty->assign('sm',$info);
			$leave_type_res=getindexrows('CALL get_search_sql("'.TABLE_PREFIX.'companyLeaveType","'."".'")',"id_leave_type","leave_type",$err);
			
			$this->smarty->assign('leavetype',$leave_type_res);
			$body = $this->smarty->fetch($this->smarty->add_theme_to_template($tpl));
			$msg=sendmail($to,$subject,$body,$from);// also u can pass  $cc,$bcc
		}
		// end
		if($id){
			$this->obj_employee->updateModifyDate();
			$msg = getmessage('EMP_LEAVE_APPLY_SUCC');
		}else{
			$msg = getmessage('EMP_LEAVE_APPLY_FAIL');
		}
	        $this->mail4NotificationOfEvent("2","Applied a leave");
		ob_clean();
		$this->_leaveRequest('','',$msg);
	}
#####################################################################################################
# Function Name : _editLeave                	                                                    #
# Description   : Gives ui to edit leave request        					    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: addLeave template    		 					            #
#####################################################################################################
	function _editLeave(){
		global $link;
		if($_SESSION['id_company']){
			$cond=" id_company=".$_SESSION['id_company'];
		}else{
			$cond=" id_employee=".$_SESSION['id_employee'];
		}
		$this->_output['id']=$this->_input['id_leave'];
		$sql='CALL get_search_sql("'.TABLE_PREFIX.'employeeLeaveRequest","'."id_leave_req='".$this->_input['id_leave']."' AND ".$cond." LIMIT 1".'")';
		
		$this->_output['res']=getsingleindexrow($sql);
		
		$leave_type_sql='CALL get_search_sql("'.TABLE_PREFIX.'companyLeaveType","'."".'")';
		
		$this->_output['leave_type_res']=getindexrows($leave_type_sql,"id_leave_type","leave_type");
		$this->_output['status']=$GLOBALS['conf']['LEAVE_STATUS'];
		$this->_output['tpl']="employee/addLeave";
	}
#####################################################################################################
# Function Name : _updateLeave                	                                                    #
# Description   : Updates leave request        					 		    #
#		                      	                                                            #
# Input         : id_leave_req                                                                      #
# Output	: call to leaveRequest function 					            #
#####################################################################################################
	function _updateLeave(){
		if($_SESSION['id_company']){
			$cond=" id_company=".$_SESSION['id_company'];
		}else{
			$cond=" id_employee=".$_SESSION['id_employee'];
		}
		$leave=$this->_input['leave'];
		$date=explode("to",trim($this->_input['leave_date']));

		$leave['from_date']=convertodate(trim($date[0]),'dd-mm-yy','yyyy-mm-dd');
		$leave['to_date']=convertodate(trim($date[1]),'dd-mm-yy','yyyy-mm-dd');

		$r=$this->obj_employee->update("employeeLeaveRequest",$leave,"id_leave_req=".$this->_input['upd_id']." AND ".$cond);
		if($r){
			$msg = getmessage('COM_UPD_SUCC');
		}else{
			$msg = getmessage('COM_UPD_FAIL');
		}
	        $this->mail4NotificationOfEvent("2","Updated applied leave");
		$this->obj_employee->updateModifyDate();
		ob_clean();
		$this->_input['qstart']=$this->_input['qstart'];
		$this->_leaveRequest($this->_input['leavestatus'],1,$msg);
	}
#####################################################################################################
# Function Name : deleteLeave                	                                                    #
# Description   : Deletes leave request        					 		    #
#		                      	                                                            #
# Input         : id_leave_req                                                                      #
# Output	: return deletion message  	 					            #
#####################################################################################################
	function deleteLeave($keys=''){
		if($_SESSION['id_company']){
			$cond=" id_company=".$_SESSION['id_company'];
		}else{
			$cond=" id_employee=".$_SESSION['id_employee'];
		}
		$r=$this->obj_employee->delete("employeeLeaveRequest"," id_leave_req IN (".$keys.") AND ".$cond);
	        $this->mail4NotificationOfEvent("2","Deleted applied leave");
		if($r){
			$this->obj_employee->updateModifyDate();
			$msg = getmessage('EMP_LEAVE_DEL_SUCC');
		}else{
			$msg = getmessage('EMP_LEAVE_DEL_FAIL');
		}
		return $msg;
	}
#####################################################################################################
# Function Name : _updateLeaveStatus           	                                                    #
# Description   : Updates leave request        					 		    #
#		                      	                                                            #
# Input         : id_leave_req                                                                      #
# Output	: call to leaveRequest function	 					            #
#####################################################################################################
	function _updateLeaveStatus(){
		global $link;
		if($_SESSION['id_company']){
			$cond=" id_company=".$_SESSION['id_company'];
		}else{
			$cond=" id_employee=".$_SESSION['id_employee'];
		}
		$leave['leave_status']=$this->_input['status'];
		$r=$this->obj_employee->update("employeeLeaveRequest",$leave,"id_leave_req=".$this->_input['id_leave']." AND ".$cond);

		// Mailing leave request details to company admin
		if($r){
			$res=getsingleindexrow(get_search_sql("employeeLeaveRequest ELR,".TABLE_PREFIX."employee E","ELR.id_employee=E.id_employee AND id_leave_req=".$this->_input['id_leave']." LIMIT 1"));	
			
			$to = $res['work_email'];
			$from = $_SESSION['username'];
			$subject = "Leave Status";
			$info=$res;
			$info['leave_status']=$this->_input['status'];
		
			$tpl= "employee/mailLeaveStatus";
			$this->smarty->assign('sm',$info);
			$body = $this->smarty->fetch($this->smarty->add_theme_to_template($tpl));
			$msg=sendmail($to,$subject,$body,$from);// also u can pass  $cc,$bcc
		}
		// end
		if($r){
			$this->obj_employee->updateModifyDate();
			$msg=getmessage('COM_UPD_SUCC');
		}else{
			$msg=getmessage('COM_UPD_FAIL');
		}
		ob_clean();
		$this->_leaveRequest('','',$msg);
	}
#####################################################################################################
# Function Name : _resetPwd           	    		                                            #
# Description   : Gives ui to reset password       				 		    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: resetPwd template		 					            #
#####################################################################################################
	function _resetPwd(){
		$this->_output['tpl']='employee/resetPwd';
	}
#####################################################################################################
# Function Name : _updatePwd           	    		                                            #
# Description   : Update new password            				 		    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: redirect to _resetPwd function 					            #
#####################################################################################################
	function _updatePwd(){
		global $link;
		if($_SESSION['id_company']){
			$id=$_SESSION['id_company'];
			$table="company";
		}elseif($_SESSION['id_employee']){
			$id=$_SESSION['id_employee'];
			$table="employee";
		}
		$res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.$table.'","'."id_".$table."='".$id."' LIMIT 1".'")');
		
		if($res['password']==trim(MD5($this->_input['opass']))){
			$_SESSION['raise_message']['global']=getmessage('EMP_PASSWORD_MISMATCH');
			if(trim($this->_input['npass'])==trim($this->_input['cpass'])){
				$pwd['password']=trim(MD5($this->_input['npass']));
				$r=$this->obj_employee->update($table,$pwd,"id_".$table."='".$id."' LIMIT 1");
				if($r){
					$_SESSION['raise_message']['global']=getmessage('EMP_PASSWORD_UPD_SUCC');
				}else{
					$_SESSION['raise_message']['global']=getmessage('EMP_PASSWORD_UPD_FAIL');	
				}
			}
		}else{
			$_SESSION['raise_message']['global']=getmessage('EMP_ENTER_CORRECT_PWD');
		}
		$this->obj_employee->updateModifyDate();
	        $this->mail4NotificationOfEvent("2","Updated my profile page");
		redirect(LBL_SITE_URL."index.php/employee/resetPwd");
	}
#####################################################################################################
# Function Name : _latestAddModifyViewEmp 	    		                                    #
# Description   : Gives all latest viewed employees   				 		    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: latestAddModifyViewEmp template 					            #
#####################################################################################################
	function _latestAddModifyViewEmp(){
		if($this->arg['flag']=='view' || $this->_input['flag']=='view'){
			$this->_output['lviewedemp']=$this->employee_bl->getLatestViewedEmployees();
			$this->_output['flag']='view';
		}else{
			$this->_output['lmodifiedemp']=$this->employee_bl->getLatestModifiedEmployees();
			$this->_output['laddedemp']=$this->employee_bl->getLatestAddedEmployees();
		}
		$this->_output['tpl']="employee/latestAddModifyViewEmp";
	}
#####################################################################################################
# Function Name : mail4NotificationOfEvent   		                                            #
# Description   : Sends mail to admin(s) if employee modify some of data	 		    #
#		                      	                                                            #
# Input         : No Input                                                                          #
# Output	: No Output 	 					   		            #
#####################################################################################################
	function mail4NotificationOfEvent($flg,$upd_page=''){
		$info['update_page'] = $upd_page;
		$info['name'] = $_SESSION['fullname'];
		$info['email'] = $_SESSION['username'];
		$info['flag'] = $flg;
		$tpl= "employee/mail4NotificationOfEvent";
		
		$res=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'companyNotification","'."id_company='".$_SESSION['emp_company']."' LIMIT 1".'")');
		$res_admin=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'company","'."id_company='".$_SESSION['emp_company']."' LIMIT 1".'")');
		
		$from = $res_admin['email_id'];
		$to = $res_admin['email_id'];
		$subject='';
		if($flg==2){
			if($res['emp_modify']==1){
				$subject = "Employee Modification";
			}
			if($_SESSION['id_company']){
				$info['email'] = $_SESSION['emp_email'];
			}
		}
		if($_SESSION['id_company'] && $flg==1){
			if($res['emp_add']==1){
				$info['email']=$upd_page;
				$subject = "Employee Added";
			}
		}
		if($_SESSION['id_company'] && $flg==3){
			if($res['emp_remove']==1){
				$this->smarty->assign('emps',$upd_page);
				$subject = "Employee Deleted";
			}
		}
		if($subject!=''){
			$this->smarty->assign('sm',$info);
			$body = $this->smarty->fetch($this->smarty->add_theme_to_template($tpl));
			$msg=sendmail($to,$subject,$body,$from);
			if($res['id_employee']){
				$res_2lvl_admin=getsingleindexrow('CALL get_search_sql("'.TABLE_PREFIX.'employee","'."id_employee='".$res['id_employee']."' LIMIT 1".'")');
				$to = $res_2lvl_admin['work_email'];
				$msg=sendmail($to,$subject,$body,$from); 	
			}
		}
	}
#####################################################################################################
# Function Name : downloadFile	    		                     	                            #
# Description   : Common download function to download all type files	 	  		    #
#		                      	                                                            #
# Input         : File name and file location                                                       #
# Output	: No Output 	 					   		            #
#####################################################################################################
	function downloadFile($dir="",$file_name=""){
		ob_clean();
		if(!file_exists($dir.$file_name)){			
        		print $dir.$file_name." not found";exit;
    		}
		$file_extension = strtolower(substr(strrchr($file_name,'.'),1));
		
		switch($file_extension){
			case "pdf": $ctype="application/pdf"; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpg": 
        		case "jpeg": $ctype="image/jpeg"; break;
        		case "bmp": $ctype="image/bmp"; break;
        		case "tif": $ctype="image/tif"; break;
			case "tiff": $ctype="image/tiff"; break;
        		case "txt": $ctype="text/plain";break;
        		case "css": $ctype="text/css"; break;
			case "csv": $ctype="application/csv";break;
			case "xml": $ctype="text/xml"; break;
			case "avi": $ctype="video/x-msvideo"; break;
			default: $ctype="application/force-download";
		}
		$fsize=filesize($dir.$file_name);
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: $ctype");
		header("Content-Disposition: attachment; filename=\"$file_name\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . $fsize);

		$fp = fopen($dir.$file_name, "r");
		fpassthru($fp);
	}
#####################################################################################################
# Function Name : unlinkFiles	    		                     	                            #
# Description   : Unlink all files of preview folder			 	  		    #
#		                      	                                                            #
# Input         : No Input			                                                    #
# Output	: No Output 	 					   		            #
#####################################################################################################
	function unlinkFiles(){
		for($i=0;$i<2;$i++){
			if($i==0){
				$file_arr=glob(APP_ROOT.$GLOBALS['conf']['IMAGE']['preview_orig']."*");
			}else{
				$file_arr=glob(APP_ROOT.$GLOBALS['conf']['IMAGE']['preview_thumb']."*");
			}
			if(!is_array($file_arr)){
				$file_arr=array();
			}
			$yes_time_stamp=strtotime("-2 day");
			foreach($file_arr as $val){
				$file_time_stamp=filemtime($val);
				if($file_time_stamp <= $yes_time_stamp){
					@unlink($val);
				}
			}
		}
	}
#####################################################################################################
# Function Name : _previewImage	    		                     	                            #
# Description   : During uploading image it crops the image and show the preview image 		    #
#		                      	                                                            #
# Input         : Uploaded file details		                                                    #
# Output	: No Output 	 					   		            #
#####################################################################################################
	function _previewImage(){
		if ($_FILES['image']['name']){
			$time= strtotime("now");
			$rid=$time."_";
			$uploadDir  = APP_ROOT.$GLOBALS['conf']['IMAGE']['preview_orig'];
			$thumbnailDir = APP_ROOT.$GLOBALS['conf']['IMAGE']['preview_thumb'];
			$thumb_height = $GLOBALS['conf']['IMAGE']['thumb_height'];
			$thumb_width = $GLOBALS['conf']['IMAGE']['thumb_width'];
						
			$fname = $rid.convert_me($_FILES['image']['name']);
			$file_tmp=$_FILES['image']['tmp_name'];
			
			copy($file_tmp, $uploadDir.$fname);
			$copy_thumb=copy($uploadDir.$fname, $thumbnailDir.$fname);
			$this->r = new thumbnail_manager($uploadDir.$fname,$thumbnailDir.$fname);
			$this->r->get_container_thumb($thumb_height,$thumb_width,0,0);
			ob_clean();
			print $fname;
		}
	}
}
