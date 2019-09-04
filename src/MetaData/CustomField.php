<?php
namespace BlueSpice\Social\Profile\MetaData;
use BlueSpice\UserInfo\MetaData;
use BlueSpice\Social\Profile\ICustomField;

class CustomField extends MetaData {

	public function getLabel() {
		return $this->getProfileCustomField()->getLabel();
	}

	public function getValue() {
		return $this->getProfileCustomField()->getValue();
	}

	/**
	 *
	 * @return ICustomField
	 */
	protected function getProfileCustomField() {
		$factory = \MediaWiki\MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		return $factory->factory( $this->name, $this->user );
	}

}
