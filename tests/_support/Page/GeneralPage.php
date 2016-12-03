<?php
namespace Page;

class GeneralPage extends AGCAPage
{
    public static $capabilityField = '#agca_form select#agca_admin_capability';
    public static $capabilityLabel = 'AGCA admin capability:';
    public static $capabilitySelected = '#agca_form #agca_admin_capability [selected]';
}
