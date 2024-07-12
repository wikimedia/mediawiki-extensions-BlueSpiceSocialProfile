<?php

namespace BlueSpice\Social\Profile\HookHandler;

use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUILessVarsInit;

class CommonUserInterface implements MWStakeCommonUILessVarsInit {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUILessVarsInit( $lessVars ): void {
		$lessVars->setVar( 'bs-color-lighten-information', '#BABABA' );
	}
}
