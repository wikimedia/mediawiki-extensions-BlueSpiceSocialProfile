<?php

namespace BlueSpice\Social\Profile\EntityListContext;

use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Social\Profile\Entity\Profile;

class UserProfile extends \BlueSpice\Social\EntityListContext {

	/**
	 * Owner of the user page
	 * @var \User
	 */
	protected $owner = null;

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( \IContextSource $context, \Config $config, \User $user = null, Profile $entiy = null, \User $owner = null ) {
		parent::__construct( $context, $config, $user, $entiy );
		$this->owner = $owner;
		if( !$this->owner ) {
			$this->owner = $this->context->getUser();
		}
	}

	protected function getOwnerFilter() {
		return (object)[
			Numeric::KEY_PROPERTY => Profile::ATTR_OWNER_ID,
			Numeric::KEY_VALUE => $this->owner->getId(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];
	}

	public function getFilters() {
		return array_merge( 
			parent::getFilters(),
			[ $this->getOwnerFilter() ]
		);
	}

	public function getLimit() {
		return 10;
	}

	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Profile::ATTR_OWNER_ID ]
		);
	}
}
