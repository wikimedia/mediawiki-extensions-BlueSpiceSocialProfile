<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [

	'BSSocialProfileEntityFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		return new \BlueSpice\Social\Profile\ProfileFactory(
			$registry,
			$services->getService( 'BSEntityConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSSocialProfileFieldsFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\Profile\FieldsFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSSocialProfileCustomFieldsFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceSocialProfileCustomFieldTypesRegistry'
		);
		return new \BlueSpice\Social\Profile\CustomFieldsFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$registry
		);
	},
];
