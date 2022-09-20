<?php

namespace BlueSpice\Social\Profile\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use BlueSpice\Social\ExtendedSearch\Job\Entity as SearchJob;
use BlueSpice\Social\Profile\Entity\Profile;
use Config;
use MediaWiki\MediaWikiServices;
use Message;
use Status;
use User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/**
	 *
	 * @var User
	 */
	protected $user;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->user = $this->services->getUserFactory()->newFromName( $newUsername );

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
		$profile->invalidateCache();

		// Add job to update search index after the process has completed
		$job = new SearchJob(
			$profile->getTitle()
		);

		MediaWikiServices::getInstance()->getJobQueueGroup()->push(
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
		$status = $this->deleteEntityPage( $deletedUser );

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
		$profile->invalidateCache();
		$profileFields = $this->getProfileFields( $profile->getConfig() );

		$data = [];
		foreach ( $profileFields as $key => $config ) {
			$value = $profile->get( $key );
			if ( !$value ) {
				continue;
			}
			if ( isset( $config['i18n'] ) && is_string( $config['i18n'] ) ) {
				$keyMessage = Message::newFromKey( $config['i18n'] )->plain();
			} else {
				$keyMessage = $key;
			}
			$data[] = "$keyMessage: $value";
		}
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => $data
		] );
	}

	/**
	 * @return Profile|null
	 */
	protected function getProfile() {
		$entityFactory = $this->services->getService( 'BSSocialProfileEntityFactory' );
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
	 * @param User $user Aggregate 'deleted' user that also performs the deletion
	 * @return Status
	 */
	protected function deleteEntityPage( User $user ) {
		$status = Status::newGood();
		if ( !$this->getProfile() || !$this->getProfile()->exists() ) {
			return $status;
		}
		$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()
			->newFromTitle( $this->getProfile()->getTitle() );
		$status->merge(
			$wikipage->doDeleteArticleReal( '', $this->getMaintenanceUser(), true )
		);

		if ( $status->isGood() ) {
			$job = new SearchJob(
				$this->getProfile()->getTitle()
			);

			MediaWikiServices::getInstance()->getJobQueueGroup()->push(
				$job
			);
		}

		return $status;
	}

	/**
	 *
	 * @return User
	 */
	private function getMaintenanceUser() {
		return $this->services->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
	}
}
