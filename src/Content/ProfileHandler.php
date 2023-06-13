<?php
namespace BlueSpice\Social\Profile\Content;

use BlueSpice\Social\Profile\Entity\Profile;
use Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\MediaWikiServices;
use ParserOutput;
use Title;

class ProfileHandler extends \WikiTextContentHandler {

	/**
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_BSSOCIALPROFILE ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	public function getContentClass() {
		return "\\BlueSpice\\Social\\Profile\\Content\\Profile";
	}

	/**
	 * Undocumented function
	 *
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$output The output object to fill (reference).
	 * @return ParserOutput
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		parent::fillParserOutput( $content, $cpoParams, $output );
		$dbKey = $cpoParams->getPage()->getDBkey();
		$title = Title::newFromDBkey( $dbKey );

		if ( $output->getExtensionData( 'ForceOrigin' ) ) {
			return $output;
		}
		$oUser = MediaWikiServices::getInstance()->getUserFactory()->newFromName( $title->getText() );
		if ( $oUser === null ) {
			// ! UserName is Invalid !
			// (e.g. if it contains illegal characters or is an IP address)
			return $output;
		}

		$entityFactory = MediaWikiServices::getInstance()->getService( 'BSSocialProfileEntityFactory' );
		$entity = $entityFactory->newFromUser( $oUser );
		if ( !$entity instanceof Profile ) {
			return $output;
		}
		$output = $this->addOutputsCategory( $entity, $output );
		$output = $this->addOutputsRenderedPage( $entity, $cpoParams->getGenerateHtml(), $output );

		return $output;
	}

	/**
	 * @param Profile $entity
	 * @param ParserOutput $output
	 * @return ParserOutput
	 */
	private function addOutputsCategory( $entity, $output ) {
		if ( $entity->getRelatedTitle()->exists() ) {
			$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()
				->newFromTitle( $entity->getRelatedTitle() );

			$cpoParams = new ContentParseParams( $entity->getRelatedTitle() );
			$contentHandler = $wikiPage->getContent()->getContentHandler();
			$categories = $contentHandler->getParserOutput( $wikiPage->getContent(), $cpoParams )->getCategories();

			foreach ( $categories as $category => $key ) {
				$output->addCategory( $category, $key );
			}
		}

		return $output;
	}

	/**
	 * @param Profile $entity
	 * @param bool $generateHtml
	 * @param ParserOutput $output
	 * @return ParserOutput
	 */
	private function addOutputsRenderedPage( $entity, $generateHtml, $output ) {
		$sText = $entity->getRenderer()->render( 'Page' );
		$sTitle = strip_tags( $entity->getHeader() );
		$output->setTitleText( $sTitle );
		if ( $generateHtml ) {
			$output->setText( $sText );
			$output->addModuleStyles( [ 'mediawiki.content.json' ] );
		} else {
			$output->setText( $sText );
		}
		return $output;
	}
}
