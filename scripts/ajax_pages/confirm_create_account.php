<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$user=mslib_fe::getUser($this->get['tx_multishop_pi1']['hash'], 'code');
if ($user['uid'] and !$user['tx_multishop_optin_crdate']) {
	$updateArray=array();
	$updateArray['disable']=0;
	$updateArray['tx_multishop_optin_crdate']=time();
	$updateArray['tx_multishop_optin_ip']= \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR');
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$user['uid'], $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	// auto login the user
	$loginData=array(
		'uname'=>$user['username'],
		//username
		'uident'=>$user['password'],
		//password
		'status'=>'login'
	);
	$GLOBALS['TSFE']->fe_user->checkPid=0; //do not use a particular pid
	$info=$GLOBALS['TSFE']->fe_user->getAuthInfoArray();
	$user=$GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $loginData['uname']);
	$GLOBALS['TSFE']->fe_user->createUserSession($user);
	// auto login the user
	// RELOAD CART CONTENTS
	$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_cart_contents', // FROM ...
		'customer_id=\''.$user['uid'].'\' and is_checkout=0', // WHERE...
		'', // GROUP BY...
		'id desc', // ORDER BY...
		'1' // LIMIT ...
	);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	if (is_array($row) && $row['contents']) {
		$cart=unserialize($row['contents']);
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		$GLOBALS['TSFE']->storeSessionData();
		// redirect to checkout page
		$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]=checkout',1);
	} else {
		// redirect to shop
		$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid);
	}
	//hook to let other plugins further manipulate the redirect link
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationSuccesfulRedirectLinkPreProc'])) {
		$params=array(
			'updateArray'=>$updateArray,
			'user'=>$user,
			'redirect_url'=>&$redirect_url
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationSuccesfulRedirectLinkPreProc'] as $funcRef) {
			 \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	if ($redirect_url) {
		header("Location: ".$redirect_url);
	}
	exit();
} elseif ($user['uid'] and $user['tx_multishop_optin_crdate']) {
	// user is already confirmed	
	// redirect to specific page
	$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid);
	//hook to let other plugins further manipulate the redirect link
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationRepeatedRedirectLinkPreProc'])) {
		$params=array(
			'updateArray'=>$updateArray,
			'user'=>$user,
			'redirect_url'=>&$redirect_url
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationRepeatedRedirectLinkPreProc'] as $funcRef) {
			 \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	if ($redirect_url) {
		header("Location: ".$redirect_url);
	}
	exit();
}
exit();
?>