<?php

namespace BlueSpice\Social\Profile\Hook\DeleteAccount;

use BlueSpice\DistributionConnector\Hook\DeleteAccount;
use BlueSpice\Social\Profile\ProfileFactory;

class DeleteUserProfile extends DeleteAccount {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$profile = $this->getProfileFactory()->newFromUser( $this->oldUser );
		return !$profile || !$profile->exists();
	}

	protected function doProcess() {
		$profile = $this->getProfileFactory()->newFromUser( $this->oldUser );
		$profile->delete(
			$this->getServices()->getService( 'BSUtilityFactory' )->getMaintenanceUser()->getUser()
		);
	}

	/**
	 *
	 * @return ProfileFactory
	 */
	private function getProfileFactory() {
		return $this->getServices()->getService( 'BSSocialProfileEntityFactory' );
	}

}
