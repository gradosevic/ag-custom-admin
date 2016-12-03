<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function login()
    {
        $I = $this;
        $I->amOnPage('/wp-login.php');
        $I->see('Username or Email Address');
        $I->submitForm('#loginform', [
            'log' => AGCA_SITE_USERNAME,
            'pwd' => AGCA_SITE_PASSWORD
        ]);

        //TODO: Make it work/
        //$I->saveSessionSnapshot('login');
        //$I->loadSessionSnapshot('login');

        $I->see('Dashboard');
    }
}
