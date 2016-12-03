<?php
namespace Page;

use AcceptanceTester as AcceptanceTester;

class AGCAPage
{
    // include url of current page
    public static $URL = '/wp-admin/tools.php?page=ag-custom-admin/plugin.php#general-settings';

    /**
     * @var AcceptanceTester
     */
    private $I;

    public function __construct(AcceptanceTester $I){
        $this->I = $I;
    }

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }

    /**
     * Asserts that menu with text is visible
     */
    public function seeMenu($text){
        $this->I->see($text, '#ag_main_menu li.normal a');
    }

    public function selectedMenu($text){
        $this->I->see($text, '#ag_main_menu li.selected a');
    }

    public function amOnGeneralPage(){
        $this->I->amOnPage($this::$URL);
        $this->I->see('General Settings');
    }

    public function seeArea($text){
        $this->I->see($text, '#agca_form .ag_table_heading h3');
    }

    public function saveSettings(){
        $this->I->submitForm('#agca_form', []);
    }
}
