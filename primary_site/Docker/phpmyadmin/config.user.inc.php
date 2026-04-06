<?php
/**
 * Custom phpMyAdmin configuration
 */

// Default theme
$cfg['ThemeDefault'] = 'pmahomme';

// Server settings
$cfg['Servers'][1]['host'] = getenv('PMA_HOST') ?: 'mysql';

// UI preferences
$cfg['ShowDatabasesNavigationAsTree'] = true;
$cfg['NavigationTreeEnableGrouping'] = true;
$cfg['MaxNavigationItems'] = 250;

// Upload/import limits
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['ExecTimeLimit'] = 600;

// Force dark mode via cookie on first visit
$cfg['ThemeManager'] = true;
