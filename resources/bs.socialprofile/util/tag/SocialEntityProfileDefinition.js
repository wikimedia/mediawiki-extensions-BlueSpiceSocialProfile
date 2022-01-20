bs.util.registerNamespace( 'bs.socialprofile.util.tag' );
bs.socialprofile.util.tag.SocialEntityProfileDefinition = function BsVecUtilTagSocialEntityProfileDefinition() {
	bs.socialprofile.util.tag.SocialEntityProfileDefinition.super.call( this );
};

OO.inheritClass( bs.socialprofile.util.tag.SocialEntityProfileDefinition, bs.vec.util.tag.Definition );

bs.socialprofile.util.tag.SocialEntityProfileDefinition.prototype.getCfg = function() {
	var cfg = bs.socialprofile.util.tag.SocialEntityProfileDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'Socialentityprofile',
		name: 'socialentityprofile',
		tagname: 'bs:socialentityprofile',
		descriptionMsg: 'bs-socialprofile-tag-socialentityprofile-desc',
		menuItemMsg: 'bs-socialprofile-ve-socialentityprofileinspector-title',
		attributes: [{
			name: 'username',
			labelMsg: 'bs-socialprofile-ve-socialentityprofile-attr-username-label',
			helpMsg: 'bs-socialprofile-ve-socialentityprofile-attr-username-help',
			type: 'text',
			default: 'WikiSysop'
		},{
			name: 'rendertype',
			labelMsg: 'bs-socialprofile-ve-socialentityprofile-attr-rendertype-label',
			helpMsg: 'bs-socialprofile-ve-socialentityprofile-attr-rendertype-help',
			type: 'dropdown',
			default: 'Short',
			options: [
				{ data: 'List', label: mw.message( 'bs-socialprofile-tag-socialentityprofile-attr-rendertype-option-list' ).plain() },
				{ data: 'Short', label: mw.message( 'bs-socialprofile-tag-socialentityprofile-attr-rendertype-option-short' ).plain() },
				{ data: 'Default', label: mw.message( 'bs-socialprofile-tag-socialentityprofile-attr-rendertype-option-default' ).plain() },
				{ data: 'Page', label: mw.message( 'bs-socialprofile-tag-socialentityprofile-attr-rendertype-option-page' ).plain() }
			]
		}]
	});
};

bs.vec.registerTagDefinition(
	new bs.socialprofile.util.tag.SocialEntityProfileDefinition()
);
