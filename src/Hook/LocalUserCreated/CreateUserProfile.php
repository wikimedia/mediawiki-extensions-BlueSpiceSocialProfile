<?php
namespace BlueSpice\Social\Profile\Hook\LocalUserCreated;

use BlueSpice\Hook\LocalUserCreated;
use BlueSpice\Social\Profile\Job\CreateProfile;
use JobQueueGroup;

class CreateUserProfile extends LocalUserCreated {

	protected function doProcess() {
		try {
			$job = new CreateProfile(
				CreateProfile::JOBCOMMAND,
				$this->user->getUserPage()
			);
			JobQueueGroup::singleton()->push( $job );
		} catch ( Exception $e ) {
		}
		return true;
	}

}
