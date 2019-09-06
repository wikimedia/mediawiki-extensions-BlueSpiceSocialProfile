<?php

namespace BlueSpice\Social\Profile\Field;

use Message;
use BlueSpice\Social\Profile\Field;

class Title extends Field {

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		$msg = Message::newFromKey( 'bs-social-profile-field-title-value' );
		return $msg->params( $this->user->getName() )->text();
	}
}
