<?php
namespace BlueSpice\Social\Profile\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;
use BlueSpice\Social\Profile\Entity\Profile;

class InvalidateProfileEntity extends PageSaveComplete {

	protected function skipProcessing() {
		if ( $this->wikiPage->getTitle()->getNamespace() !== NS_USER ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->isTalkPage() ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->isSubpage() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$services = $this->getServices();
		$entityFactory = $services->getService( 'BSSocialProfileEntityFactory' );
		$entity = $entityFactory->newFromUser(
			$services->getUserFactory()->newFromName( $this->wikiPage->getTitle()->getText() )
		);
		if ( !$entity instanceof Profile ) {
			// do not fatal - here is something wrong very bad!
			return true;
		}
		if ( !$entity->exists() ) {
			return true;
		}
		$entity->invalidateCache();
		return true;
	}
}
