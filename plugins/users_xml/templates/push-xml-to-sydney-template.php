<?php 

global $wpdb;
$users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users_xml WHERE ported_status = 0", OBJECT );
if(count($users) > 0){
	$xml = new SimpleXMLElement('<xml/>');
	$file = '/home/devcmccanada/public_html/CMC_DailyBorrowerImport';
	$sydney = $xml->addChild('sydney');
	$import = $sydney->addChild('import');
	$template = $import->addChild('template');
	$template->addAttribute('name',"Borrowers");
	$template->addAttribute('id',"Borrower");
	foreach($users as $usr){
		$id = $usr->user_id;
		$name = 'name';
		$password = $usr->password;
		$email = $usr->email;
		$billing_address_1 = $usr->billing_address_1;
		$billing_city = $usr->billing_city;
		$billing_country = $usr->billing_country;
		$billing_state = $usr->billing_state;
		$billing_postcode = $usr->billing_postcode;
		$billing_phone = $usr->billing_phone;
					$record = $template->addChild('record');
	    			$record->addAttribute('searchfield', 'ID');
	    			$record->addAttribute('searchvalue', $id);
	    				$fieldid = $record->addChild('field',"<![CDATA[$id]]>");
					    $fieldid->addAttribute('id',"ID");

					    $fieldName = $record->addChild('field',"<![CDATA[$name]]>");
					    $fieldName->addAttribute('id',"Name");

					    $fieldName = $record->addChild('field',"<![CDATA[$id]]>");
	    				$fieldName->addAttribute('id',"AuxUserID");
	    				$fieldName->addAttribute('linkfield',"~Name");

	    				$fieldName = $record->addChild('field',"<![CDATA[$password]]>");
	    				$fieldName->addAttribute('id',"Password");

	    				$fieldName = $record->addChild('field',"<![CDATA[$email]]>");
	    				$fieldName->addAttribute('id',"EmailTxt");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_address_1]]>");
	    				$fieldName->addAttribute('id',"Address1");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_city]]>");
	    				$fieldName->addAttribute('id',"City");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_country]]>");
	    				$fieldName->addAttribute('id',"Country.Code");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_state]]>");
	    				$fieldName->addAttribute('id',"StateProv.Code");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_postcode]]>");
	    				$fieldName->addAttribute('id',"ZipCode");

	    				$fieldName = $record->addChild('field',"<![CDATA[$billing_phone]]>");
	    				$fieldName->addAttribute('id',"PhoneNumber");

 





	    				
	}
	$xml->asXML($file);
	/*echo '<pre>';
	print_r($users);
	exit;*/

	$host = 'ftp.sydneyplus.com';
	$port = 21;


	$ftp = 'ftp.sydneyplus.com';

	$user = 'cmc';


	$pass = 'LePhah1u';

	$remote_file = '/dropoff/CMC_DailyBorrowerImport.xml';

	// set up basic connection
	$conn_id = ftp_connect($host,$port);

	// login with username and password
	$login_result = ftp_login($conn_id, $user, $pass);
	//echo $login_result;
	// upload a file
	$file_size = ftp_size($conn_id, $remote_file);

	if ($file_size != -1) {
	    echo "File exists";
	    ftp_delete($conn_id, $remote_file);
	}


	if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
	  	  
	} 
	// close the connection
	ftp_close($conn_id);

}
