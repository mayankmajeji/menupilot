<?php
/**
 * Acceptance Test Example
 *
 * @package MenuPilot
 */

class AdminSettingsPageCest {

	public function _before(\AcceptanceTester $I) {
		$I->loginAsAdmin();
	}

	// Test admin menu exists
	public function testAdminMenuExists(\AcceptanceTester $I) {
		$I->amOnAdminPage('/');
		$I->see('MenuPilot');
	}

	// Test settings page loads
	public function testSettingsPageLoads(\AcceptanceTester $I) {
		$I->amOnAdminPage('admin.php?page=menupilot-settings');
		$I->see('MenuPilot');
	}
}
