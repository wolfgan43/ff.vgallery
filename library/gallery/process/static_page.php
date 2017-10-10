<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function process_static_page($static_type, $static_value, $user_path, &$layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $block = array();

    $settings_path = $globals->settings_path;
    $theme = $cm->oPage->getTheme();
    
    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
    
    if(strlen($layout_settings["AREA_STATIC_PLUGIN"]))
	    setJsRequest($layout_settings["AREA_STATIC_PLUGIN"]);


    
    if($static_type == "STATIC_PAGE_BY_DB") {
        $tpl = ffTemplate::factory(get_template_cascading($user_path, "draft.html"));
        $tpl->load_file("draft.html", "main");

        $db = ffDB_Sql::factory();
        
        $sSQL = "SELECT 
                    drafts.ID AS ID_draft
                    , drafts.name AS draft_name
                    , drafts_rel_languages.title
                    , drafts_rel_languages.value
                    , drafts.owner AS owner 
                FROM drafts_rel_languages
                    INNER JOIN drafts ON drafts.ID = drafts_rel_languages.ID_drafts
                    INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = drafts_rel_languages.ID_languages
                WHERE drafts.ID = " . $db->toSql($static_value, "Number") . "
                    AND " . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_INSET, "Text") . "
                    AND drafts.ID_domain = " . $db->toSql($globals->ID_domain, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
        	$block["class"]["default"] = ffCommon_url_rewrite($db->getField("draft_name", "Text", true));

        	$ID_draft = $db->getField("ID_draft", "Number", true);
        	
            set_cache_data("D", $ID_draft);
            //$globals->cache["data_blocks"]["DV" . "0" . "-" . $ID_draft] = $ID_draft;
            $static_name = $db->getField("draft_name", "Text", true);
        	$static_title = $db->getField("title", "Text", true);
        	$owner = $db->getField("owner", "Number", true);

        	if($owner == get_session("UserNID")) {
				$is_owner = true;
        	} else {
				$is_owner = false;
        	}
			
    		check_function("set_generic_tags");
	        $draft_value = set_generic_tags($db->getField("value")->getValue(), $settings_path);

			$tpl_draft = ffTemplate::factory(null);
			$tpl_draft->load_content($draft_value, "Main");
	        
            $tpl->set_var("content", ($layout_settings["AREA_STATIC_TITLE_HTMLTAG"] ? '<' . $layout_settings["AREA_STATIC_TITLE_HTMLTAG"] .'>' . ffTemplate::_get_word_by_code($unic_id . "_title") . '</' . $layout_settings["AREA_STATIC_TITLE_HTMLTAG"]. '>' : "") . $tpl_draft->rpparse("Main", false));
            
            set_cache_data("D", $static_value);
            //$globals->cache["data_blocks"]["DV" . "0" . "-" . $static_value] = $static_value;        
        } else {
            $tpl->set_var("content", "");
            $strError = ffTemplate::_get_word_by_code("static_page_nopage_db");
        }
    } elseif($static_type == "STATIC_PAGE_BY_FILE") {
		$block["class"]["default"] = ffCommon_url_rewrite(ffGetFilename($static_value));

    	check_function("set_generic_tags");
        $static_value = set_generic_tags($static_value, $settings_path);

        if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . ffCommon_dirname($static_value) . "/" . LANGUAGE_INSET . "/" . basename($static_value))) {

            if($layout_settings["AREA_STATIC_TPL_ORIGINAL"]) {
                $buffer = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . ffCommon_dirname($static_value) . "/" . LANGUAGE_INSET . "/" . basename($static_value));
                return $buffer;
            }

            $tpl = ffTemplate::factory(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . ffCommon_dirname($static_value) . "/" . LANGUAGE_INSET); 
            $tpl->load_file(basename($static_value), "main");
        } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . $static_value)) {
            if($layout_settings["AREA_STATIC_TPL_ORIGINAL"]) {
                $buffer = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . $static_value);
                return $buffer;
            }
            
            $tpl = ffTemplate::factory(FF_DISK_PATH . FF_THEME_DIR . "/" . $theme . "/" . GALLERY_TPL_PATH . ffcommon_dirname($static_value));
            $tpl->load_file(basename($static_value), "main");
        } else {
            $tpl = ffTemplate::factory(get_template_cascading($user_path, "draft.html"));
            $tpl->load_file("draft.html", "main");
            $tpl->set_var("content", "");
            $strError = ffTemplate::_get_word_by_code("static_page_nopage_file");
        }
        
        set_cache_data("T", basename($static_value));
		//$globals->cache["data_blocks"]["TV" . "0" . "-" . basename($static_value)] = basename($static_value);        
    } 
    
    
    if(is_array($globals->request) && count($globals->request)) {
	    foreach($globals->request AS $request_key => $request_value) {
			$tpl->set_var($request_key, $_GET[$request_key]);
			
			$tpl->set_var("current:" . $request_key . "=" . $_GET[$request_key], ' class="' . cm_getClassByFrameworkCss("current", "util"). '"');
			$tpl->set_var("current-class:" . $request_key . "=" . $_GET[$request_key], cm_getClassByFrameworkCss("current", "util"));
			$tpl->set_var("selected:" . $request_key . "=" . $_GET[$request_key], ' selected="selected"');
			$tpl->set_var("checked:" . $request_key . "=" . $_GET[$request_key], ' checked="checked"');
	    }
   }

    /**
     * Admin Father Bar
     */
    if (
        AREA_DRAFT_SHOW_MODIFY
        || AREA_DRAFT_SHOW_DELETE
        || AREA_PROPERTIES_SHOW_MODIFY
        || AREA_ECOMMERCE_SHOW_MODIFY
        || AREA_LAYOUT_SHOW_MODIFY
        || AREA_SETTINGS_SHOW_MODIFY
        || $is_owner
    ) {
        $admin_menu["admin"]["unic_name"] = $unic_id . $static_type. "-" . $is_owner;

        if($is_owner && !AREA_SHOW_NAVBAR_ADMIN)
            $admin_menu["admin"]["title"] = ffTemplate::_get_word_by_code("static_pages_owner") . ": " . $static_title;
        else
            $admin_menu["admin"]["title"] = $layout["title"];

        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];

        if($is_owner && !AREA_SHOW_NAVBAR_ADMIN) {
            $base_path = FF_SITE_PATH . VG_SITE_DRAFT . "/modify/" . ffCommon_url_rewrite($static_name);
            $path_params = "?keys[ID]=" . $static_value;

            $admin_menu["admin"]["modify"] = $base_path . $path_params . "&owner=" . $owner;
        } elseif(AREA_DRAFT_SHOW_MODIFY) {
            if($static_type == "STATIC_PAGE_BY_DB") {
                $base_path = FF_SITE_PATH . "/restricted/draft/modify/" . ffCommon_url_rewrite($static_name);
                $path_params = "?keys[ID]=" . $static_value;
            } else {
                $base_path = FF_SITE_PATH . "/restricted/draft/html/modify";
                $path_params = "?keys[nameID]=" . ffCommon_url_rewrite(basename($static_value));
            }

            $admin_menu["admin"]["modify"] = $base_path . $path_params;
        }

        if(AREA_DRAFT_SHOW_DELETE) {
            $admin_menu["admin"]["delete"] = ffDialog(TRUE,
                "yesno",
                ffTemplate::_get_word_by_code("drafts_erase_title"),
                ffTemplate::_get_word_by_code("drafts_erase_description"),
                "--returl--",
                $base_path . $path_params . "&ret_url=" . "--encodereturl--" . "&DraftModify_frmAction=confirmdelete",
                FF_SITE_PATH . VG_SITE_DRAFT . "/dialog");
        }

        if(AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(AREA_ECOMMERCE_SHOW_MODIFY) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(AREA_LAYOUT_SHOW_MODIFY) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(AREA_SETTINGS_SHOW_MODIFY) {
            $admin_menu["admin"]["setting"] = "";//$layout["type"];
        }

        $admin_menu["sys"]["path"] = $user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
    }

	/**
	* Process Block Header
	*/
  //  $admin_menu = null;
    if(check_function("set_template_var")) {
        $tpl = set_template_var($tpl);
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl, $block);
    }
	
    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    } else {
        $tpl->set_var("SezError", "");
    }
	/*
	if(is_array($tpl->DVars) && $tpl->DVars["real_father"]) {
		return array("content" => '<div class="block' . $block_layout_class . (is_array($layout["class"]) ? " " . implode(" ", $layout["class"]) : "") . $fixed_class . ($static_class ? " " . trim($static_class, "-") : "") . '" id="' . ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id)) . '"' . $block_properties . '>' . $tpl->rpparse("main", false) . '</div>');
	} else {
    	return array("content" => $tpl->rpparse("main", false));
	}*/
	
	return array("content" => $block["tpl"]["header"] . $tpl->rpparse("main", false) . $block["tpl"]["footer"]);
}
