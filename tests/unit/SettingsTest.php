<?php
/**
 * Unit Test Example
 *
 * @package MenuPilot
 */

namespace MenuPilot\Tests\Unit;

use Codeception\Test\Unit;
use MenuPilot\Settings;

class SettingsTest extends Unit {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		// Setup before each test
	}

	protected function _after() {
		// Cleanup after each test
	}

	// Test example
	public function testSettingsClassExists() {
		$this->assertTrue( class_exists( Settings::class ) );
	}

	public function testGetSettings() {
		$settings = new Settings();
		$result   = $settings->get_settings();
		$this->assertIsArray( $result );
	}
}
