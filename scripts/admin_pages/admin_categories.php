<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_categories_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_categories_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/admin_categories.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['categories']=$this->cObj->getSubpart($subparts['template'], '###CATEGORIES###');
$GLOBALS['TSFE']->additionalHeaderData[]='
<script src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'js/jquery.treeview/jquery.treeview.js" type="text/javascript"></script>
<link rel="stylesheet" href="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'js/jquery.treeview/jquery.treeview.css" />
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".master_categories_ul").sortable({
		cursor:"move",
		items:">li.categories_sorting",
		update: function(e, ui) {
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_categories_sorting').'";
			jQuery(this).sortable("refresh");
			sorted = jQuery(this).sortable("serialize", "id");
			jQuery.ajax({
				type:"POST",
				url:href,
				data:sorted,
				success: function(msg) {
					//do something with the sorted data
				}
			});
		}
	});
	$(".sub_categories_ul").sortable({
		cursor:"move",
		items:">li.sub_categories_sorting",
		update: function(e, ui) {
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_categories_sorting').'";
			jQuery(this).sortable("refresh");
			sorted = jQuery(this).sortable("serialize", "id");
			jQuery.ajax({
				type:"POST",
				url:href,
				data:sorted,
				success: function(msg) {
					//do something with the sorted data
				}
			});
		}
	});
	$(document).on("click", "#delete_selected_categories", function () {
		if (confirm("'.$this->pi_getLL('admin_label_delete_selected_categories').'")) {
			return true;
		} else {
			return false;
		}
	});
	$("#msAdmin_category_listing_ul").treeview({
		collapsed: true,
		animated: "medium",
		control:"#sidetreecontrol",
		persist: "location"
	});
	$(document).on("click", ".movecats", function() {
		var current_id = $(this).attr("id");
		var selectbox_id= "#" + current_id.replace("cb-", "sl-");
		var childrens = $(this).parent().find("ul>li.category > input.movecats");
		if ($(this).is(":checked")) {
			$(selectbox_id).attr("disabled", "disabled");
			if ($(childrens).length > 0) {
				$(childrens).each(function(i,v){
					var c_current_id = $(v).attr("id");
					var c_selectbox_id= "#" + c_current_id.replace("cb-", "sl-");
					$(v).attr("disabled", "disabled");
					$(c_selectbox_id).attr("disabled", "disabled");
				});
			}
		} else {
			$(selectbox_id).removeAttr("disabled");
			if ($(childrens).length > 0) {
				$(childrens).each(function(i,v){
					var c_current_id = $(v).attr("id");
					var c_selectbox_id= "#" + c_current_id.replace("cb-", "sl-");
					$(v).removeAttr("disabled");
					$(c_selectbox_id).removeAttr("disabled");
				});
			}
		}
	});
});
</script>';
$counter=0;
$categories=mslib_fe::getSubcatsOnly($this->categoriesStartingPoint, 1);
$cat_selectbox='';
$contentItem='';
foreach ($categories as $category) {
	$counter++;
	if ($category['categories_image']) {
		$image='<img src="'.mslib_befe::getImagePath($category['categories_image'], 'categories', 'normal').'" alt="'.htmlspecialchars($category['categories_name']).'">';
	} else {
		$image='<div class="no_image"></div>';
	}
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($category['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats)>0) {
		foreach ($cats as $item) {
			$where.="categories_id[".$level."]=".$item['id']."&";
			$level++;
		}
		$where=substr($where, 0, (strlen($where)-1));
		$where.='&';
	}
	$where.='categories_id['.$level.']='.$category['categories_id'];
	// get all cats to generate multilevel fake url eof
	if ($category['categories_url']) {
		$target=' target="_blank"';
		$link=$category['categories_url'];
	} else {
		$target="";
		$link='';
	}
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($category['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats)>0) {
		foreach ($cats as $tmp) {
			$where.="categories_id[".$level."]=".$tmp['id']."&";
			$level++;
		}
		$where=substr($where, 0, (strlen($where)-1));
	}
	$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
	$cat_selectbox.='<option value="'.$category['categories_id'].'" id="sl-cat_'.$category['categories_id'].'">+ '.$category['categories_name'].' (ID: '.$category['categories_id'].')</option>';
	$category_action_icon='<div class="action_icons">
	<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id']).'&action=edit_category" class="msadmin_edit_icon"><span>'.$this->pi_getLL('admin_label_link_edit').'</span></a>
	<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id'].'&action=delete_category').'" class="msadmin_delete_icon" alt="'.$this->pi_getLL('admin_label_alt_remove').'"><span>delete</span></a>
	<a href="'.$link.'" target="_blank" class="msadmin_view"><span>view</span></a>
	</div>';
	$subcat_list='';
	$dataArray=mslib_fe::getSitemap($category['categories_id'], array(), 1, 0);
	if (count($dataArray)) {
		$sub_content=mslib_fe::displayAdminCategories($dataArray, false, 0, $category['categories_id']);
		if ($sub_content) {
			$subcat_list.='<ul class="sub_categories_ul">';
			$subcat_list.=$sub_content;
			$subcat_list.='</ul>';
		}
		$cat_selectbox.=mslib_fe::displayAdminCategories($dataArray, true, 1, $category['categories_id']);
	}
	$markerArray=array();
	$markerArray['COUNTER']=$counter;
	$markerArray['EXTRA_CLASS']=(!$category['status'] ? 'msAdminCategoryDisabled' : '');
	$markerArray['CATEGORY_ID']=$category['categories_id'];
	$markerArray['CATEGORY_EDIT_LINK']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id']).'&action=edit_category';
	$markerArray['CATEGORY_NAME']=$category['categories_name'].' (ID: '.$category['categories_id'].')';
	$markerArray['CATEGORY_STATUS']=(!$category['status'] ? '(disabled)' : '');
	$markerArray['CATEGORY_ACTION_ICON']=$category_action_icon;
	$markerArray['SUB_CATEGORY_LIST']=$subcat_list;
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['categories'], $markerArray, '###|###');
}
$cat_selectbox='<select name="move_to_cat" id="move_to_cat">
<option value="0">'.$this->pi_getLL('admin_label_option_main_category').'</option>
'.$cat_selectbox.'
</select>';
$subpartArray=array();
$subpartArray['###ADMIN_CATEGORIES_HEADER###']='<h1>'.$this->pi_getLL('admin_label_categories_overview').'</h1>';
$subpartArray['###FORM_ACTION_LINK###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories&cid='.$this->get['categories_id'].'&action=move_categories');
$subpartArray['###TARGET_CATEGORIES_TREE###']=$cat_selectbox;
$subpartArray['###ADMIN_LABEL_COLLAPSE_ALL###']=$this->pi_getLL('admin_label_collapse_all');
$subpartArray['###ADMIN_LABEL_EXPAND_ALL###']=$this->pi_getLL('admin_label_expand_all');
$subpartArray['###ADMIN_LABEL_MOVE_SELECTED_CATEGORIES_TO###']=$this->pi_getLL('admin_label_move_selected_categories_to');
$subpartArray['###ADMIN_LABEL_OR###']=$this->pi_getLL('admin_label_or');
$subpartArray['###ADMIN_LABEL_BTN_MOVE###']=$this->pi_getLL('admin_label_btn_move');
$subpartArray['###ADMIN_LABEL_BTN_DELETE_SELECTED_CATEGORIES###']=$this->pi_getLL('admin_label_btn_delete_selected_categories');
$subpartArray['###CATEGORIES###']=$contentItem;
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
?>