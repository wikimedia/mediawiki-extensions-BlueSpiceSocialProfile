<?php

namespace BlueSpice\Social\Profile\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddSocialEntityProfileTag extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:socialentityprofile',
			'type' => 'tag',
			'name' => 'socialentityprofile',
			'desc' => $this->msg( 'bs-socialprofile-tag-socialentityprofile-desc' )->text(),
			'examples' => [
				[ 'code' => '<bs:socialentityprofile username="WikiSysop" rendertype="Short"/>' ]
			],
			'previewable' => false,
			'mwvecommand' => 'socialentityprofileCommand',
			'helplink' => $this->getHelpLink()
		];

		return true;
	}

	/**
	 *
	 * @return string
	 */
	private function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceSocialProfile' )->getUrl();
	}

}
