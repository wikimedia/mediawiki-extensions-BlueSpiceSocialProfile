<?php

/**
 * BSSociaEntityProfile class for BSSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialProfile
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Profile\Entity;

use MediaWiki\MediaWikiServices;
use BlueSpice\Social\Entity\Page;
use BlueSpice\Social\Profile\ICustomField;
use BlueSpice\Social\Profile\IField;

/**
 * BSSociaEntityProfile class for BSSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocialProfile
 */
class Profile extends Page {
	const TYPE = 'profile';
	protected $sBaseTitleContent = null;

	/**
	 * @deprecated since version 3.0.0 - Use Service
	 * (BlueSpiceSocialProfileEntityFactory)->newFromUser instead
	 * @param \User $oUser
	 * @return type
	 */
	public static function newFromUser( \User $oUser ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		$entityFactory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		return $entityFactory->newFromUser( $oUser );
	}

	/**
	 * @param \stdClass $data
	 */
	public function setValuesByObject( \stdClass $data ) {
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach( $fieldDefinitions as $name => $definition ) {
			if( isset( $data->{$name} ) ) {
				$this->set( $name, $data->{$name} );
			}
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileFieldsFactory'
		);
		foreach( $fieldDefinitions as $name => $definition ) {
			if( isset( $data->{$name} ) ) {
				$this->set( $name, $data->{$name} );
			}
		}
		return parent::setValuesByObject( $data );
	}

	public function getBaseTitleContent() {
		if( $this->sBaseTitleContent ) {
			return $this->sBaseTitleContent;
		}
		$this->sBaseTitleContent = '';

		if( !$this->getRelatedTitle()->exists() ) {
			return $this->sBaseTitleContent;
		}
		$oWikiPage = \WikiPage::factory( $this->getRelatedTitle() );
		try {
			$oOutput = $oWikiPage->getContent()->getParserOutput(
				$this->getRelatedTitle(),
				null,
				\ParserOptions::newFromContext( \RequestContext::getMain() ),
				true,
				true
			);
		} catch( \Exception $e ) {
			//sometimes parser recursion - unfortunately this can not be solved
			//due to the randomnes of the content model -.-
			$oOutput = null;
		}

		if( !$oOutput ) {
			return $this->sBaseTitleContent;
		}
		$this->sBaseTitleContent = $oOutput->getText();
		return $this->sBaseTitleContent;
	}

	/**
	 * Returns the Message object for the entity header
	 * @param Message $oMsg
	 * @return Message
	 */
	public function getHeader( $oMsg = null ) {
		$oMsg = parent::getHeader( $oMsg );
		return $oMsg->params([
			$this->getRelatedTitle()->getFullText()
		]);
	}

	/**
	 * Gets the BSSociaEntityPage attributes formated for the api
	 * @return object
	 */
	public function getFullData( $a = array() ) {
		$fields = [];
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileFieldsFactory'
		);
		foreach( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if( !$field instanceof IField ) {
				continue;
			}
			$fields[$name] = $this->get( $name );
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if( !$field instanceof ICustomField ) {
				continue;
			}
			$fields[$name] = $this->get( $name, $field->getDefault() );
		}
		return parent::getFullData( array_merge( $a, $fields ) );
	}

	public function getRelatedTitle() {
		if( $this->relatedTitle ) {
			return $this->relatedTitle;
		}
		$this->relatedTitle = $this->getOwner()->getUserPage();
		return $this->relatedTitle instanceof \Title
			? $this->relatedTitle
			: parent::getRelatedTitle();
	}

	public function getActions( array $aActions = array(), \User $oUser = null ) {
		if( !$oUser ) {
			$oUser = \RequestContext::getMain()->getUser();
		}
		$aActions = parent::getActions( $aActions, $oUser );

		$oStatus = $this->userCan( 'editothers', $oUser );
		if( $this->userIsOwner( $oUser ) || $oStatus->isOK() ) {
			$aActions[] = 'changeimage';
			$aActions[] = 'editprofilefields';
		}
		if( $oStatus->isOK() ) {
			$aActions[] = 'edithiddenfields';
		}
		return $aActions;
	}

	public function render( $sType = 'Default', $bNoCache = false ) {
		if( $sType == 'Page' && !$bNoCache ) {
			$bNoCache = true;
		}
		return parent::render( $sType, $bNoCache );
	}

	/**
	 * Saves the current BSSocialEntity
	 * @return Status
	 */
	public function save( \User $oUser = null, $aOptions = array() ) {
		if( !$this->getOwner() || $this->getOwner()->isAnon() ) {
			return \Status::newFatal( wfMessage(
				'bs-socialprofile-entity-fatalstatus-save-invaliduser'
			));
		}
		$fieldDefinitions = $this->getConfig()->get(
			'ProfileCustomFieldsDefinitions'
		);
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		foreach( $fieldDefinitions as $name => $definition ) {
			$field = $factory->factory( $name, $this->getOwner() );
			if( !$field instanceof ICustomField ) {
				continue;
			}
			$status = $field->validate(
				$this->get( $name, $field->getDefault() )
			);
			if( !$status->isOK() ) {
				return $status;
			}
			$this->set( $name, $status->getValue() );
		}
		return parent::save( $this->getOwner(), $aOptions );
	}
}