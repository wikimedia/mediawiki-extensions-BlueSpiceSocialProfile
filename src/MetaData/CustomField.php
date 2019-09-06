<?php
namespace BlueSpice\Social\Profile\MetaData;

use BlueSpice\Services;
use BlueSpice\UserInfo\MetaData;
use BlueSpice\Social\Profile\ICustomField;

class CustomField extends MetaData {

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->getProfileCustomField()->getLabel();
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->getProfileCustomField()->getValue();
	}

	/**
	 *
	 * @return ICustomField
	 */
	protected function getProfileCustomField() {
		$factory = Services::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		return $factory->factory( $this->name, $this->user );
	}

}
