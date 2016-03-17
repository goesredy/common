<?php
/**
 * @copyright  Copyright (C) 2013 - 2015 P.Alex (Alexandru Pruteanu)
 * @license    Licensed under the MIT License; see LICENSE
 */

namespace PHPStructuredData;

/**
 * PHP abstract class for interacting with Microdata and RDFa Lite 1.1 semantics.
 *
 * @since  1.0
 */
abstract class StructuredData
{
	/**
	 * Array with all available Types and Properties from the http://schema.org vocabulary
	 *
	 * @var  array
	 */
	protected static $types = null;

	/**
	 * The Type
	 *
	 * @var  string
	 */
	protected $type = null;

	/**
	 * The Property
	 *
	 * @var  string
	 */
	protected $property = null;

	/**
	 * The Human content
	 *
	 * @var  string
	 */
	protected $content = null;

	/**
	 * The Machine content
	 *
	 * @var  string
	 */
	protected $machineContent = null;

	/**
	 * The Fallback Type
	 *
	 * @var  string
	 */
	protected $fallbackType = null;

	/**
	 * The Fallback Property
	 *
	 * @var  string
	 */
	protected $fallbackProperty = null;

	/**
	 * Used for checking if the library output is enabled or disabled
	 *
	 * @var  boolean
	 */
	protected $enabled = true;

	/**
	 * Initialize the class and setup the default $Type
	 *
	 * @param   string   $type  Optional, fallback to 'Thing' Type
	 * @param   boolean  $flag  Enable or disable the library output
	 */
	public function __construct($type = '', $flag = true)
	{
		if ($this->enabled = (boolean) $flag)
		{
			// Fallback to 'Thing' Type
			if (!$type)
			{
				$type = 'Thing';
			}

			$this->setType($type);
		}
	}

	/**
	 * Load all available Types and Properties from the http://schema.org vocabulary contained in the types.json file
	 *
	 * @return  void
	 */
	protected static function loadTypes()
	{
		// Load the JSON file
		if (!static::$types)
		{
			$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'types.json';
			static::$types = json_decode(file_get_contents($path), true);
		}
	}

	/**
	 * Reset all params
	 *
	 * @return void
	 */
	protected function resetParams()
	{
		$this->content          = null;
		$this->machineContent	= null;
		$this->property         = null;
		$this->fallbackProperty = null;
		$this->fallbackType     = null;
	}

	/**
	 * Enable or Disable the library output
	 *
	 * @param   boolean  $flag  Enable or disable the library output
	 *
	 * @return  StructuredData  Instance of $this
	 */
	public function enable($flag = true)
	{
		$this->enabled = (boolean) $flag;

		return $this;
	}

	/**
	 * Return 'true' if the library output is enabled
	 *
	 * @return  boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Set a new http://schema.org Type
	 *
	 * @param   string  $type  The $Type to be setup
	 *
	 * @return  StructuredData  Instance of $this
	 */
	public function setType($type)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the Type
		$this->type = static::sanitizeType($type);

		// If the given $Type isn't available, fallback to 'Thing' Type
		if (!static::isTypeAvailable($this->type))
		{
			$this->type	= 'Thing';
		}

		return $this;
	}

	/**
	 * Return the current $Type name
	 *
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Setup a $Property
	 *
	 * @param   string  $property  The Property
	 *
	 * @return  StructuredData  Instance of $this
	 */
	public function property($property)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the $Property
		$property = static::sanitizeProperty($property);

		// Control if the $Property exists in the given $Type and setup it, otherwise leave it 'NULL'
		if (static::isPropertyInType($this->type, $property))
		{
			$this->property = $property;
		}

		return $this;
	}

	/**
	 * Return the current $Property name
	 *
	 * @return  string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Setup a Human content or content for the Machines
	 *
	 * @param   string  $content         The human content or machine content to be used
	 * @param   string  $machineContent  The machine content
	 *
	 * @return  StructuredData  Instance of $this
	 */
	public function content($content, $machineContent = null)
	{
		$this->content = $content;
		$this->machineContent = $machineContent;

		return $this;
	}

	/**
	 * Return the current $content
	 *
	 * @return  string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Return the current $machineContent
	 *
	 * @return  string
	 */
	public function getMachineContent()
	{
		return $this->machineContent;
	}

	/**
	 * Setup a Fallback Type and Property
	 *
	 * @param   string  $type      The Fallback Type
	 * @param   string  $property  The Fallback Property
	 *
	 * @return  StructuredData  Instance of $this
	 */
	public function fallback($type, $property)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the $Type
		$this->fallbackType = static::sanitizeType($type);

		// If the given $Type isn't available, fallback to 'Thing' Type
		if (!static::isTypeAvailable($this->fallbackType))
		{
			$this->fallbackType = 'Thing';
		}

		// Control if the $Property exist in the given $Type and setup it, otherwise leave it 'NULL'
		if (static::isPropertyInType($this->fallbackType, $property))
		{
			$this->fallbackProperty = $property;
		}
		else
		{
			$this->fallbackProperty = null;
		}

		return $this;
	}

	/**
	 * Return the current $fallbackType
	 *
	 * @return  string
	 */
	public function getFallbackType()
	{
		return $this->fallbackType;
	}

	/**
	 * Return the current $fallbackProperty
	 *
	 * @return  string
	 */
	public function getFallbackProperty()
	{
		return $this->fallbackProperty;
	}

	/**
	 * This function handles the display logic.
	 * It checks if the Type, Property are available, if not check for a Fallback,
	 * then reset all params for the next use and return the HTML.
	 *
	 * @param   string   $displayType  Optional, 'inline', available options ['inline'|'span'|'div'|meta]
	 * @param   boolean  $emptyOutput  Return an empty string if the library output is disabled and there is a $content value
	 *
	 * @return  string
	 */
	public function display($displayType = '', $emptyOutput = false)
	{
		// Initialize the HTML to output
		$html = ($this->content !== null && !$emptyOutput) ? $this->content : '';

		// Control if the library output is enabled, otherwise return the $content or an empty string
		if (!$this->enabled)
		{
			// Reset params
			$this->resetParams();

			return $html;
		}

		// If the $property is wrong for the current $Type check if a Fallback is available, otherwise return an empty HTML
		if ($this->property)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = static::htmlSpan($html, $this->property);
						break;

					case 'div':
						$html = static::htmlDiv($html, $this->property);
						break;

					case 'meta':
						$html = ($this->machineContent !== null) ? $this->machineContent : $html;
						$html = static::htmlMeta($html, $this->property);
						break;

					default:
						// Default $displayType = 'inline'
						$html = static::htmlProperty($this->property);
						break;
				}
			}
			else
			{
				/*
				 * Process and return the HTML in an automatic way,
				 * with the $Property expected Types and display everything in the right way,
				 * check if the $Property is 'normal', 'nested' or must be rendered in a metadata tag
				 */
				switch (static::getExpectedDisplayType($this->type, $this->property))
				{
					case 'nested':
						// Retrieve the expected 'nested' Type of the $Property
						$nestedType = static::getExpectedTypes($this->type, $this->property);
						$nestedProperty = '';

						// If there is a Fallback Type then probably it could be the expectedType
						if (in_array($this->fallbackType, $nestedType))
						{
							$nestedType = $this->fallbackType;

							if ($this->fallbackProperty)
							{
								$nestedProperty = $this->fallbackProperty;
							}
						}
						else
						{
							$nestedType = $nestedType[0];
						}

						// Check if a $content is available, otherwise fallback to an 'inline' display type
						if ($this->content !== null)
						{
							if ($nestedProperty)
							{
								$html = static::htmlSpan(
									$this->content,
									$nestedProperty
								);
							}

							$html = static::htmlSpan(
								$html,
								$this->property,
								$nestedType,
								true
							);
						}
						else
						{
							$html = static::htmlProperty($this->property) . ' ' . static::htmlScope($nestedType);

							if ($nestedProperty)
							{
								$html .= ' ' . static::htmlProperty($nestedProperty);
							}
						}

						break;

					case 'meta':
						// Check if a $content is available, otherwise fallback to an 'inline' display type
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->property) . $this->content;
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if a $content is available,
						 * otherwise fallback to an 'inline' display type
						 */
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->property);
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}

						break;
				}
			}
		}
		elseif ($this->fallbackProperty)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = static::htmlSpan($html, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'div':
						$html = static::htmlDiv($html, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'meta':
						$html = ($this->machineContent !== null) ? $this->machineContent : $html;
						$html = static::htmlMeta($html, $this->fallbackProperty, $this->fallbackType);
						break;

					default:
						// Default $displayType = 'inline'
						$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						break;
				}
			}
			else
			{
				/*
				 * Process and return the HTML in an automatic way,
				 * with the $Property expected Types an display everything in the right way,
				 * check if the Property is 'nested' or must be rendered in a metadata tag
				 */
				switch (static::getExpectedDisplayType($this->fallbackType, $this->fallbackProperty))
				{
					case 'meta':
						// Check if a $content is available, otherwise fallback to an 'inline' display Type
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->fallbackProperty, $this->fallbackType);
						}
						else
						{
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if a $content is available,
						 * otherwise fallback to an 'inline' display Type
						 */
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->fallbackProperty);
							$html = static::htmlSpan($html, '', $this->fallbackType);
						}
						else
						{
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

						break;
				}
			}
		}
		elseif (!$this->fallbackProperty && $this->fallbackType !== null)
		{
			$html = static::htmlScope($this->fallbackType);
		}

		// Reset params
		$this->resetParams();

		return $html;
	}

	/**
	 * Return the HTML of the current Scope
	 *
	 * @return  string
	 */
	public function displayScope()
	{
		// Control if the library output is enabled, otherwise return the $content or empty string
		if (!$this->enabled)
		{
			return '';
		}

		return static::htmlScope($this->type);
	}

	/**
	 * Return the sanitized $Type
	 *
	 * @param   string  $type  The Type to sanitize
	 *
	 * @return  string
	 */
	public static function sanitizeType($type)
	{
		return ucfirst(trim($type));
	}

	/**
	 * Return the sanitized $Property
	 *
	 * @param   string  $property  The Property to sanitize
	 *
	 * @return  string
	 */
	public static function sanitizeProperty($property)
	{
		return lcfirst(trim($property));
	}

	/**
	 * Return an array with all available Types and Properties from the http://schema.org vocabulary
	 *
	 * @return  array
	 */
	public static function getTypes()
	{
		static::loadTypes();

		return static::$types;
	}

	/**
	 * Return an array with all available Types from the http://schema.org vocabulary
	 *
	 * @return  array
	 */
	public static function getAvailableTypes()
	{
		static::loadTypes();

		return array_keys(static::$types);
	}

	/**
	 * Return the expected Types of the given Property
	 *
	 * @param   string  $type      The Type to process
	 * @param   string  $property  The Property to process
	 *
	 * @return  array
	 */
	public static function getExpectedTypes($type, $property)
	{
		static::loadTypes();

		$tmp = static::$types[$type]['properties'];

		// Check if the $Property is in the $Type
		if (isset($tmp[$property]))
		{
			return $tmp[$property]['expectedTypes'];
		}

		// Check if the $Property is inherit
		$extendedType = static::$types[$type]['extends'];

		// Recursive
		if (!empty($extendedType))
		{
			return static::getExpectedTypes($extendedType, $property);
		}

		return array();
	}

	/**
	 * Return the expected display type: [normal|nested|meta]
	 * In which way to display the Property:
	 * normal -> itemprop="name"
	 * nested -> itemprop="director" itemscope itemtype="http://schema.org/Person"
	 * meta   -> <meta itemprop="datePublished" content="1991-05-01">
	 *
	 * @param   string  $type      The Type where to find the Property
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 */
	protected static function getExpectedDisplayType($type, $property)
	{
		$expectedTypes = static::getExpectedTypes($type, $property);

		// Retrieve the first expected type
		$type = $expectedTypes[0];

		// Check if it's a 'meta' display
		if ($type === 'Date' || $type === 'DateTime' || $property === 'interactionCount')
		{
			return 'meta';
		}

		// Check if it's a 'normal' display
		if ($type === 'Text' || $type === 'URL' || $type === 'Boolean' || $type === 'Number')
		{
			return 'normal';
		}

		// Otherwise it's a 'nested' display
		return 'nested';
	}

	/**
	 * Recursive function, control if the given Type has the given Property
	 *
	 * @param   string  $type      The Type where to check
	 * @param   string  $property  The Property to check
	 *
	 * @return  boolean
	 */
	public static function isPropertyInType($type, $property)
	{
		if (!static::isTypeAvailable($type))
		{
			return false;
		}

		// Control if the $Property exists, and return 'true'
		if (array_key_exists($property, static::$types[$type]['properties']))
		{
			return true;
		}

		// Recursive: Check if the $Property is inherit
		$extendedType = static::$types[$type]['extends'];

		if (!empty($extendedType))
		{
			return static::isPropertyInType($extendedType, $property);
		}

		return false;
	}

	/**
	 * Control if the given Type is available
	 *
	 * @param   string  $type  The Type to check
	 *
	 * @return  boolean
	 */
	public static function isTypeAvailable($type)
	{
		static::loadTypes();

		return (array_key_exists($type, static::$types)) ? true : false;
	}
}
