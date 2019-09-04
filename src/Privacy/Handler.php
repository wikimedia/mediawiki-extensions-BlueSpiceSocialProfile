<?php

namespace BlueSpice\Social\Profile\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use BlueSpice\Social\Profile\Entity\Profile;
use MediaWiki\MediaWikiServices;
use Status;
use User;
use BlueSpice\Social\ExtendedSearch\Job\Entity as SearchJob;
use JobQueueGroup;
use Title;
use WikiPage;

class Handler implements IPrivacyHandler {
	protected $user;

	public function __construct( \Database $db ) {}


	public function anonymize( $oldUsername, $newUsername ) {
		$this->user = User::newFromName( $oldUsername );

		$profile = $this->getProfile();
		if ( !$profile ) {
			// User has no profile - nothing to anonymize
			return Status::newGood();
		}

		$profileFields = array_keys( $this->getProfileFields(
			$profile->getConfig()
		) );
		foreach( $profileFields as $key ) {
			$profile->set( $key, '' );
		}

		$profile->save( $this->user );

		// Add job to update search index after the process has completed
		$job = new SearchJob(
			$profile->getTitle()
		);

		JobQueueGroup::singleton()->push(
			$job
		);

		return Status::newGood();
	}

	public function delete( User $userToDelete, User $deletedUser ) {
		$this->user = $userToDelete;
		$status = $this->deleteEntityPage();

		// Deleting a page will return non-fatal \Status if deletion fails,
		// but we need to return a fatal to stop the deletion process
		if ( $status->isGood() ) {
			return $status;
		}

		return Status::newFatal( $status->getMessage() );
	}

	public function exportData( array $types, $format, User $user ) {
		$this->user = $user;
		$profile = $this->getProfile();
		if ( $profile instanceof Profile === false ) {
			return Status::newGood( [] );
		}

		$profileFields = $this->getProfileFields( $profile->getConfig() );

		$data = [];
		foreach( $profileFields as $key => $config ) {
			$value = $profile->get( $key );
			if ( !$value ) {
				continue;
			}
			$keyMessage = wfMessage( $config['i18n'] )->plain();
			$data[] = "$keyMessage: {$profile->get( $key )}";
		}
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => $data
		] );
	}

	/**
	 * @return Profile
	 */
	protected function getProfile() {
		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		return $entityFactory->newFromUser( $this->user );
	}

	/**
	 * @param \Config $config
	 * @return array
	 */
	protected function getProfileFields( $config ) {
		$profileFields = $config->get( 'BSSocialProfileFields' );
		$customProfileFields = $config->get( 'BSSocialProfileCustomFields' );
		
		return array_merge( $profileFields, $customProfileFields );
	}

	/**
	 * Deletes entity page for given user profile
	 * and removes the entity from search index
	 *
	 * @return \Status
	 */
	protected function deleteEntityPage() {
		$profile = $this->getProfile();

		$profilePage = $profile->getTitle();
		if ( $profilePage instanceof Title && $profilePage->exists() ) {
			$wikipage = WikiPage::newFromID( $profilePage->getArticleID() );
			$status = $wikipage->doDeleteArticleReal( '', true );
		}

		if ( $status->isGood() ) {
			$job = new SearchJob(
				$profile->getTitle()
			);

			JobQueueGroup::singleton()->push(
				$job
			);
		}

		return $status;
	}
}
