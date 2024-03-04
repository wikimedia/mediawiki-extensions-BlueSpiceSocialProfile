<?php

namespace BlueSpice\Social\Profile\Hook\BSUsageTrackerRegisterCollectors;

use BlueSpice\Social\Data\Entity\Store;
use BlueSpice\Social\Profile\Entity\Profile;
use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;
use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class NoOfCustomSocialProfiles extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$store = new Store;
		$res = $store->getReader( $this->getContext() )
			->read(	new ReaderParams( $this->getParams() ) );

		$noOfCustomSocialProfiles = 0;
		foreach ( $res->getRecords() as $record ) {
			if ( $record->get( 'social-profile-department' ) ||
				$record->get( 'social-profile-function' ) ||
				$record->get( 'social-profile-location' ) ||
				$record->get( 'social-profile-phone' )
			) {
				$noOfCustomSocialProfiles++;
			}
		}

		$this->collectorConfig['no-of-custom-social-profiles'] = [
			'class' => 'Basic',
			'config' => [
				'identifier' => 'no-of-custom-social-profiles',
				'internalDesc' => 'Number of custom Social Profiles',
				'count' => $noOfCustomSocialProfiles
			]
		];
	}

	/**
	 * @return array
	 */
	protected function getParams(): array {
		return [
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_FILTER => $this->getFilter()
		];
	}

	/**
	 * @return array
	 */
	protected function getFilter(): array {
		return [ [
			StringValue::KEY_PROPERTY => Profile::ATTR_TYPE,
			StringValue::KEY_TYPE => FieldType::STRING,
			StringValue::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
			StringValue::KEY_VALUE => Profile::TYPE,
		] ];
	}
}
