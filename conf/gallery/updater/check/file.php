<?php  
	error_reporting((E_ALL ^ E_NOTICE) | E_STRICT);
    ini_set("memory_limit", "300M"); 
    
	if(!function_exists("ffCommon_dirname")) {
		function ffCommon_dirname($path) 
		{
		    $res = dirname($path);
		    if(dirname("/") == "\\")
		        $res = str_replace("\\", "/", $res);
		    
		    if($res == ".")
		        $res = "";
		        
		    return $res;
		}
	}

	if(!defined("CM_TABLE_PREFIX")) {
		if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/cm/conf/config.php")) {
			require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/cm/conf/config.php");
		}
	}
	if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
		if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/path.php")) {
			require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/path.php");
		}
	}
	if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
        if (strpos(php_uname(), "Windows") !== false)
            $tmp_file = str_replace("\\", "/", __FILE__);
        else
            $tmp_file = __FILE__;
        
	    if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
		    $st_document_root =  $_SERVER["DOCUMENT_ROOT"];
			if (substr($st_document_root,-1) == "/")
			    $st_document_root = substr($st_document_root,0,-1);

			$st_site_path = str_replace($st_document_root, "", str_replace("/conf/gallery/updater/check/file.php", "", $tmp_file));
			$st_disk_path = $st_document_root . $st_site_path;
		} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
		    $st_document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
			if (substr($st_document_root,-1) == "/")
			    $st_document_root = substr($st_document_root,0,-1);

			$st_site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/updater/check/file.php", "", $_SERVER["SCRIPT_FILENAME"]));
			$st_disk_path = $st_document_root . str_replace($st_document_root, "", str_replace("/conf/gallery/updater/check/file.php", "", $tmp_file));
		} else {
			$st_disk_path = str_replace("/conf/gallery/updater/check/file.php", "", $tmp_file);
			$st_site_path = str_replace("/conf/gallery/updater/check/file.php", "", $_SERVER["SCRIPT_NAME"]);
		}

	    define("FF_SITE_PATH", $st_site_path);
	    define("FF_DISK_PATH", $st_disk_path);
	}

	if(!defined("MASTER_SITE")) {
		if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php")) {
			require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php");
		} else {
			die();
		}
	}

	if(!defined("FF_DATABASE_NAME")) {
		if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/db.php")) {
			require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/db.php");

			if(file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php")) {
				require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
				$db_updater =  new ffDB_Sql;
			}
		}
	} else {
		$db_updater = ffDB_Sql::factory();
	}

    if(file_exists(FF_DISK_PATH . "/ff/common.php"))
        require_once(FF_DISK_PATH . "/ff/common.php");
    
	if(MASTER_SITE == DOMAIN_INSET && (!defined("FF_DATABASE_NAME") ||  !file_exists(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php"))) {
		die("Master Site must have every system files");
	}
	
	if(!defined("DOMAIN_INSET"))
		define("DOMAIN_INSET", $_SERVER["HTTP_HOST"]);

/*
    if(substr_count(DOMAIN_INSET, ".") == 1) {
        define("DOMAIN_NAME"        , DOMAIN_INSET);
    } else {
        define("DOMAIN_NAME"        , substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1));
    }

    if(substr_count($_REQUEST["s"], ".") == 1) {
        $remote_host = $_REQUEST["s"];
    } else {
        $remote_host = substr(urldecode($_REQUEST["s"]), strpos(urldecode($_REQUEST["s"]), ".") + 1);
    }
*/
	if(strpos(strtolower(DOMAIN_INSET), "www.") === 0) {
    	define("DOMAIN_NAME"		, substr(DOMAIN_INSET, strpos(DOMAIN_INSET, ".") + 1));	
	} else {
		define("DOMAIN_NAME"		, DOMAIN_INSET);
	}

	if(defined("PRODUCTION_SITE") && strlen(PRODUCTION_SITE)) {
	    if(strpos(PRODUCTION_SITE, "www.") === 0) {
	        define("DOMAIN_SYNC_NAME"        , substr(PRODUCTION_SITE, 4));
	    } else {
	        define("DOMAIN_SYNC_NAME"        , PRODUCTION_SITE);
	    }
	} elseif(defined("DEVELOPMENT_SITE") && strlen(DEVELOPMENT_SITE)) {
	    if(strpos(DEVELOPMENT_SITE, "www.") === 0) {
	        define("DOMAIN_SYNC_NAME"        , substr(DEVELOPMENT_SITE, 4));
	    } else {
	        define("DOMAIN_SYNC_NAME"        , DEVELOPMENT_SITE);
	    }
	}    

    if(strpos($_REQUEST["s"], "www.") === 0) {
        $remote_host = substr($_REQUEST["s"], 4);
    } else {
        $remote_host = $_REQUEST["s"];
    }

	//require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))) . "/config/updater.php");
	
	if(is_object($cm)) {
		$realPathInfo = $cm->real_path_info;
		//$page_path = $cm->oPage->page_path;
	} else {
		$fftmp_ffq = false;
		if (isset($_REQUEST["_ffq_"])) // used to manage .htaccess [QSA] option, this overwhelm other options
		{
			$fftmp_ffq = true;
			$_SERVER["PATH_INFO"] = $_REQUEST["_ffq_"];
			$_SERVER["ORIG_PATH_INFO"] = $_REQUEST["_ffq_"];
		}
		else if (isset($_SERVER["ORIG_PATH_INFO"]))
			$_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];

		if (strlen($_SERVER["QUERY_STRING"]))
		{
			$fftmp_new_querystring = "";
			$fftmp_parts = explode("&", rtrim($_SERVER["QUERY_STRING"], "&"));
			foreach ($fftmp_parts as $fftmp_value)
			{
				$fftmp_subparts = explode("=", $fftmp_value);
				if ($fftmp_subparts[0] == "_ffq_")
					continue;
				if (!isset($_REQUEST[$fftmp_subparts[0]]))
					$_REQUEST[$fftmp_subparts[0]] = (count($fftmp_subparts) == 2 ? rawurldecode($fftmp_subparts[1]) : "");
				$fftmp_new_querystring .= $fftmp_subparts[0] . (count($fftmp_subparts) == 2 ? "=" . $fftmp_subparts[1] : "") . "&";
			}
			if ($fftmp_ffq)
			{
				$_SERVER["QUERY_STRING"] = $fftmp_new_querystring;
				unset($_REQUEST["_ffq_"]);
				unset($_GET["_ffq_"]);
			}
			unset($fftmp_new_querystring);
			unset($fftmp_parts);
			unset($fftmp_value);
			unset($fftmp_subparts);
		}

		// fix request_uri. can't use code above due to multiple redirects (es.: R=401 and ErrorDocument in .htaccess)
		if (strpos($_SERVER["REQUEST_URI"], "?") !== false)
		{
			$fftmp_requri_parts = explode("?", $_SERVER["REQUEST_URI"]);
			if (strlen($fftmp_requri_parts[1]))
			{
				$fftmp_new_querystring = "";
				$fftmp_parts = explode("&", rtrim($fftmp_requri_parts[1], "&"));
				foreach ($fftmp_parts as $fftmp_value)
				{
					$fftmp_subparts = explode("=", $fftmp_value);
					if ($fftmp_subparts[0] == "_ffq_")
						continue;
					$fftmp_new_querystring .= $fftmp_subparts[0] . (count($fftmp_subparts) == 2 ? "=" . $fftmp_subparts[1] : "") . "&";
				}
				
				$_SERVER["REQUEST_URI"] = $fftmp_requri_parts[0] . "?" . $fftmp_new_querystring;
				
				unset($fftmp_new_querystring);
				unset($fftmp_parts);
				unset($fftmp_value);
				unset($fftmp_subparts);
			}
			unset($fftmp_requri_parts);
		}
		
        $realPathInfo = $_SERVER['PATH_INFO'];
  
		if(substr($realPathInfo, 0, 1) !== "/" 
			&& array_key_exists('REDIRECT_URL', $_SERVER)
			&& FF_DISK_PATH . $_SERVER['REDIRECT_URL'] != __FILE__
		) {
            if(strpos(FF_DISK_PATH . $_SERVER['REDIRECT_URL'], __FILE__) === 0) {
				$realPathInfo    = substr(FF_DISK_PATH . $_SERVER['REDIRECT_URL'], strlen(__FILE__));
            } else {
				$realPathInfo    = $_SERVER['REDIRECT_URL'];
            }

            $arr_query_string = explode("&", $_SERVER['REDIRECT_QUERY_STRING']);
            if(is_array($arr_query_string) && count($arr_query_string)) {
                foreach($arr_query_string AS $arr_query_string_value) {
                    $arr_query_string_data = explode("=", $arr_query_string_value);
                    if(is_array($arr_query_string_data) && count($arr_query_string_data)) {
                        $_REQUEST[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
                        $_GET[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
                    }
                }
            }
        }

		//$page_path = "/conf/gallery/updater/check";
	}

	if(strpos($realPathInfo, $_SERVER["SCRIPT_NAME"]) === 0)
		$realPathInfo = substr($realPathInfo, strlen($_SERVER["SCRIPT_NAME"]));
	
	$sync = false;
	$sync_rev = false;
    if(strpos($realPathInfo, "/sync") === 0) {
		$realPathInfo = substr($realPathInfo, strlen("/sync"));

		if(defined("DOMAIN_SYNC_NAME"))
			$sync = true;

		if(DOMAIN_SYNC_NAME == $remote_host)
			$sync_rev = true;
    }
    
    if(strlen($remote_host)) {
        if(MASTER_SITE == DOMAIN_INSET 
        	|| (MASTER_SITE != DOMAIN_INSET && is_dir(FF_DISK_PATH . "/conf/gallery/mc") && DOMAIN_NAME != $remote_host)
        ) {
            define("MASTER_CONTROL", true);

            $sSQL = "SELECT IF(expiration_date >= CURDATE() OR expiration_date = '0000-00-00', 1, 0) AS date_check
                        , " . CM_TABLE_PREFIX . "mod_security_domains.nome
                        , " . CM_TABLE_PREFIX . "mod_security_domains.ID
                    FROM " . CM_TABLE_PREFIX . "mod_security_domains 
                    WHERE FIND_IN_SET(" . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ", ip_address)";
            $db_updater->query($sSQL);
            if($db_updater->nextRecord()) {
            	$denied_check = "different_host";
                do {
                    $real_remote_host = $db_updater->getField("nome", "Text", true);
                    if($real_remote_host == $remote_host) {
                        if($db_updater->getField("date_check", "Number", true)) {
                            $ID_domain = $db_updater->getField("ID", "Number", true);
                            require_once(ffCommon_dirname(__FILE__) . "/manifesto.php");
                            
                            if(is_array($manifesto) && count($manifesto)) {
                                $str_manifesto = "";
                                foreach($manifesto AS $manifesto_key => $manifesto_value) {
                                    if(strlen($manifesto_key)) {
                                        if(strlen($str_manifesto))
                                            $str_manifesto .= ",";
                                        
                                        $str_manifesto .= "'" . $manifesto_key . "'";
                                    }
                                }
                                if(strlen($str_manifesto)) {
                                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains_fields.*
                                            FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_updater->toSql($ID_domain, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field IN (" . $str_manifesto . ")";
                                    $db_updater->query($sSQL);
                                    if($db_updater->nextRecord()) {
                                        do {
                                            $manifesto[$db_updater->getField("field", "Text", true)]["enable"] = $db_updater->getField("value", "Number", true);
                                        } while($db_updater->nextRecord());
                                    }
                                }

                                foreach($manifesto AS $manifesto_key => $manifesto_value) {
                                    if(!$manifesto_value["enable"]) {
                                        if(is_array($manifesto_value["path"])) {
                                            foreach($manifesto_value["path"] AS $path_value) {
                                                $fs_master_exclude[$path_value] = true;
                                            }
                                        } else {
                                            if(strlen($manifesto_value["path"])) {
                                                $fs_master_exclude[$manifesto_value["path"]] = true;
                                            }
                                        }
                                    }
                                }
                            }

                            $denied_check = false;
                        } else {
                            $denied_check = "expire_date";
                        }
                        break;
                    }
                } while($db_updater->nextRecord());
                
                if($denied_check == "different_host") {
                	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.*
                			FROM " . CM_TABLE_PREFIX . "mod_security_domains
                			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql($remote_host);
                	$db_updater->query($sSQL);
                	if($db_updater->numRows() == 1 && $db_updater->nextRecord()) {
                		$ID_domain = $db_updater->getField("ID", "Number", true);
                		
                		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains 
            						SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = IF(`ip_address` = ''
            									, " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
            									, CONCAT(`ip_address`, ',', " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ")
            							)
                				WHERE " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . $db_updater->toSql($ID_domain, "Number");
                		$db_updater->execute($sSQL);
                	} else {
		                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
		                        (
		                            `ID` ,
		                            `nome` ,
		                            `owner` ,
		                            `company_name` ,
		                            `type` ,
		                            `creation_date` ,
		                            `expiration_date` ,
		                            `time_zone` ,
		                            `status` ,
		                            `billing_status` ,
		                            `ip_address`
		                        ) 
		                        VALUES 
		                        (
		                            NULL , 
		                            " . $db_updater->toSql($remote_host, "Text") . ", 
		                            0, 
		                            '', 
		                            '0', 
		                            CURDATE(), 
		                            CURDATE(), 
		                            0, 
		                            0, 
		                            0, 
		                            " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
		                        )";
		                $db_updater->execute($sSQL);
					}
	                $denied_check = false;
				}
            } else {
            	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
            			FROM " . CM_TABLE_PREFIX . "mod_security_domains
            			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db_updater->toSql($remote_host, "Text") . "
            				AND (expiration_date >= CURDATE() OR expiration_date = '0000-00-00')";
            	$db_updater->query($sSQL);
            	if($db_updater->nextRecord()) {
            		$ID_domain = $db_updater->getField("ID", "Number", true);
            		
            		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains
            					SET " . CM_TABLE_PREFIX . "mod_security_domains.`ip_address` = IF(`ip_address` = ''
            								, " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . "
            								, CONCAT(`ip_address`, ',', " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . ")
            						)
            				WHERE " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . $db_updater->toSql($ID_domain, "Number");
            		$db_updater->execute($sSQL);
            	} else {
	                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
	                        (
	                            `ID` ,
	                            `nome` ,
	                            `owner` ,
	                            `company_name` ,
	                            `type` ,
	                            `creation_date` ,
	                            `expiration_date` ,
	                            `time_zone` ,
	                            `status` ,
	                            `billing_status` ,
	                            `ip_address`
	                        ) 
	                        VALUES 
	                        (
	                            NULL , 
	                            " . $db_updater->toSql($remote_host, "Text") . ", 
	                            0, 
	                            '', 
	                            '0', 
	                            CURDATE(), 
	                            CURDATE(), 
	                            0, 
	                            0, 
	                            0, 
	                            " . $db_updater->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
	                        )";
	                $db_updater->execute($sSQL);
				}
                $denied_check = false;
            }
        } else {
            if(DOMAIN_NAME == $remote_host /*&& $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]*/) {
                $denied_check = false;        
            } elseif($sync) {
            	$denied_check = false;
			} else {
                $denied_check = "different_host (" . DOMAIN_NAME . " => " . $remote_host . ", " . $_SERVER["SERVER_ADDR"] . " => " . $_SERVER["REMOTE_ADDR"] . ")";
            }
        }
    } else {
        $denied_check = "remote_host_empty";
    }

    if(!$denied_check) {
        @set_time_limit(0);   

        if($realPathInfo != "" && is_file(FF_DISK_PATH . $realPathInfo)) {
            ini_set('auto_detect_line_endings',true); 

            if(function_exists("output_header"))
                output_header(FF_DISK_PATH . $realPathInfo, "inline", $realPathInfo, "text/plain", null, null, "text/");
            //header("Content-type: text/plain");
            //header("Content-Disposition: inline filename=" . rawurlencode(basename($realPathInfo)));
            //header("Content-Length: " . filesize(FF_DISK_PATH . $realPathInfo));
            readfile(FF_DISK_PATH . $realPathInfo);
            exit;
        } else {
            $mode = $_REQUEST["mode"];  
            
            $fs_exclude = array();
            
            require(ffCommon_dirname(__FILE__) . "/exclude_fs.php");

			if(strpos(ffCommon_dirname(ffCommon_dirname(__FILE__)), FF_DISK_PATH) === false) {  
				die("File Config Path Corrupted: Wrong[" . FF_DISK_PATH . "] => ChangeIn[" . ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "]");
			} else {
	            if($realPathInfo != "" && $realPathInfo == str_replace(FF_DISK_PATH, "", ffCommon_dirname(ffCommon_dirname(__FILE__)))) {
	                $str_root_path = ffCommon_dirname(ffCommon_dirname(__FILE__));
	            } else {
	                $str_root_path = FF_DISK_PATH . $realPathInfo;
	                $fs_exclude[str_replace(FF_DISK_PATH, "", ffCommon_dirname(ffCommon_dirname(__FILE__)))] = true;    
	            }
			}
            if(is_array($fs_master_exclude) && count($fs_master_exclude)) {
                $fs_exclude = array_merge($fs_exclude, $fs_master_exclude);
            }
            
            $fs = get_fs($str_root_path, $fs_exclude);
            ksort($fs);
            reset($fs);

            if($mode == "compact") 
                    echo md5(json_encode($fs));
                else
                    echo json_encode($fs);
            exit;
        }
    } else {
        die($denied_check);
    }

    function get_fs($absolute_path, $fs_exclude = NULL) {
        static $fs = array();
        
        $relative_path = str_replace(FF_DISK_PATH, "", $absolute_path);
        
        if (is_dir($absolute_path)) {
            if ($handle = opendir($absolute_path)) {
                while (false !== ($file = readdir($handle))) { 
                    if ($file != "." && $file != ".." && $file != ".svn" && $file != CM_SHOWFILES_THUMB_PATH && $file != ".thumbs") { 
                    	if(array_key_exists($relative_path . "/" . $file, $fs_exclude)
                    		&& !is_array($fs_exclude[$relative_path . "/" . $file])
                    		&& $fs_exclude[$relative_path . "/" . $file] == true
                    	) {
							continue;
                    	}
                    	if(array_key_exists($relative_path, $fs_exclude)
                    		&& is_array($fs_exclude[$relative_path])
                    		&& (
                    			(
                    				array_key_exists($file, $fs_exclude[$relative_path])
                    				&& (!is_array($fs_exclude[$relative_path][$file]) && $fs_exclude[$relative_path][$file] == true)
                    			)
                    			||
                    			!array_key_exists($file, $fs_exclude[$relative_path])
                    		)
                    	) {
							continue;	
                    	}
                        if (is_dir($absolute_path . "/" . $file)) {
                            $fs[$relative_path . "/" . $file] = "-1";
                            get_fs($absolute_path . "/" . $file, $fs_exclude);
                        } else {
                            if(is_file($absolute_path . "/" . $file)) {
                                $fs[$relative_path . "/" . $file] = filesize($absolute_path . "/" . $file);
                            }
                        }
                    }
                }
            }
        } else {
            if(is_file($absolute_path)) {
                $fs[$relative_path . "/" . $file] = filesize($absolute_path . "/" . $file);
            }
        }
        return $fs;
    }
?>
