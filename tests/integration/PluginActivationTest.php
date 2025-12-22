<?php
/**
 * Integration Test Example
 *
 * @package MenuPilot
 */

namespace MenuPilot\Tests\Integration;

use Codeception\TestCase\WPTestCase;

class PluginActivationTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		// Setup before each test
	}

	public function tearDown(): void {
		// Cleanup after each test
		parent::tearDown();
	}

	public function testPluginIsActivated() {
		$this->assertTrue(defined('MENUPILOT_VERSION'));
	}

	public function testSettingsOptionExists() {
		$settings = get_option('menupilot_settings');
		$this->assertNotFalse($settings);
	}
}
