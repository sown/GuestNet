<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

include '/usr/share/php/Math/BigInteger.php';

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/London');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_GB.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-gb');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}
elseif (isset($_SERVER['REMOTE_ADDR']))
{
	$developmentAddrs = array();
	if (file_exists(APPPATH.'config/dev_ips'.EXT))
	{
		include APPPATH.'config/dev_ips'.EXT;
	}

	if (in_array($_SERVER['REMOTE_ADDR'], $developmentAddrs))
		Kohana::$environment = Kohana::DEVELOPMENT;
	else
	{
		Kohana::$environment = Kohana::PRODUCTION;
	}
}

/**
 * Enable xdebug parameter collection in development mode to improve fatal stack traces.
 */
if (Kohana::$environment == Kohana::DEVELOPMENT && extension_loaded('xdebug'))
{
    ini_set('xdebug.collect_params', 3);
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => '/',
	'index_file' => FALSE
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'auth'       => MODPATH.'auth',       // Basic authentication
	'cache'         => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database'   => MODPATH.'database',   // Database access
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	
	'doctrine2'  => MODPATH.'doctrine2',  // Doctrine2
	//'php-ipaddress' => MODPATH.'php-ipaddress',

	));


/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
#Route::set('default', '(<controller>(/<action>(/<id>)))')
#	->defaults(array(
#		'controller' => 'welcome',
#		'action'     => 'index',
#	));


Route::set('login', '', array(
        ))
        ->defaults(array(
                'controller' => 'login',
                'action'     => 'login_page',
        ));

Route::set('logout', 'logout', array(
        ))
        ->defaults(array(
                'controller' => 'login',
                'action'     => 'logout',
        ));

Route::set('glogin', 'glogin', array(
        ))
        ->defaults(array(
                'controller' => 'googlelogin',
                'action'     => 'default',
        ));

Route::set('main', 'main', array(
        ))
        ->defaults(array(
                'controller' => 'main',
                'action'     => 'default',
        ));

Route::set('forgot_password', 'forgot_password', array(
        ))
        ->defaults(array(
                'controller' => 'login',
                'action'     => 'forgot_password',
        ));

Route::set('myaccount', 'myaccount', array(
        ))
        ->defaults(array(
		'controller' => 'accounts',
                'action'     => 'default',
        ));

Route::set('guests', 'guests', array(
        ))
        ->defaults(array(
                'controller' => 'accounts',
                'action'     => 'guests',
        ));

Route::set('generate_regcode', 'codes', array(
        ))
        ->defaults(array(
                'controller' => 'registrationcodes',
                'action'     => 'generate',
        ));

Route::set('redeem_regcode', 'redeem', array(
        ))
        ->defaults(array(
                'controller' => 'registrationcodes',
                'action'     => 'redeem',
        ));


Route::set('error', 'error/<action>(/<message>)', array(
		'action' => '[0-9]++',
		'message' => '.+'))
	->defaults(array(
		'controller' => 'error'
	));

require_once(APPPATH.'/classes/mysql-dbo.php');
Doctrine\DBAL\Types\Type::addType('ipv4address', 'Model_Type_IPv4Address');
Doctrine\DBAL\Types\Type::addType('ipv6address', 'Model_Type_IPv6Address');
Doctrine\DBAL\Types\Type::addType('ipv4networkaddress', 'Model_Type_IPv4NetworkAddress');
Doctrine\DBAL\Types\Type::addType('ipv6networkaddress', 'Model_Type_IPv6NetworkAddress');
Doctrine\DBAL\Types\Type::addType('deploymenttype', 'Model_Type_DeploymentType');
