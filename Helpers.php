<?php

namespace Some\Thing;

use Closure;

if( !class_exists('Some\Thing\Helpers', false) )
{
	class Helpers
	{
		protected static $callbacks = [];

		protected static $version = '0.0.0';

		/**
		 * @return string
		 */
		public static function __getVersion()
		{
			return static::$version;
		}

		/**
		 * @param  string $version
		 * @param  array  $methods
		 */
		public static function __add($methods = [])
		{
			foreach( $methods as $method => $value )
			{
				if( is_callable($value) )
				{
					$value = [ $value, '1.0.0' ];
				}

				$value = (object)[
					'name' => $method,
					'callback' => $value[0],
					'version' => $value[1],
				];

				if( !isset(static::$callbacks[$method]) || version_compare($value['version'], static::$callbacks[$method]['version']) >= 0 )
				{
					static::$callbacks[$method] = $value;
				}
			}
		}

		public static function __callStatic($method, $arguments)
		{
			// call a function static
			// and bind static context to it for recursive functions
			
			if( isset(static::$callbacks[$method]['callback']) )
			{
				return call_user_func_array(Closure::bind(static::$callbacks[$method]['callback'], null, __CLASS__), $arguments);
			}

			throw new BadMethodCallException("Method {$method} doesn't exist");
		}
	}
}

Helpers::__add([
	'stringContains' => [ function($string, $substring) {
		return stripos($string, $substring) !== false;
	}, '1.0.1'],

	'func' => function() {
		echo 'Cow';
	},

	'rec' => function() {
		static::func();
	},
]);
