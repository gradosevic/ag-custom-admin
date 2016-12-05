<?php
namespace Page;

use AcceptanceTester as AcceptanceTester;

class AGCAPage
{
    // include url of current page
    public static $URL = '/wp-admin/tools.php?page=ag-custom-admin/plugin.php#general-settings';
    public static $PAGE_TITLE = 'General Settings';

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
        return $this;
    }

    public function selectedMenu($text){
        $this->I->see($text, '#ag_main_menu li.selected a');
        return $this;
    }

    public function visit(){
        $this->I->amOnPage($this::$URL);
        $this->I->see($this::$PAGE_TITLE);
        return $this;
    }

    public function seeArea($text){
        $this->I->see($text, '#agca_form .ag_table_heading h3');
        return $this;
    }

    public function saveSettings(){
        $this->I->submitForm('#agca_form', []);
        return $this;
    }

    public function agcaOptionChecked($name){
        return $this->I->executeJS("return jQuery('[name=$name]:checked').size()");
    }

    public function getAgcaSelectedOption($selector){
        return $this->I->executeJS("return jQuery(\"#agca_form $selector\").val();");
    }

    /**
     * Prepares options before testing and persists them in database
     * @param array $options
     */
    public function prepareAgcaOptions($options = []){
        $I = $this->I;

        foreach($options as $key=>$option){
            if(is_array($option)){ //selectbox or something else
                switch($option['type']){
                    case 'select':
                        $I->changeAgcaSelectOption($key, $option['value']);
                    default:;
                }
            }else if(is_bool($option)){ //checkbox
                if($option){
                    $I->checkAgcaOption($key);
                }else{
                    $I->uncheckAgcaOption($key);
                }
            }else{ //text
                //TODO: Implement text options
            }
        }

        $this->saveSettings()->visit();

        //Check if options are persisted correctly
        foreach($options as $key=>$option){
            if(is_array($option)){ //select or something else
                switch($option['type']){
                    case 'select':
                        $I->assertEquals(
                            $option['value'],
                            $this->getAgcaSelectedOption($key)
                        );
                    default:;
                }
            }else if(is_bool($option)){ //checkbox
                $isOptionChecked = $I->isAgcaOptionChecked($key);
                if($option){
                    $I->assertTrue($isOptionChecked);
                }else{
                    $I->assertFalse($isOptionChecked);
                }
            }else{ //text
                //TODO: Implement text options
            }
        }
    }
}
