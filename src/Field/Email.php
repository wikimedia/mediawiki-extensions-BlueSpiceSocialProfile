<?php

namespace BlueSpice\Social\Profile\Field;

use BlueSpice\Social\Profile\Field;
use MediaWiki\MediaWikiServices;

class Email extends Field {

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->user->getEmail();
	}

	/**
	 *
	 * @return bool
	 */
	public function isHidden() {
		return !MediaWikiServices::getInstance()->getUserOptionsLookup()
			->getBoolOption( $this->user, 'bs-social-profile-infoshowemail' );
	}
}
