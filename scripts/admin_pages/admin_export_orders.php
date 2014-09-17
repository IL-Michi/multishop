<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// defining the types
$array=array();
$array['orders_id']='Orders ID';
$array['orders_status']='Orders status';
$array['customer_id']='Customer ID';
$array['customer_billing_telephone']='Customer billing telephone';
$array['customer_billing_email']='Customer billing e-mail';
$array['customer_billing_name']='Customer billing name';
$array['customer_billing_address']='Customer billing address';
$array['customer_billing_city']='Customer billing city';
$array['customer_billing_zip']='Customer billing zip';
$array['customer_delivery_telephone']='Customer delivery telephone';
$array['customer_delivery_email']='Customer delivery e-mail';
$array['customer_billing_country']='Customer billing country';
$array['customer_delivery_name']='Customer delivery name';
$array['customer_delivery_address']='Customer delivery address';
$array['customer_delivery_city']='Customer delivery city';
$array['customer_delivery_zip']='Customer delivery zip';
$array['customer_delivery_country']='Customer delivery country';
$array['orders_grand_total_excl_vat']='Orders grand total (excl. vat)';
$array['orders_grand_total_incl_vat']='Orders grand total (incl. vat)';
$array['payment_status']='Orders payment status';
$array['shipping_method']='Shipping method';
$array['shipping_cost_excl_vat']='Shipping costs (excl. vat)';
$array['shipping_cost_incl_vat']='Shipping costs (incl. vat)';
$array['shipping_cost_vat_rate']='Shipping costs tax rate';
$array['payment_method']='Payment method';
$array['payment_cost_excl_vat']='Payment costs (excl. vat)';
$array['payment_cost_incl_vat']='Payment costs (incl. vat)';
$array['payment_cost_vat_rate']='Payment costs tax rate';
$array['order_products']='Order products';
/*
$array['products_id']='Products id';
$array['products_name']='Products name';
$array['products_model']='Products model';
$array['products_qty']='Products quantity';
$array['products_vat_rate']='Products vat rate';
$array['products_final_price_excl_vat']='Products price (excl. vat)';
$array['products_final_price_incl_vat']='Products price (incl. vat)';
*/
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['adminExportOrdersColtypesHook'])) {
	$params=array(
		'array'=>&$array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['adminExportOrdersColtypesHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
asort($array);
if ($_REQUEST['section']=='edit' or $_REQUEST['section']=='add') {
	if ($this->post) {
		$erno=array();
		if (!$this->post['name']) {
			$erno[]='Name is required';
		} else {
			if (!$this->post['feed_type'] and (!is_array($this->post['fields']) || !count($this->post['fields']))) {
				$erno[]='No fields defined';
			}
		}
		if (empty($this->post['visual_orders_date_from'])) {
			$this->post['orders_date_from']='';
		}
		if (empty($this->post['visual_orders_date_till'])) {
			$this->post['orders_date_till']='';
		}
		if (is_array($erno) and count($erno)>0) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		} else {
			// lets save it
			$updateArray=array();
			$updateArray['name']=$this->post['name'];
			$updateArray['status']=$this->post['status'];
			$updateArray['fields']=serialize($this->post['fields']);
			$updateArray['post_data']=serialize($this->post);
			if (is_numeric($this->post['orders_export_id'])) {
				// edit
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_export', 'id=\''.$this->post['orders_export_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// insert
				$updateArray['page_uid']=$this->showCatalogFromPage;
				$updateArray['crdate']=time();
				$updateArray['code']=md5(uniqid());
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_export', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$this->ms['show_main']=1;
		}
		header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_export_orders'));
	} else {
		if ($_REQUEST['section']=='edit' and is_numeric($this->get['orders_export_id'])) {
			$str="SELECT * from tx_multishop_orders_export where id='".$this->get['orders_export_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$feeds=array();
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$this->post=$row;
				$this->post['fields']=unserialize($row['fields']);
				// now also unserialize for the custom field
				$post_data=unserialize($row['post_data']);
				$this->post['fields_headers']=$post_data['fields_headers'];
				$this->post['fields_values']=$post_data['fields_values'];
			}
		}
	}
	if (!$this->ms['show_main']) {
		$first_order_sql="SELECT crdate from tx_multishop_orders order by orders_id asc limit 1";
		$first_order_qry=$GLOBALS['TYPO3_DB']->sql_query($first_order_sql);
		$first_order_rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($first_order_qry);
		$first_year=date('Y', $first_order_rs['crdate']);

		$content.='
		<div class="main-heading"><h2>Orders export Wizard</h2></div>
		<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" id="orders_export_form">
			<div class="account-field">
					<label>'.htmlspecialchars($this->pi_getLL('name')).'</label><input type="text" name="name" value="'.htmlspecialchars($this->post['name']).'" />
			</div>';
		// order status selectbox
		$all_orders_status=mslib_fe::getAllOrderStatus();
		$order_status_sb='<select name="order_status">
			<option value="all"'.($post_data['order_status']=='all'?' selected="selected"':'').'>'.$this->pi_getLL('all').'</option>';
		if (is_array($all_orders_status) and count($all_orders_status)) {
			foreach ($all_orders_status as $row) {
				if ($post_data['order_status']==$row['id']) {
					$order_status_sb.='<option value="'.$row['id'].'" selected="selected">'.$row['name'].'</option>'."\n";
				} else {
					$order_status_sb.='<option value="'.$row['id'].'">'.$row['name'].'</option>'."\n";
				}
			}
		}
		$order_status_sb.='</select>';
		// payment status selectbox
		$payment_status_sb='<select name="payment_status">
			<option value="all"'.($post_data['payment_status']=='all'?' selected="selected"':'').'>'.$this->pi_getLL('all').'</option>
			<option value="paid"'.($post_data['payment_status']=='paid'?' selected="selected"':'').'>'.$this->pi_getLL('paid').'</option>
			<option value="unpaid"'.($post_data['payment_status']=='unpaid'?' selected="selected"':'').'>'.$this->pi_getLL('unpaid').'</option>
			</select>';
		// order by selectbox
		$order_by_sb='<select name="order_by">
				<option value="orders_id"'.($post_data['order_by']=='orders_id'?' selected="selected"':'').'>'.$this->pi_getLL('orders_id').'</option>
				<option value="status_last_modified"'.($post_data['order_by']=='status_last_modified'?' selected="selected"':'').'>'.$this->pi_getLL('status_last_modified').'</option>
				<option value="billing_name"'.($post_data['order_by']=='billing_name'?' selected="selected"':'').'>'.$this->pi_getLL('billing_name').'</option>
				<option value="crdate"'.($post_data['order_by']=='crdate'?' selected="selected"':'').'>'.$this->pi_getLL('creation_date').'</option>
				<option value="grand_total"'.($post_data['order_by']=='grand_total'?' selected="selected"':'').'>'.$this->pi_getLL('grand_total').'</option>
				<option value="shipping_method_label"'.($post_data['order_by']=='shipping_method_label'?' selected="selected"':'').'>'.$this->pi_getLL('shipping_method_label').'</option>
				<option value="payment_method_label"'.($post_data['order_by']=='payment_method_label'?' selected="selected"':'').'>'.$this->pi_getLL('payment_method_label').'</option>
			</select>';
		// sort direction selectbox
		$sort_direction_sb='<select name="payment_status">
				<option value="desc"'.($post_data['payment_status']=='desc'?' selected="selected"':'').'>'.$this->pi_getLL('sort_direction_desc').'</option>
				<option value="asc"'.($post_data['payment_status']=='asc'?' selected="selected"':'').'>'.$this->pi_getLL('sort_direction_asc').'</option>
			</select>';
		// order type selectbox
		$order_type_sb='<select name="order_type">
				<option value="all"'.($post_data['order_type']=='desc'?' selected="selected"':'').'>'.$this->pi_getLL('orders').'</option>
				<option value="by_phone"'.($post_data['order_type']=='by_phone'?' selected="selected"':'').'>'.ucfirst(strtolower($this->pi_getLL('admin_manual_order'))).'</option>
				<option value="proposal"'.($post_data['order_type']=='proposal'?' selected="selected"':'').'>'.$this->pi_getLL('admin_proposals').'</option>
			</select>';
		$content.='
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_type')).'</label>
			'.$order_type_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_date')).'</label>
			<div class="input_label_wrapper">
				<label for="visual_orders_date_from">'.htmlspecialchars($this->pi_getLL('admin_from')).'</label>
				<input name="visual_orders_date_from" id="visual_orders_date_from" type="text" value="'.$post_data['visual_orders_date_from'].'" />
				<input name="orders_date_from" id="orders_date_from" type="hidden" value="'.$post_data['orders_date_from'].'" />
			</div>
			<div class="input_label_wrapper">
				<label for="visual_orders_date_till">'.htmlspecialchars($this->pi_getLL('admin_till')).'</label>
				<input name="visual_orders_date_till" id="visual_orders_date_till" type="text" value="'.$post_data['visual_orders_date_till'].'" />
				<input name="orders_date_till" id="orders_date_till" type="hidden" value="'.$post_data['orders_date_till'].'" />
			</div>
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_status')).'</label>
			'.$order_status_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_payment_status')).'</label>
			'.$payment_status_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_by')).'</label>
			'.$order_by_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('sort_direction')).'</label>
			'.$sort_direction_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('maximum_number_of_order_products')).'</label>
			<input type="text" name="maximum_number_of_order_products" value="'.($post_data['maximum_number_of_order_products']?$post_data['maximum_number_of_order_products']:'25').'" />
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('status')).'</label>
			<input name="status" type="radio" value="0"'.((isset($this->post['status']) and !$this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('disabled')).'
			<input name="status" type="radio" value="1"'.((!isset($this->post['status']) or $this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('enabled')).'
		</div>
		<div class="account-field hide_pf">
			<div class="hr"></div>
		</div>
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('fields')).'</label>
				<input id="add_field" name="add_field" type="button" value="'.htmlspecialchars($this->pi_getLL('add_field')).'" class="msadmin_button" />
		</div>
		<div id="admin_orders_exports_fields">';
		$counter=0;
		if (is_array($this->post['fields']) and count($this->post['fields'])) {
			foreach ($this->post['fields'] as $field) {
				$counter++;
				$content.='<div><div class="account-field"><label>'.htmlspecialchars($this->pi_getLL('type')).'</label><select name="fields['.$counter.']" rel="'.$counter.'" class="msAdminOrdersExportSelectField">';
				foreach ($array as $key=>$option) {
					$content.='<option value="'.$key.'"'.($field==$key ? ' selected' : '').'>'.htmlspecialchars($option).'</option>';
				}
				$content.='</select><input class="delete_field msadmin_button" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div>';
				// custom field
				if ($field=='custom_field') {
					$content.='<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers['.$counter.']" type="text" value="'.$this->post['fields_headers'][$counter].'" /><span class="value">Value</span><input name="fields_values['.$counter.']" type="text" value="'.$this->post['fields_values'][$counter].'" /></div>';
				}
				$content.='
				</div>';
			}
		}
		$content.='
		</div>
		<div class="account-field">
			<div class="hr"></div>
		</div>
		<div class="account-field">
				<label>&nbsp;</label>
				<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('save')).'" class="msadmin_button" /></span>
		</div>
		<input name="orders_export_id" type="hidden" value="'.$this->get['orders_export_id'].'" />
		<input name="section" type="hidden" value="'.$_REQUEST['section'].'" />
		</form>
		<script type="text/javascript">
		 $("#visual_orders_date_from").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'yy/mm/dd').'",
			altField: "#orders_date_from",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.$first_year.':'.(date('Y')+1).'"
		});
		$("#visual_orders_date_till").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'yy/mm/dd').'",
			altField: "#orders_date_till",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.$first_year.':'.(date('Y')+1).'"
		});
		jQuery(document).ready(function($) {
			jQuery("#admin_orders_exports_fields").sortable({
				cursor:     "move",
				//axis:       "y",
				update: function(e, ui) {
					jQuery(this).sortable("refresh");
				}
			});
			var counter=\''.$counter.'\';
			$("#feed_type").change(function(){
				var selected=$("#feed_type option:selected").val();
				if (selected) {
					// hide
					$(".hide_pf").hide();
				} else {
					$(".hide_pf").show();
				}
			});
			$(document).on("click", "#add_field", function(event) {
				counter++;
				var item=\'<div><div class="account-field"><label>Type</label><select name="fields[\'+counter+\']" rel="\'+counter+\'" class="msAdminOrdersExportSelectField">';
		foreach ($array as $key=>$option) {
			$content.='<option value="'.$key.'">'.htmlspecialchars($option).'</option>';
		}
		$content.='</select><input class="delete_field msadmin_button" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div></div>\';
				$(\'#admin_orders_exports_fields\').append(item);
				$(\'select.msAdminOrdersExportSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().remove();
			});
			$(\'.msAdminOrdersExportSelectField\').select2({
					width:\'650px\'
			});
			$(document).on("change", ".msAdminOrdersExportSelectField", function() {
				var selected=$(this).val();
				var counter=$(this).attr("rel");
				if(selected==\'custom_field\') {
					$(this).next().remove();
					$(this).parent().append(\'<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers[\'+counter+\']" type="text" /><span class="value">Value</span><input name="fields_values[\'+counter+\']" type="text" /></div>\');
				}
			});
		});
		</script>';
	}
} else {
	$this->ms['show_main']=1;
}
if ($this->ms['show_main']) {
	if (is_numeric($this->get['status']) and is_numeric($this->get['orders_export_id'])) {
		$updateArray=array();
		$updateArray['status']=$this->get['status'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_export', 'id=\''.$this->get['orders_export_id'].'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if (is_numeric($this->get['delete']) and is_numeric($this->get['orders_export_id'])) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders_export', 'id=\''.$this->get['orders_export_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	// show listing
	$str="SELECT * from tx_multishop_orders_export order by id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$orders=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$orders[]=$row;
	}
	if (is_array($orders) and count($orders)) {
		$content.='<div class="main-heading"><h2>'.htmlspecialchars($this->pi_getLL('admin_export_orders')).'</h2></div>
		<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">
		<tr>
			<th width="25">'.htmlspecialchars($this->pi_getLL('id')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('name')).'</th>
			<th width="100" nowrap>'.htmlspecialchars($this->pi_getLL('created')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('status')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('download')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('action')).'</th>
		</tr>
		';
		foreach ($orders as $order) {
			$order['orders_export_link_excel']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_orders_export&orders_export_hash='.$order['code'].'&format=excel';
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['ordersIterationItem'])) {
				$params=array(
					'order'=>&$order
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['ordersIterationItem'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
			$content.='
			<tr>
				<td align="right" width="25" nowrap><a href="'.$order['feed_link'].'" target="_blank">'.htmlspecialchars($order['id']).'</a></td>
				<td><a href="'.$order['orders_export_link_excel'].'" target="_blank">'.htmlspecialchars($order['name']).'</a></td>
				<td width="100" align="center" nowrap>'.date("Y-m-d", $order['crdate']).'</td>
				<td width="50">
				';
			if (!$order['status']) {
				$content.='<span class="admin_status_red" alt="Disable"></span>';
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_export_id='.$order['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
			} else {
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_export_id='.$order['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
				$content.='<span class="admin_status_green" alt="Enable"></span>';
			}
			$content.='</td>
			<td width="150">
				<a href="'.$order['orders_export_link_excel'].'" class="admin_menu">Download Order export xls</a>
			</td>
			<td width="50">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_export_id='.$order['id'].'&section=edit').'" class="admin_menu_edit">edit</a>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_export_id='.$order['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
			$content.='
			</td>
			</tr>
			';
		}
		$content.='</table>';
	} else {
		$content.='<h3>'.htmlspecialchars($this->pi_getLL('currently_there_are_no_orders_export_created')).'</h3>';
	}
	$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&section=add').'" class="msBackendButton continueState arrowRight arrowPosLeft float_right"><span>'.htmlspecialchars($this->pi_getLL('add')).'</span></a>';
}
?>