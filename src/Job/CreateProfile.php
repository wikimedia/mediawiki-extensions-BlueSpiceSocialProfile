<?php
namespace BlueSpice\Social\Profile\Job;

use BlueSpice\Social\Profile\Entity\Profile as Profile;
use Job;
use MediaWiki\MediaWikiServices;
use User;

class CreateProfile extends Job {
	const JOBCOMMAND = 'socialprofilecreate';

	public function run() {
		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		$user = User::newFromName( $this->title->getText() );
		if ( !$user || $user->isAnon() ) {
			return true;
		}
		$entity = $entityFactory->newFromUser( $user );

		if ( !$entity instanceof Profile ) {
			return true;
		}

		if ( $entity->exists() ) {
			return true;
		}
		$status = $entity->save();
		return $status->isOk();
	}
}
