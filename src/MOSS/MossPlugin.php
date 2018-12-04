<?php
/**
 * @file
 * Plugin class for the MOSS Submission Analysis Subsystem
 */

namespace CL\Moss;

use CL\Site\Site;
use CL\Site\System\Server;
use CL\Site\Router;


/**
 * Plugin class for the MOSS Submission Analysis Subsystem
 */
class MossPlugin extends \CL\Site\Plugin {
	/**
	 * A tag that represents this plugin
	 * @return string A tag like 'course', 'users', etc.
	 */
	public function tag() {return 'moss';}

	/**
	 * Return an array of tags indicating what plugins this one is dependent on.
	 * @return array of tags this plugin is dependent on
	 */
	public function depends() {return ['course'];}

	/**
	 * Install the plugin
	 * @param Site $site The Site configuration object
	 */
	public function install(Site $site) {
		$this->site = $site;
	}


	/**
	 * Amend existing object
	 * The Router is amended with routes for the login page
	 * and for the user API.
	 * @param $object Object to amend.
	 */
	public function amend($object) {
		if($object instanceof Router) {
			$router = $object;

			$router->addRoute(['moss', ':assign', ':submission'], function(Site $site, Server $server, array $params, array $properties, $time) {
				$view = new MossView($site, $server, $properties);
				return $view->whole();
			});
		}
	}
}