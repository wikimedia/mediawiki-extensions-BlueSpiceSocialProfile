<?php

namespace BlueSpice\Social\Profile\Field;

class Title extends \BlueSpice\Social\Profile\Field {

	public function getValue() {
		$msg = \Message::newFromKey( 'bs-social-profile-field-title-value' );
		return $msg->params( $this->user->getName() )->text();
	}
}

