<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        $oPage->tplAddMeta("p:domain_verify", $actual_srv["code"]);
    }
