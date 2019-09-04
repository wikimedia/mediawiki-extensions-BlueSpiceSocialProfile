<?php

namespace BlueSpice\Social\Profile\Field;
use BlueSpice\Data\FieldType;

class Name extends \BlueSpice\Social\Profile\Field {

	public function getValue() {
		return empty( $this->user->getRealName() )
			? $this->user->getName()
			: $this->user->getRealName();
	}
}

