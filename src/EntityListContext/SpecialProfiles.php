<?php

namespace BlueSpice\Social\Profile\EntityListContext;

use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Social\Profile\Entity\Profile;

class SpecialProfiles extends \BlueSpice\Social\EntityListContext {

	public function getLimit() {
		return 10;
	}

	protected function getTypeFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Profile::ATTR_TYPE,
			ListValue::KEY_VALUE => [ Profile::TYPE ],
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => \BlueSpice\Data\FieldType::LISTVALUE
		];
	}

	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Profile::ATTR_TYPE ]
		);
	}

	protected function getSortProperty() {
		return Profile::ATTR_TIMESTAMP_CREATED;
	}

}
