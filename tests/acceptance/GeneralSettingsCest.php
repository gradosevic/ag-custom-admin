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

        //set new value
        $I->selectOption(GeneralPage::$capabilityLabel, 'create_users');
        $I->see('create_users', GeneralPage::$capabilitySelected);
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->see('create_users', GeneralPage::$capabilitySelected);

        //Return to default
        $I->selectOption(GeneralPage::$capabilityLabel, 'edit_dashboard');
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->see('edit_dashboard', GeneralPage::$capabilitySelected);

        //TODO: Test if this actually affects user that does not have that capability. Login with subscriber



    }

    public function test_screen_options(AcceptanceTester $I){
        $page = new GeneralPage($I);
        $page->amOnGeneralPage();

        //Toggle hiding OFF
        $I->selectOption('agca_screen_options_menu', false);
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->selectOption('agca_screen_options_menu', false);

        $dashboardPage = new WPDashboardPage($I);
        $dashboardPage->amOnDashboardPage();
        $dashboardPage->canSeeScreenOptions();

        //Toggle hiding ON;
        $page->amOnGeneralPage();
        $I->selectOption('agca_screen_options_menu', true);
        $page->saveSettings();
        $page->amOnGeneralPage();
        $I->selectOption('agca_screen_options_menu', true);

        $dashboardPage->amOnDashboardPage();

        //TODO: Does not work. Enable webdriver instead of PHPBrowser
        //$dashboardPage->canSeeScreenOptions(false);
    }
}
