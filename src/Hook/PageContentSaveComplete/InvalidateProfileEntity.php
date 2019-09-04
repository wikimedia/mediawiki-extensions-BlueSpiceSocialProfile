<?php
namespace BlueSpice\Social\Profile\Hook\PageContentSaveComplete;

use BlueSpice\Hook\PageContentSaveComplete;
use BlueSpice\Social\Profile\Entity\Profile;

class InvalidateProfileEntity extends PageContentSaveComplete {

	protected function skipProcessing() {
		if( $this->wikipage->getTitle()->getNamespace() !== NS_USER ) {
			return true;
		}
		if( $this->wikipage->getTitle()->isTalkPage() ) {
			return true;
		}
		if( $this->wikipage->getTitle()->isSubpage() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$entityFactory = $this->getServices()->getService(
			'BSSocialProfileEntityFactory'
		);
		$entity = $entityFactory->newFromUser(
			\User::newFromName( $this->wikipage->getTitle()->getText() )
		);
		if( !$entity instanceof Profile ) {
			//do not fatal - here is something wrong very bad!
			return true;
		}
		if( !$entity->exists() ) {
			return true;
		}
		$entity->invalidateCache();
		return true;
	}
}