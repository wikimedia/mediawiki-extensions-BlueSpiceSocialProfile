<?php

namespace BlueSpice\Social\Profile\Tag;

use BlueSpice\Social\Profile\Entity\Profile as Entity;
use BlueSpice\Social\Profile\Renderer\Entity\Profile as EntityRenderer;
use BlueSpice\Tag\Handler;
use MediaWiki\MediaWikiServices;
use MWException;
use Parser;
use PPFrame;

class SocialEntityProfileHandler extends Handler {
	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$services = MediaWikiServices::getInstance();
		$factory = $services->getService( 'BSSocialProfileEntityFactory' );
		$user = $services->getUserFactory()->newFromName( $processedArgs['username'] );
		if ( !$user ) {
			new MWException(
				"Invalid user for with username '{$processedArgs['username']}'"
			);
		}
		$this->entity = $factory->newFromUser( $user );
		if ( !$this->entity instanceof Entity ) {
			new MWException(
				"Non existent or invalid profile for '{$processedArgs['username']}'"
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$user = MediaWikiServices::getInstance()->getUserFactory()
			->newFromUserIdentity( $this->parser->getUserIdentity() );
		if ( !$this->entity->userCan( 'read', $user ) ) {
			return "";
		}
		return $this->entity->getRenderer()->render(
			$this->processedArgs[EntityRenderer::RENDER_TYPE]
		);
	}
}
