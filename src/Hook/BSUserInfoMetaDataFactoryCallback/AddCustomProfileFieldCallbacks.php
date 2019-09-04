<?php
namespace BlueSpice\Social\Profile\Hook\BSUserInfoMetaDataFactoryCallback;

use BlueSpice\UserInfo\Hook\BSUserInfoMetaDataFactoryCallback;

class AddCustomProfileFieldCallbacks extends BSUserInfoMetaDataFactoryCallback {

	protected function skipProcessing() {
		if( $this->callback ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		if( !$field = $factory->factory( $this->name, $this->user ) ) {
			return true;
		}
		if( $field->isHidden() ) {
			return true;
		}

		$this->callback = "\\BlueSpice\\Social\\Profile\\MetaData\\CustomField::getInstance";
		return true;
	}
}