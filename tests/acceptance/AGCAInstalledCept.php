<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Make sure that AGCA is installed and activated');
$I->login();
$I->amOnPage('/wp-admin/tools.php?page=ag-custom-admin/plugin.php');
$I->see('AG Custom Admin Settings');
