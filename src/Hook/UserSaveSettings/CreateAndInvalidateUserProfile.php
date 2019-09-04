<?php
namespace BlueSpice\Social\Profile\Hook\UserSaveSettings;
use BlueSpice\Hook\UserSaveSettings;
use BlueSpice\Social\Profile\Entity\Profile;

class CreateAndInvalidateUserProfile extends UserSaveSettings {
	protected function doProcess() {
		$entityFactory = $this->getServices()->getService(
			'BSSocialProfileEntityFactory'
		);
		$entity = $entityFactory->newFromUser( $this->user );

		if( !$entity instanceof Profile ) {
			return true;
		}

		if( !$entity->exists() && PHP_SAPI !== 'cli' ) {
			return $oStatus = $entity->save();
		}
		$entity->invalidateCache();

		return true;
	}
}