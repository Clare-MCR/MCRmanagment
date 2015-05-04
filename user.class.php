<?php
/**
 * @class User
 * @abstract The User class, controls access, user permissions and stores
 * the user's crsid
 * @description Not much more to say than the abstract really
 */

class User {
 	/**
     * @class User
     * @abstract The User class, controls access, user permissions and stores
     * the user's crsid
     * @description Not much more to say than the abstract really
     */


    # User associated variables
    private $crsid;
    private $e_view;
    private $e_book;
    private $e_adm;
    private $p_view;
    private $p_book;
    private $p_adm;
    private $type;
    private $enabled;

    private $name;
    private $exists;
	private $permissions;

	private $cra;
	private $mcr_member;
	private $associate_member;
	private $college_bill;

	private $table_name;
	private $db;
	/**
	 * Check If user exists in Database
	 * If exists get Data from Database
	 */
	public function __construct($crsid)
	{
		global $wpdb;
		$this->db = $wpdb;
		$this->crsid = $crsid;
		$this->table_name = $this->db->prefix . "mcraccess";
		if ($this->exists()) {
			$this->getSQLuserData($crsid);
			$this->has_perm();
			$this->getName();
		}

	}
	public function __destruct()
	{}
	public function __toString()
	{
		$string = $this->getValue(crsid) ;
		return $string;

	}

	# Standard get and set functions to control internal vars
    public function getValue($val)
    {
        return $this->$val;
    }

    public function setValue($val, $value)
    {
        $this->$val = $value;
    }

    # Checks whether a user has a given permission
    public function has_perm()
    {
    	if($this->getValue('enabled') && $this->getValue('p_view')) {
    		$this->setValue('permissions',TRUE);
    		return TRUE;
    	} else {
    		$this->setValue('permissions',FALSE);
    		return FALSE;
    	}
    }

    # Checks whether the user exists already.
    public function getSQLuserData($crsid)
    {
    	$user_count = $this->db->get_var( $this->db->prepare("SELECT COUNT(crsid) FROM $this->table_name WHERE crsid = %s ", $this->crsid));
    	if ($user_count > 1){
			echo "User Duplicated in database";
			die;
    	}
    	$get_user = $this->db->get_row($this->db->prepare("SELECT e_view,e_book,e_adm,p_view, p_book, p_adm,
								mcr_member, associate_member, cra,
								college_bill, type, enabled FROM $this->table_name WHERE crsid = %s ", $this->crsid), ARRAY_A);
    	foreach($get_user as $key => $value) {
        	$this->$key = (bool)$value;
    		}
    	}
	//Lookup user name
	public function getName()
		{
			$ds = ldap_connect("ldap.lookup.cam.ac.uk");
    		$lsearch = ldap_search($ds, "ou=people,o=University of Cambridge,dc=cam,dc=ac,dc=uk", "uid=" . $this->crsid. "");
    		$info = ldap_get_entries($ds, $lsearch);
			if (isset($info[0]["cn"])){
				$this->name = $info[0]["cn"][0];
			};
			if ($this->name == "") {
            		$this->name = $this->crsid;
    		}
		}

	public function exists()
	{
		$user_count = $this->db->get_var( $this->db->prepare("SELECT COUNT(crsid) FROM $this->table_name WHERE crsid = %s ", $this->crsid));
		if ($user_count >= 1) {
			$this->exists = TRUE;
			return True;
		} else {
			$this->exists = FALSE;
			$this->setDefaults();
			return False;
		}
    }

 	public function commit() {


 		if ($this->exists) {
 			//echo "UPDATE";//debug
 			$this->db->update($this->table_name,
 				array(
 					'e_view'=>$this->e_view,
 					'e_book'=>$this->e_book,
 					'e_adm'=>$this->e_adm,
 					'p_view'=>$this->e_view,
 					'p_book'=>$this->p_book,
 					'p_adm'=>$this->p_adm,
 					'enabled'=>$this->enabled,
 					'type'=>$this->type,
 					'cra'=>$this->cra,
 					'mcr_member'=>$this->mcr_member,
 					'associate_member'=>$this->associate_member,
 					'college_bill'=>$this->college_bill
 				),
 				array(
 					'crsid'=>$this->crsid
 				),'%d','%s');
 		} else {
 			//echo "INSERT";//debug
 			$this->db->insert($this->table_name,
 				array(
 					'crsid'=>$this->crsid,
 					'e_view'=>$this->e_view,
 					'e_book'=>$this->e_book,
 					'e_adm'=>$this->e_adm,
 					'p_view'=>$this->e_view,
 					'p_book'=>$this->p_book,
 					'p_adm'=>$this->p_adm,
 					'enabled'=>$this->enabled,
 					'type'=>$this->type,
 					'cra'=>$this->cra,
 					'mcr_member'=>$this->mcr_member,
 					'associate_member'=>$this->associate_member,
 					'college_bill'=>$this->college_bill
 				),
 				array('%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d'));
 		}
 	}

 	public function deleteUser()
 		{
 			//IF exist
 			if ($this->exists) {
 				$this->db->delete($this->table_name,array('crsid'=>$this->crsid),'%s');
 				}
 		}

	public function setDefaults() {
 		$this->e_view = TRUE;
 		$this->e_book = TRUE;
 		$this->e_adm = FALSE;
 		$this->p_view = TRUE;
 		$this->p_book = TRUE;
 		$this->p_adm = FALSE;
 		$this->enabled = TRUE;
 		$this->type = TRUE;
 		$this->cra = FALSE;
 		$this->mcr_member = TRUE;
 		$this->associate_member = FALSE;
 		$this->college_bill = TRUE;
 	}

}
?>