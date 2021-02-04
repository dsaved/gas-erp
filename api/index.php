<?php
	// setting error logging to be active 
	ini_set("log_errors", TRUE);  
    require "Paths.ini";
    require CONFIG_LIBS;
	/**
     * Initialize The Model Class From Model Dir
     * @return null
     */
	function autoloadModel($className) {
		$filename = MODELS . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
     * Initialize The Controller Class From Controller Dir
     * @return null
     */
	function autoloadController($className) {
		$filename = CONTROLLERS . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
     * Initialize The Controller Class From Controller Dir
     * @return null
     */
	function autoloadModels($className) {
		if ($handle = opendir(MODELS)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && is_dir(MODELS.$entry)) {
					$filename = MODELS ."$entry/". $className . ".php";
					if (is_readable($filename)) {
						require $filename;
					}
				}
			}
			closedir($handle);
		}
	}

	/**
     * Initialize The Controller Class From Controller Dir
     * @return null
     */
	function autoloadControllers($className) {
		if ($handle = opendir(CONTROLLERS)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && is_dir(CONTROLLERS.$entry)) {
					$filename = CONTROLLERS ."$entry/". $className . ".php";
					if (is_readable($filename)) {
						require $filename;
					}
				}
			}
			closedir($handle);
		}
	}
	
	/**
     * Initialize The Application Base File From Libs Dir
     * @return boolean
     */
	function autoloadApplication($className) {
		$filename = APP . $className . ".php";
		if (is_readable($filename)) {
			require $filename;
		}
	}
	
	// Register Autoloaders
	spl_autoload_register("autoloadModel");
	spl_autoload_register("autoloadModels");
	spl_autoload_register("autoloadController");
	spl_autoload_register("autoloadControllers");
	spl_autoload_register("autoloadApplication");
	
	$app = new Application();
	$app->run();
	