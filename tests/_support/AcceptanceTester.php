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

    private function login($username = AGCA_SITE_USERNAME, $password = AGCA_SITE_PASSWORD)
    {
        $I = $this;
        $I->amOnPage('/wp-login.php');
        $I->see('Username or Email Address');

        $I->submitForm('#loginform', [
            'log' => $username,
            'pwd' => $password
        ]);

        //TODO: Make it work/
        //$I->saveSessionSnapshot('login');
        //$I->loadSessionSnapshot('login');

        $I->wait(1);

        $I->see('Dashboard');
    }

    public function loginAsAdmin()
    {
        $this->login();
        return $this;
    }

    public function loginAsSubscriber()
    {
        $this->login(
            AGCA_SITE_SUBSCRIBER_USERNAME,
            AGCA_SITE_SUBSCRIBER_PASSWORD
        );
        return $this;
    }

    public function loginAsEditor()
    {
        $this->login(
            AGCA_SITE_EDITOR_USERNAME,
            AGCA_SITE_EDITOR_PASSWORD
        );
        return $this;
    }

    public function logOut(){
        $logOutLink = $this->executeJS('return jQuery("#wp-admin-bar-logout a").attr("href");');
        $this->amOnUrl($logOutLink);
        return $this;
    }

    public function checkAgcaOption($name)
    {
        try {
            if(!$this->isAgcaOptionChecked($name)){
                $this->click("#agca_form input.agca-checkbox[name=$name]:not(:checked) + div");
            }
        } catch (Exception $e) {
        }
    }

    public function isAgcaOptionChecked($name)
    {
        return (bool)$this->executeJS(
            'return jQuery("#agca_form input.agca-checkbox[name=' . $name . ']:checked").size()'
        );
    }

    public function uncheckAgcaOption($name)
    {
        try {
            if($this->isAgcaOptionChecked($name)){
                $this->click("#agca_form input.agca-checkbox[name=$name]:checked + div");
            }
        } catch (Exception $e) {
        }
    }

    public function getAGCAOptionLabel($name)
    {
        return $this->executeJS("return jQuery(\"label[for=$name]\").text();");
    }

    public function changeAgcaSelectOption($selector, $value)
    {
        $this->executeJS("jQuery(\"#agca_form $selector\").val('$value');");
    }
}
