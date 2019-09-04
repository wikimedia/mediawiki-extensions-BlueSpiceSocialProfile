<?php

namespace BlueSpice\Social\Profile\Hook\HtmlPageLinkRendererBegin;

class SetProfileLinksKnown extends \BlueSpice\Hook\HtmlPageLinkRendererBegin {

	const ATTR_PROCCESSED = 'knownProfileProccessed';

	protected function skipProcessing() {
		if( isset( $this->extraAttribs[static::ATTR_PROCCESSED] ) ) {
			return true;
		}

		if( !$title = \Title::newFromLinkTarget( $this->target ) ) {
			return true;
		}
		if( $title->getNamespace() !== NS_USER ) {
			return true;
		}
		if( $title->isKnown() ) {
			return true;
		}
		if( $title->exists() || $title->isSubpage() ) {
			return true;
		}
		if( !$user = \User::newFromName( $title->getText() ) ) {
			return true;
		}
		if( $user->isAnon() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		//kinda hacky, but the known parameter can not be changed anymore :/
		$this->ret = $this->linkRenderer->makeKnownLink(
			$this->target,
			$this->text,
			array_merge( $this->extraAttribs, [ static::ATTR_PROCCESSED => true ] ),
			$this->query
		);
		return false;
	}

}
