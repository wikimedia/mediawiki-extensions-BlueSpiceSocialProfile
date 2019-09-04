<?php

namespace BlueSpice\Social\Profile\Tag;

use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Social\Profile\Entity\Profile as Entity;
use BlueSpice\Social\Profile\Renderer\Entity\Profile as EntityRenderer;

class SocialEntityProfile extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getContainerElementName() {
		return 'div';
	}

	public function needsParsedInput() {
		return false;
	}

	public function needsParseArgs() {
		return true;
	}

	public function getMarkerType() {
		return new NoWiki();
	}

	public function getInputDefinition() {
		return null;
	}

	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::STRING,
				'username',
				''
			),
			new ParamDefinition(
				ParamType::STRING,
				EntityRenderer::RENDER_TYPE,
				EntityRenderer::RENDER_TYPE_SHORT
			),
		];
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		return new SocialEntityProfileHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:socialentityprofile',
		];
	}

}
