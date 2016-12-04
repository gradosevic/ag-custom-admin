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

        $I->wait(1);

        $I->see('Dashboard');
    }

    public function checkAgcaOption($name){
        try{
            $this->click("#agca_form input.agca-checkbox[name=$name]:not(:checked) + div");
        }catch (Exception $e){}
    }

    public function isAgcaOptionChecked($name){
        return (bool) $this->executeJS(
            'return jQuery("#agca_form input.agca-checkbox[name='.$name.']:checked").size()'
        );
    }

    public function uncheckAgcaOption($name){
        try{
            $this->click("#agca_form input.agca-checkbox[name=$name]:checked + div");
        }catch (Exception $e){}
    }

    public function getAGCAOptionLabel($name){
        return $this->executeJS("return jQuery(\"label[for=$name]\").text();");
    }

    public function changeAgcaSelectOption($selector, $value){
        $this->executeJS("jQuery(\"#agca_form $selector\").val('$value');");
    }
}
