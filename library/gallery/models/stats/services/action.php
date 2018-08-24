<?php
/**
 *   VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @link https://bitbucket.org/cmsff/vgallery
 */

class statsActions
{
	const TYPE                                              = "action";

	private $device                                         = null;
	private $stats                                        	= null;
	private $services										= array(
																"nosql" 					=> null
																, "sql"						=> null
																//, "fs" 					=> null
															);
    protected $connectors										= array(
																"sql"                       => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"   			=> null
																	, "name"       			=> null
																	, "prefix"				=> "TRACE_DATABASE_"
																	, "table"               => "trace_actions"
																	, "key"                 => "ID"
																)
																, "nosql"                   => array(
																	"host"          		=> null
																	, "username"    		=> null
																	, "password"    		=> null
																	, "name"       			 => null
																	, "prefix"				=> "TRACE_MONGO_DATABASE_"
																	, "table"               => "cache_actions"
																	, "key"                 => "ID"
																	)
																, "fs"                      => array(
																	"path"                  => "/cache/actions"
																	, "name"                => "title"
                                                                )
															);
	private $struct											= array(
																"id_anagraph"				=> "number"
																, "tbl"						=> "string"
																, "name"					=> "string"
																, "smart_url"				=> "string"
																, "email"					=> "string"
																, "tel"						=> "string"
																, "src"						=> "string"
																, "url"						=> "string"
																, "tags"					=> "arrayOfNumber"
																, "uid"						=> "number"
																, "token"					=> "array"
																, "created"					=> "number"
																, "last_update"				=> "number"
																, "last_login"				=> "number"
																, "user_vars"				=> "array"
															);
    private $relationship									= array();
    private $indexes										= array();
    private $tables											= array();
    private $alias											= array();

	public function __construct($stats)
	{
        $this->stats = $stats;

        $this->stats->setConfig($this->connectors, $this->services);
	}

	public function getDevice()
	{
		return $this->device;
	}

	public function get_stats($where = null, $set = null, $fields = null)
	{
		$arrWhere = $this->normalize_params($where);
		$arrFields = $this->getUserFields($fields);
		$storage = $this->getStorage();

		$res = $storage->read($arrWhere, $arrFields);
		if($set && is_array($res["result"]) && count($res["result"]) == 1) {
			$update = $this->set_vars($set, $arrWhere, $res["result"][0]["user_vars"]);
		}

		return $res;
	}

	public function get_vars($where = null, $fields = null, $table = "user_vars") {
		$stats = $this->get_stats($where);

		if(is_array($stats["result"]) && count($stats["result"])) {
			$results = $stats["result"];
			foreach($results AS $result) {
				$key = implode("|", array_intersect_key($result, $where));

				if (is_array($fields) && count($fields)) {
					foreach ($fields AS $field) {
						if (array_key_exists($field, $result[$table])) {
							$res[$key][$field] = $result[$table][$field];
						}
					}
				} elseif (strlen($fields) && array_key_exists($fields, $result[$table])) {
					$res[$key] = $result[$table][$fields];
				} else {
					$res[$key] = $result[$table];
				}
			}
		}

		return (count($res) > 1
			? $res
			: $res[$key]
		);
	}

    /**
     * @param $set
     * @param null $where
     * @param string $table
     * @return null
     */
    public function set_vars($set, $where = null, $table = "user_vars") {
        $arrWhere 							= $this->normalize_params($where);
        if(is_array($set) && count($set)) {
            $storage 						= $this->getStorage();

            $res                            = $storage->read($arrWhere);
            $old 						    = $res["result"][0];

            if(is_array($old)) {
                $set                        = array($table => $set);
                $user_vars                  = $this->stats->normalize_fields($set, array_intersect_key($old, $set));
            }
        }

        if($user_vars && $where) {
            $user_vars["last_update"]       = time();
            $update                         = $storage->update($user_vars, $arrWhere);
        }

        return $res;
    }

	public function write_stats($insert = null, $update = null) {
		$user = $this->getUserStats();

		$this->getStorage()->write(
			(is_array($insert)
				? array_replace_recursive($user["insert"], $insert)
				: $user["insert"]
			)
			, (is_array($update)
				? array_replace_recursive($user["update"], $update)
				: $user["update"]
			)
		);
	}

    /**
     * @param $type
     * @return array
     */
    public function getStruct() {
        return array(
            "struct"                                        => $this->struct
            , "indexes"                                     => $this->indexes
            , "relationship"                                => $this->relationship
            , "table"                                       => $this->tables
            , "alias"                                       => $this->alias
            , "connectors"                                  => false
        );
    }

	private function getUserFields($fields = null) {
		if(!is_array($fields)) {
			$fields = array(
				"id_anagraph"				=> true
				, "avatar"					=> true
				, "name"					=> true
				, "smart_url"				=> true
				, "email"					=> true
				, "tel"						=> true
				, "src"						=> true
				, "tags"					=> true
				, "uid"						=> true
				, "token"					=> true
				, "created"					=> true
				, "last_update"				=> true
				, "user_vars"				=> true
			);
		}

		return $fields;
	}

	private function getUserStats()
	{
		$globals = ffGlobals::getInstance("gallery");

		//codice operativo
		$created 							= time();

		$user["insert"] = array(
			"id_anagraph" 					=> $globals->author["id"]
			, "avatar"						=> $globals->author["avatar"]
			, "name" 						=> $globals->author["name"]
			, "smart_url" 					=> $globals->author["smart_url"]
			, "email"						=> $globals->author["email"]
			, "tel"							=> $globals->author["tel"]
			, "src" 						=> $globals->author["src"]
			, "url" 						=> $globals->author["url"]
			, "tags" 						=> $globals->author["tags"]
			, "uid" 						=> $globals->author["uid"]
			, "token"						=> $globals->author["token"]
			, "created"						=> $created
			, "last_update"					=> $created
			, "last_login"					=> "0"
			, "user_vars"					=> $globals->author["user_vars"]

		);

		$user["update"]["set"] = array(
			"tags" 							=> $globals->author["tags"]
			, "token"						=> $globals->author["token"]
			, "last_update"	    			=> $created
		);

		$user["update"]["where"] = array(
			"id_anagraph" 					=> $globals->author["id"]
		);

		return $user;
	}
	/**
	 * User Stats
	 */
	private function getStorage()
	{
		$storage = Storage::getInstance($this->services, $this->getStruct());

		return $storage;
	}

	private function normalize_params($params = null) {
		if(is_array($params)) {
			$where 							= $params;
		} elseif(strlen($params)) {
			$where = array(
				"id_anagraph" 				=> $params
			);
		} else {
			$user_permission 				= cache_get_session();
			$where = array(
				"uid" 						=> $user_permission["ID"]
			);
		}

		return $where;
	}

}