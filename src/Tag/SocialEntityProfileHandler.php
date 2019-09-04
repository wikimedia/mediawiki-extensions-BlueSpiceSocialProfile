<?php

namespace BlueSpice\Social\Profile\Tag;

use BlueSpice\Services;
use BlueSpice\Tag\Handler;
use BlueSpice\Social\Profile\Entity\Profile as Entity;
use BlueSpice\Social\Profile\Renderer\Entity\Profile as EntityRenderer;

class SocialEntityProfileHandler extends Handler {
	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	public function __construct( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$factory = Services::getInstance()->getService(
			'BSSocialProfileEntityFactory'
		);
		$user = \User::newFromName( $processedArgs['username'] );
		if( !$user ) {
			new \MWException(
				"Invalid user for with username '{$processedArgs['username']}'"
			);
		}
		$this->entity = $factory->newFromUser( $user );
		if( !$this->entity instanceof Entity ) {
			new \MWException(
				"Non existent or invalid profile for '{$processedArgs['username']}'"
			);
		}

	}

	public function handle() {
		if( !$this->entity->userCan( 'read', $this->parser->getUser() ) ) {
			return "";
		}
		return $this->entity->getRenderer()->render(
			$this->processedArgs[EntityRenderer::RENDER_TYPE]
		);
	}
}