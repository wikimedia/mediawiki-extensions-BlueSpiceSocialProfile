<?php

namespace BlueSpice\Social\Profile\Field;

class Email extends \BlueSpice\Social\Profile\Field {

	public function getValue() {
		return $this->user->getEmail();
	}

	public function isHidden() {
		return !$this->user->getOption(
			'bs-social-profile-infoshowemail'
		);
	}
}

