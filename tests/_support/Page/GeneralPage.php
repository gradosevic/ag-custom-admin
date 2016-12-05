<?php
namespace Page;

class GeneralPage extends AGCAPage
{
    public static $helpMenuOption = 'agca_help_menu';
    public static $helpMenuOptionLabel = '"Help" menu';

    public static $screenOption = 'agca_screen_options_menu';
    public static $screenOptionLabel = '"Screen Options" menu';

    public static $capabilityField = 'select#agca_admin_capability';
    public static $capabilityLabel = 'AGCA admin capability:';
    public static $capabilityEditDashboard = 'edit_dashboard';
    public static $capabilityCreateUsers = 'create_users';
    public static $capabilityEditPosts = 'edit_posts';

    public static $excludeAdministratorOption = 'agca_role_allbutadmin';
    public static $excludeAdministratorOptionLabel = 'Exclude AGCA admin from customizations';
}
