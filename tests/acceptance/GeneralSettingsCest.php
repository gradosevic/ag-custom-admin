<?php

use Page\GeneralPage;
use Page\WPDashboardPage;

class GeneralSettingsCest
{
    /**
     * @var GeneralPage
     */
    private $generalPage;

    public function _before(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $this->generalPage = new GeneralPage($I);
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function test_general_settings_shows_up(AcceptanceTester $I)
    {
        $this->generalPage->visit();
    }

    public function test_main_menu(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $page = $this->generalPage->visit();

        // Assertions ------------------------------------------------

        $page
            ->selectedMenu('General')
            ->seeMenu('Admin Bar')
            ->seeMenu('Footer')
            ->seeMenu('Dashboard')
            ->seeMenu('Login Page')
            ->seeMenu('Admin Menu')
            ->seeMenu('Colorizer')
            ->seeMenu('Advanced')
            ->seeMenu('Themes')
            ->seeMenu('Upgrade');
    }

    public function test_areas(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $page = $this->generalPage->visit();

        // Assertions ------------------------------------------------

        $page
            ->seeArea('Pages')
            ->seeArea('Security')
            ->seeArea('Feedback and Support');
    }

    public function test_feedback_and_support(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $this->generalPage->visit();

        // Assertions ------------------------------------------------

        $I->see('Idea for improvement');
        $I->see('Report an issue');
        $I->see('Idea for admin theme');
        $I->see('Add a Review on WordPress.org');
        $I->see('Visit our support site');
        $I->see('Donate');
        $I->see('Upgrade to Cusmin');

        //TODO: Test click on links open link in a new tab
    }

    public function test_capability_field(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $dashboardPage = new WPDashboardPage($I);
        $page = $this->generalPage->visit();

        // Prerequisites -------------------------------------------

        $page->prepareAgcaOptions([
            GeneralPage::$helpMenuOption => true,
            GeneralPage::$excludeAdministratorOption =>true,
            GeneralPage::$capabilityField => [
                'type' => 'select',
                'value' => GeneralPage::$capabilityEditPosts
            ]
        ]);

        // Assertions ------------------------------------------------

        //EDITOR SHOULD NOT SEE THE CHANGES
        $I->logOut()->loginAsEditor();
        $dashboardPage->visit()->canSeeHelpOptions();

        //SUBSCRIBER SHOULD SEE THE CHANGES
        $I->logOut()->loginAsSubscriber();
        $dashboardPage->visit()->canSeeHelpOptions(false);


        $I->logOut()->loginAsAdmin();

        $page->visit();

        //Return to default capability value
        $page->prepareAgcaOptions([
            GeneralPage::$capabilityField => [
                'type' => 'select',
                'value' => GeneralPage::$capabilityEditDashboard
            ]
        ]);
    }

    public function test_exclude_administrator(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $dashboardPage = new WPDashboardPage($I);
        $page = $this->generalPage->visit();

        $option = GeneralPage::$excludeAdministratorOption;
        $label = GeneralPage::$excludeAdministratorOptionLabel;


        // Prerequisites -------------------------------------------

        $page->prepareAgcaOptions([
            GeneralPage::$helpMenuOption => true,
            GeneralPage::$capabilityField => [
                'type' => 'select',
                'value' => GeneralPage::$capabilityEditDashboard
            ]
        ]);

        // Assertions ------------------------------------------------

        //Assert label is correct
        $I->assertEquals($label, $I->getAGCAOptionLabel($option));

        //Toggle OFF: Administrator is not excluded, he should see customizations
        $page->prepareAgcaOptions([ $option => false ]);
        $dashboardPage->visit()->canSeeHelpOptions(false);

        $page->visit();

        //Toggle ON: Administrator is excluded, customizations should not affect him
        $page->prepareAgcaOptions([ $option => true ]);
        $dashboardPage->visit()->canSeeHelpOptions();

        //Revert back this option to defaults:
        $page->visit();
        $page->prepareAgcaOptions([ $option => false ]);
    }

    public function test_help_menu(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $dashboardPage = new WPDashboardPage($I);
        $page = $this->generalPage->visit();

        $option = GeneralPage::$helpMenuOption;
        $label = GeneralPage::$helpMenuOptionLabel;

        // Assertions ------------------------------------------------

        //Assert label is correct
        $I->assertEquals($label, $I->getAGCAOptionLabel($option));

        //Toggle  OFF
        $page->prepareAgcaOptions([ $option => false ]);
        $dashboardPage->visit()->canSeeHelpOptions();

        //Toggle ON;
        $page->visit();

        $page->prepareAgcaOptions([ $option => true ]);
        $dashboardPage->visit()->canSeeHelpOptions(false);
    }

    public function test_screen_options(AcceptanceTester $I)
    {
        // Initialization -----------------------------------------

        $dashboardPage = new WPDashboardPage($I);
        $page = $this->generalPage->visit();

        $option = GeneralPage::$screenOption;
        $label = GeneralPage::$screenOptionLabel;

        // Assertions ------------------------------------------------

        //Assert label is correct
        $I->assertEquals($label, $I->getAGCAOptionLabel($option));

        //Toggle hiding OFF
        $page->prepareAgcaOptions([ $option => false ]);
        $dashboardPage->visit()->canSeeScreenOptions();

        $page->visit();

        //Toggle hiding ON;
        $page->prepareAgcaOptions([ $option => true ]);
        $dashboardPage->visit()->canSeeScreenOptions(false);
    }
}
