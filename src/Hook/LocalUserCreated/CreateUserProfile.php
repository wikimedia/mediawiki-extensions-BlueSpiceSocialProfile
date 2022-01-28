<?php
namespace BlueSpice\Social\Profile\Hook\LocalUserCreated;

use BlueSpice\Hook\LocalUserCreated;
use BlueSpice\Social\Profile\Job\CreateProfile;
use MediaWiki\MediaWikiServices;

class CreateUserProfile extends LocalUserCreated {

	protected function doProcess() {
		try {
			$job = new CreateProfile(
				CreateProfile::JOBCOMMAND,
				$this->user->getUserPage()
			);
			MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
		} catch ( Exception $e ) {
		}
		return true;
	}

}
