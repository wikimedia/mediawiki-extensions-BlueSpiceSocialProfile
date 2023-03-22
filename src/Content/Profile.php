<?php
namespace BlueSpice\Social\Profile\Content;

use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\MediaWikiServices;

class Profile extends \WikitextContent {

	/**
	 *
	 * @var string
	 */
	public $mModelID = CONTENT_MODEL_WIKITEXT;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 * @return string
	 */
	public function getModel() {
		return CONTENT_MODEL_WIKITEXT;
	}

	/**
	 *
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_BSSOCIALPROFILE ) {
		parent::__construct( $text, CONTENT_MODEL_WIKITEXT );
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * Returns a ParserOutput object containing information derived from this content.
	 * Most importantly, unless $generateHtml was false, the return value contains an
	 * HTML representation of the content.
	 *
	 * Subclasses that want to control the parser output may override this, but it is
	 * preferred to override fillParserOutput() instead.
	 *
	 * Subclasses that override getParserOutput() itself should take care to call the
	 * ContentGetParserOutput hook.
	 *
	 * @since 1.24
	 *
	 * @param \Title $title Context title for parsing
	 * @param int|null $revId Revision ID (for {{REVISIONID}})
	 * @param \ParserOptions|null $options Parser options
	 * @param bool $generateHtml Whether or not to generate HTML
	 * @param bool $bForceOrigin
	 *
	 * @return ParserOutput Containing information derived from this content.
	 */
	public function getParserOutput( \Title $title, $revId = null, \ParserOptions $options = null,
		$generateHtml = true, $bForceOrigin = false ) {
		if ( $options === null ) {
			$options = \ParserOptions::newFromAnon();
		}

		$output = new \ParserOutput();
		$output->setExtensionData( 'ForceOrigin', $bForceOrigin );

		if ( $this->services->getHookContainer()->run( 'ContentGetParserOutput',
			[ $this, $title, $revId, $options, $generateHtml, &$output ] ) ) {

			// Save and restore the old value, just in case something is reusing
			// the ParserOptions object in some weird way.
			$oldRedir = $options->getRedirectTarget();
			$options->setRedirectTarget( $this->getRedirectTarget() );

			$discussionHandler = new ProfileHandler( $this->getModel() );
			$cpoParams = new ContentParseParams( $title, $revId, $options, $generateHtml );
			$discussionHandler->fillParserOutputInternal( $this, $cpoParams, $output );
			$options->setRedirectTarget( $oldRedir );
		}

		$this->services->getHookContainer()->run(
			'ContentAlterParserOutput',
			[
				$this,
				$title,
				$output
			]
		);

		return $output;
	}
}
