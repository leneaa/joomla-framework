<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Data
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/buran.php';
require_once __DIR__ . '/stubs/capitaliser.php';

use Joomla\Data\Data;
use Joomla\Date\Date;
use Joomla\Registry\Registry;

/**
 * Tests for the JData class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Data
 * @since       12.3
 */
class JDataTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    Joomla\Data\Data
	 * @since  12.3
	 */
	private $instance;

	/**
	 * Tests the object constructor.
	 *
	 * @return  void
	 *
	 * @covers	Joomla\Data\Data::__construct
	 */
	public function test__construct()
	{
		$instance = new Data(array('property1' => 'value1', 'property2' => 5));
		$this->assertThat(
			$instance->property1,
			$this->equalTo('value1')
		);
	}

	/**
	 * Tests the __get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::__get
	 * @since   12.3
	 */
	public function test__get()
	{
		$this->assertNull(
			$this->instance->foobar,
			'Unknown property should return null.'
		);
	}

	/**
	 * Tests the __isset method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::__isset
	 * @since   12.3
	 */
	public function test__isset()
	{
		$this->assertFalse(isset($this->instance->title), 'Unknown property');

		$this->instance->bind(array('title' => true));

		$this->assertTrue(isset($this->instance->title), 'Property is set.');
	}

	/**
	 * Tests the __set method where a custom setter is available.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::__set
	 * @since   12.3
	 */
	public function test__set_setter()
	{
		$instance = new JDataCapitaliser;

		// Set the property and assert that it is the expected value.
		$instance->test_value = 'one';
		$this->assertEquals('ONE', $instance->test_value);

		$instance->bind(array('test_value' => 'two'));
		$this->assertEquals('TWO', $instance->test_value);
	}

	/**
	 * Tests the __unset method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::__unset
	 * @since   12.3
	 */
	public function test__unset()
	{
		$this->instance->bind(array('title' => true));

		$this->assertTrue(isset($this->instance->title));

		unset($this->instance->title);

		$this->assertFalse(isset($this->instance->title));
	}

	/**
	 * Tests the bind method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::bind
	 * @since   12.3
	 */
	public function testBind()
	{
		$properties = array('null' => null);

		$this->instance->null = 'notNull';
		$this->instance->bind($properties, false);
		$this->assertSame('notNull', $this->instance->null, 'Checking binding without updating nulls works correctly.');

		$this->instance->bind($properties);
		$this->assertSame(null, $this->instance->null, 'Checking binding with updating nulls works correctly.');
	}

	/**
	 * Tests the bind method with array input.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::bind
	 * @since   12.3
	 */
	public function testBind_array()
	{
		$properties = array(
			'property_1' => 'value_1',
			'property_2' => '1',
			'property_3' => 1,
			'property_4' => false,
			'property_5' => array('foo')
		);

		// Bind an array to the object.
		$this->instance->bind($properties);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->instance->$property);
		}
	}

	/**
	 * Tests the bind method with input that is a traverable object.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::bind
	 * @since   12.3
	 */
	public function testBind_arrayObject()
	{
		$properties = array(
			'property_1' => 'value_1',
			'property_2' => '1',
			'property_3' => 1,
			'property_4' => false,
			'property_5' => array('foo')
		);

		$traversable = new ArrayObject($properties);

		// Bind an array to the object.
		$this->instance->bind($traversable);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->instance->$property);
		}
	}

	/**
	 * Tests the bind method with object input.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::bind
	 * @since   12.3
	 */
	public function testBind_object()
	{
		$properties = new stdClass;
		$properties->property_1 = 'value_1';
		$properties->property_2 = '1';
		$properties->property_3 = 1;
		$properties->property_4 = false;
		$properties->property_5 = array('foo');

		// Bind an array to the object.
		$this->instance->bind($properties);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->instance->$property);
		}
	}

	/**
	 * Tests the bind method for an expected exception.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\Data::bind
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testBind_exception()
	{
		$this->instance->bind('foobar');
	}

	/**
	 * Tests the count method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::count
	 * @since   12.3
	 */
	public function testCount()
	{
		// Tests the current object is empty.
		$this->assertCount(0, $this->instance);

		// Set a complex property.
		$this->instance->foo = array(1 => array(2));
		$this->assertCount(1, $this->instance);

		// Set some more properties.
		$this->instance->bar = 'bar';
		$this->instance->barz = 'barz';
		$this->assertCount(3, $this->instance);
	}

	/**
	 * Tests the dump method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::dump
	 * @since   12.3
	 */
	public function testDump()
	{
		$dump = $this->instance->dump();

		$this->assertEquals(
			'object',
			gettype($dump),
			'Dump should return an object.'
		);

		$this->assertEmpty(
			get_object_vars($dump),
			'Empty JData should give an empty dump.'
		);

		$properties = array(
			'scalar' => 'value_1',
			'date' => new Date('2012-01-01'),
			'registry' => new Registry(array('key' => 'value')),
			'JData' => new Data(
				array(
					'level2' => new Data(
						array(
							'level3' => new Data(
								array(
									'level4' => new Data(
										array(
											'level5' => 'deep',
										)
									)
								)
							)
						)
					)
				)
			),
		);

		// Bind an array to the object.
		$this->instance->bind($properties);

		// Dump the object (default is 3 levels).
		$dump = $this->instance->dump();

		$this->assertEquals($dump->scalar, 'value_1');
		$this->assertEquals($dump->date, '2012-01-01 00:00:00');
		$this->assertEquals($dump->registry, (object) array('key' => 'value'));
		$this->assertInstanceOf('stdClass', $dump->JData->level2);
		$this->assertInstanceOf('stdClass', $dump->JData->level2->level3);
		$this->assertInstanceOf('Joomla\Data\Data', $dump->JData->level2->level3->level4);

		$dump = $this->instance->dump(0);
		$this->assertInstanceOf('Joomla\Date\Date', $dump->date);
		$this->assertInstanceOf('Joomla\Registry\Registry', $dump->registry);
		$this->assertInstanceOf('Joomla\Data\Data', $dump->JData);

		$dump = $this->instance->dump(1);
		$this->assertEquals($dump->date, '2012-01-01 00:00:00');
		$this->assertEquals($dump->registry, (object) array('key' => 'value'));
		$this->assertInstanceOf('stdClass', $dump->JData);
		$this->assertInstanceOf('Joomla\Data\Data', $dump->JData->level2);
	}

	/**
	 * Tests the dumpProperty method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::dumpProperty
	 * @since   12.3
	 */
	public function testDumpProperty()
	{
		$dumped = new SplObjectStorage;

		$this->instance->bind(array('dump_test' => 'dump_test_value'));
		$this->assertEquals(
			'dump_test_value',
			TestReflection::invoke($this->instance, 'dumpProperty', 'dump_test', 3, $dumped)
		);
	}

	/**
	 * Tests the getIterator method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::getIterator
	 * @since   12.3
	 */
	public function testGetIterator()
	{
		$this->assertInstanceOf('ArrayIterator', $this->instance->getIterator());
	}

	/**
	 * Tests the getProperty method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::getProperty
	 * @since   12.3
	 */
	public function testGetProperty()
	{
		$this->instance->bind(array('get_test' => 'get_test_value'));
		$this->assertEquals('get_test_value', $this->instance->get_test);
	}

	/**
	 * Tests the getProperty method.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\Data::getProperty
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testGetProperty_exception()
	{
		$this->instance->bind(array('get_test' => 'get_test_value'));

		// Get the reflection property. This should throw an exception.
		$property = TestReflection::getValue($this->instance, 'get_test');
	}

	/**
	 * Tests the jsonSerialize method.
	 *
	 * Note, this is not completely backward compatible. Previous this would just return the class name.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::jsonSerialize
	 * @since   12.3
	 */
	public function testJsonSerialize()
	{
		$this->assertEquals('{}', json_encode($this->instance->jsonSerialize()), 'Empty object.');

		$this->instance->bind(array('title' => 'Simple Object'));
		$this->assertEquals('{"title":"Simple Object"}', json_encode($this->instance->jsonSerialize()), 'Simple object.');
	}

	/**
	 * Tests the setProperty method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::setProperty
	 * @since   12.3
	 */
	public function testSetProperty()
	{
		$this->instance->set_test = 'set_test_value';
		$this->assertEquals('set_test_value', $this->instance->set_test);

		$object = new JDataCapitaliser;
		$object->test_value = 'upperCase';

		$this->assertEquals('UPPERCASE', $object->test_value);
	}

	/**
	 * Tests the setProperty method.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Data\Data::setProperty
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testSetProperty_exception()
	{
		// Get the reflection property. This should throw an exception.
		$property = TestReflection::getValue($this->instance, 'set_test');
	}

	/**
	 * Test that JData::setProperty() will not set a property which starts with a null byte.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Data\Data::setProperty
	 * @see     http://us3.php.net/manual/en/language.types.array.php#language.types.array.casting
	 * @since   12.3
	 */
	public function testSetPropertySkipsPropertyWithNullBytes()
	{
		// Create a property that starts with a null byte.
		$property = "\0foo";

		// Attempt to set the property.
		$this->instance->$property = 'bar';

		// The property should not be set.
		$this->assertNull($this->instance->$property);
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Data;
	}
}
