<?php

use Page\GeneralPage;
use Page\WPDashboardPage;

class GeneralSettingsCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->login();
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function test_general_settings_shows_up(AcceptanceTester $I)
    {
        (new GeneralPage($I))->amOnGeneralPage();
    }

    public function test_main_menu(AcceptanceTester $I){
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();

        $page->selectedMenu('General');
        $page->seeMenu('Admin Bar');
        $page->seeMenu('Footer');
        $page->seeMenu('Dashboard');
        $page->seeMenu('Login Page');
        $page->seeMenu('Admin Menu');
        $page->seeMenu('Colorizer');
        $page->seeMenu('Advanced');
        $page->seeMenu('Themes');
        $page->seeMenu('Upgrade');
    }

    public function test_areas(AcceptanceTester $I){
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();
        $page->seeArea('Pages');
        $page->seeArea('Security');
        $page->seeArea('Feedback and Support');
    }

    public function test_feedback_and_support(AcceptanceTester $I){
        (new GeneralPage($I))->amOnGeneralPage();

        $I->see('Idea for improvement');
        $I->see('Report an issue');
        $I->see('Idea for admin theme');
        $I->see('Add a Review on WordPress.org');
        $I->see('Visit our support site');
        $I->see('Donate');
        $I->see('Upgrade to Cusmin');

        //TODO: Test click on links open link in a new tab
    }

    public function test_capability_field(AcceptanceTester $I){
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();

        $editDashboardOption = 'edit_dashboard';
        $createUsersOption = 'create_users';

        //set new value
        $I->changeAgcaSelectOption(GeneralPage::$capabilityField, $createUsersOption);
        $I->assertEquals($createUsersOption, $page->getAgcaSelectedOption(GeneralPage::$capabilityField));
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->assertEquals($createUsersOption, $page->getAgcaSelectedOption(GeneralPage::$capabilityField));

        //Return to default
        $I->changeAgcaSelectOption(GeneralPage::$capabilityField, $editDashboardOption);
        $I->assertEquals($editDashboardOption, $page->getAgcaSelectedOption(GeneralPage::$capabilityField));
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->assertEquals($editDashboardOption, $page->getAgcaSelectedOption(GeneralPage::$capabilityField));

        //TODO: Test if this actually affects user that does not have that capability. Login with subscriber
    }

    public function test_help_menu(AcceptanceTester $I)
    {
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();

        $option = 'agca_help_menu';
        $label = '"Help" menu';

        //Assert label is correct
        $I->assertEquals($label, $I->getAGCAOptionLabel($option));

        //Toggle hiding OFF
        $I->uncheckAgcaOption($option);
        $page->saveSettings();

        $page->amOnGeneralPage();
        $I->assertFalse($I->isAgcaOptionChecked($option));

        $dashboardPage = new WPDashboardPage($I);
        $dashboardPage->amOnDashboardPage();
        $dashboardPage->canSeeHelpOptions();

        //Toggle hiding ON;
        $page->amOnGeneralPage();
        $I->checkAgcaOption($option);
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->assertTrue($I->isAgcaOptionChecked($option));

        $dashboardPage->amOnDashboardPage();
        $dashboardPage->canSeeHelpOptions(false);
    }

    public function test_screen_options(AcceptanceTester $I){
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();

        $option = 'agca_screen_options_menu';
        $label = '"Screen Options" menu';

        //Assert label is correct
        $I->assertEquals($label, $I->getAGCAOptionLabel($option));

        //Toggle hiding OFF
        $I->uncheckAgcaOption($option);
        $page->saveSettings();

        $page->amOnGeneralPage();
        $I->assertFalse($I->isAgcaOptionChecked($option));

        $dashboardPage = new WPDashboardPage($I);
        $dashboardPage->amOnDashboardPage();
        $dashboardPage->canSeeScreenOptions();

        //Toggle hiding ON;
        $page->amOnGeneralPage();
        $I->checkAgcaOption($option);
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->assertTrue($I->isAgcaOptionChecked($option));

        $dashboardPage->amOnDashboardPage();
        $dashboardPage->canSeeScreenOptions(false);
    }
}
