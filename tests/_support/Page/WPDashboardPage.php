<?php
namespace Page;

use AcceptanceTester;

class WPDashboardPage
{
    // include url of current page
    public static $URL = '/wp-admin/index.php';

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

    public function visit(){
        $this->I->amOnPage($this::$URL);
        $this->I->see('Dashboard', 'body h1:nth-child(1)');
        return $this;
    }

    public function canSeeScreenOptions($shouldSee = true){
        $this->canSeeTopRightDropdown('Screen Options', '#show-settings-link', $shouldSee);
    }

    public function canSeeHelpOptions($shouldSee = true){
        $this->canSeeTopRightDropdown('Help', '#contextual-help-link', $shouldSee);
    }

    private function canSeeTopRightDropdown($text, $selector, $shouldSee = true){
        if($shouldSee){
            $this->I->canSee($text, $selector);
        }else{
            $this->I->dontSee($text, $selector);
        }
    }
}
