<?php

namespace BlueSpice\Social\Profile\Privacy;

use Status;
use Message;
use User;
use Config;
use JobQueueGroup;
use Title;
use WikiPage;
use BlueSpice\Services;
use BlueSpice\Social\ExtendedSearch\Job\Entity as SearchJob;
use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use BlueSpice\Social\Profile\Entity\Profile;

class Handler implements IPrivacyHandler {
	/**
	 *
	 * @var User
	 */
	protected $user;

	/**
	 *
	 * @param \Database $db
	 */
	public function __construct( \Database $db ) {
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
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
		foreach ( $profileFields as $key ) {
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

	/**
	 *
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		$this->user = $userToDelete;
		$status = $this->deleteEntityPage();

		// Deleting a page will return non-fatal Status if deletion fails,
		// but we need to return a fatal to stop the deletion process
		if ( $status->isGood() ) {
			return $status;
		}

		return Status::newFatal( $status->getMessage() );
	}

	/**
	 *
	 * @param array $types
	 * @param string $format
	 * @param User $user
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		$this->user = $user;
		$profile = $this->getProfile();
		if ( $profile instanceof Profile === false ) {
			return Status::newGood( [] );
		}

		$profileFields = $this->getProfileFields( $profile->getConfig() );

		$data = [];
		foreach ( $profileFields as $key => $config ) {
			$value = $profile->get( $key );
			if ( !$value ) {
				continue;
			}
			$keyMessage = Message::newFromKey( $config['i18n'] )->plain();
			$data[] = "$keyMessage: {$profile->get( $key )}";
		}
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => $data
		] );
	}

	/**
	 * @return Profile|null
	 */
	protected function getProfile() {
		$entityFactory = Services::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		return $entityFactory->newFromUser( $this->user );
	}

	/**
	 * @param Config $config
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
	 * @return Status
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
