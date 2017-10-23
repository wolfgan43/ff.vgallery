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

if(!(AREA_GROUPS_SHOW_MODIFY && AREA_USERS_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();

$gid = $_REQUEST["gid"];

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "GroupsMembers";
$oGrid->title = ffTemplate::_get_word_by_code("groups_members");
$oGrid->source_SQL = "
			            SELECT DISTINCT
							" . CM_TABLE_PREFIX . "mod_security_users.*
							, " . CM_TABLE_PREFIX . "mod_security_users.avatar AS users_avatar
							, (SELECT COUNT(*) AS count_gid
								FROM " . CM_TABLE_PREFIX . "mod_security_users_rel_groups
								WHERE 
									" . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . $db->toSql($gid, "Number") . "
									AND " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
							) AS is_member
						FROM " . CM_TABLE_PREFIX . "mod_security_users
			            WHERE 1
		                [AND][WHERE]
		                GROUP BY " . CM_TABLE_PREFIX . "mod_security_users.ID
		                [HAVING]
	                	[ORDER]";
$oGrid->order_default = "ID";
//$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
//$oGrid->record_id = "GroupsMembersModify";
//$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_delete_bt = false;
$oGrid->display_new = false;
$oGrid->addEvent("on_before_parse_row", "GroupsMembers_on_before_parse_row");


// Ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "is_member";
$oField->label = ffTemplate::_get_word_by_code("users_is_member");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no")))
                       );
$oField->src_having = true;
$oGrid->addSearchField($oField);
// Chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "register-ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->label = ffTemplate::_get_word_by_code("users_id");
$oField->order_SQL = " is_member DESC, username"; 
$oGrid->addContent($oField);

if(ENABLE_AVATAR_SYSTEM) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "users_avatar";
    $oField->label = ffTemplate::_get_word_by_code("users_avatar");
    $oField->encode_entities = false;
    $oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("users_username");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("users_email");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "is_member";
$oField->label = ffTemplate::_get_word_by_code("users_is_member");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no")))
                       );
$oGrid->addContent($oField);

if(strlen($_REQUEST["ret_url"]) && !$cm->oPage->isXHR()) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "back";
	$oButton->action_type = "gotourl";
	$oButton->url = urldecode($_REQUEST["ret_url"]);
        $oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("back");
	$oButton->parent_page = array(&$cm->oPage);
	$oGrid->addActionButton($oButton);
}

$cm->oPage->addContent($oGrid);


function GroupsMembers_on_before_parse_row($component) {
    if(isset($component->grid_fields["users_avatar"])) { 
    	if(check_function("get_user_avatar"))
    		$component->grid_fields["users_avatar"]->setValue(get_user_avatar($component->db[0]->getField("users_avatar", "Text", true), true, $component->db[0]->getField("email", "Text", true)));
/*
        $avatar = $component->db[0]->getField("users_avatar", "Text", true);
        if(strlen($avatar)
            && (substr(strtolower($avatar), 0, 7) == "http://"
                || substr(strtolower($avatar), 0, 8) == "https://"
            )
        ) {
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . $avatar . '" />');
        } else if(strlen($avatar) && file_exists(DISK_UPDIR . $avatar) && is_file(DISK_UPDIR . $avatar)) { 
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar" . $avatar . '" />');
        } else {
            $component->grid_fields["users_avatar"]->setValue('<img class="avatar" src="' . FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . CM_DEFAULT_THEME . "/images/spacer.gif" . '" />');
        }
*/
    }
}