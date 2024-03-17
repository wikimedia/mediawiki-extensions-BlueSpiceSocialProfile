<?php
namespace BlueSpice\Social\Profile\Job;

use BlueSpice\Social\Profile\Entity\Profile;
use Job;
use MediaWiki\MediaWikiServices;

class CreateProfile extends Job {
	public const JOBCOMMAND = 'socialprofilecreate';

	public function run() {
		$services = MediaWikiServices::getInstance();
		$entityFactory = $services->getService( 'BSSocialProfileEntityFactory' );
		$user = $services->getUserFactory()->newFromName( $this->title->getText() );
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
