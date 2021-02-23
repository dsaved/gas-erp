<?php

/**
 * Description of Session
 *
 * @author kenny @modified By Dsaved
 */
class Session
{
	public static function exists($name)
	{
		return isset($_SESSION[SESSION_KEY . $name]) ? true : false;
	}

	public static function put($name, $value)
	{
		return $_SESSION[SESSION_KEY . $name] = $value;
	}

	public static function destroy()
	{
		return session_unset();
	}

	public static function get($name)
	{
		if (self::exists($name)) {
			return $_SESSION[SESSION_KEY . $name];
		} else {
			return "";
		}
	}

	public static function delete($name)
	{
		if (self::exists($name)) {
			unset($_SESSION[SESSION_KEY . $name]);
		}
	}

	public static function flash($name, $string = "")
	{
		if (self::exists($name)) {
			$session = self::get($name);
			self::delete($name);
			return $session;
		} else {
			self::put($name, $string);
		}
	}
}
