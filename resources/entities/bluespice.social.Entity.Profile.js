/**
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BSSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityProfile = function( $el, type, data ) {
	var me = this;
	me.PROFILE_FIELDS_CONTAINER = 'bs-social-profile-fields';
	bs.social.Entity.call( this, $el, type, data );
};
OO.initClass( bs.social.EntityProfile );
OO.inheritClass( bs.social.EntityProfile, bs.social.Entity );

bs.social.EntityProfile.prototype.makeActionMenu = function() {
	//TODO!
	var me = this;
	if( !me.exists() ) {
		return null;
	}
	var actions = me.data.get('actions', []);

	if( !actions || actions.length < 1 ) {
		return null;
	}
	var html = '';
	for( var i = 0; i < actions.length; i++ ) {
		if( actions[i] === "edithiddenfields" ) {
			continue;
		}
		if( actions[i] === "read" ) {
			continue;
		}
		if( actions[i] === 'source' ) {
			continue;
		}
		if( this.getData().outputtype !== 'Page' ) {
			if( actions[i] === "changeimage" ) {
				continue;
			}
			if( actions[i] === "editprofilefields" ) {
				continue;
			}
		}
		html += "<a href='#' class='bs-social-entity-" + actions[i] + "'>" + actions[i] + "</a>";
	}
	var $actionsContainer = me.getContainer( me.ACTIONS_CONTAINER );
	var $actions = $actionsContainer.children(
		'.bs-social-entity-actions-content'
	).first();
	$actions.parent().show();
	$actions.html( html );
	$actions.find('a.bs-social-entity-changeimage').html(
		mw.message( "bs-socialprofile-entityaction-changeimage").plain()
	);
	$actions.find('.bs-social-entity-changeimage').first().on('click', function(e) {
		e.preventDefault();
		mw.loader.using( ['mediawiki.notify','ext.bluespice.extjs'] ).done( function() {
			Ext.onReady( function() {
				Ext.require( 'BS.Avatars.SettingsWindow', function() {
					BS.Avatars.SettingsWindow.show();
				} );
			} );
		} );
		return false;
	});

	$actions.find('a.bs-social-entity-edit').html(
		mw.message( "bs-social-entityaction-edit").plain()
	);
	$actions.find('a.bs-social-entity-edit').click( function(e) {
		e.preventDefault();
		window.location.href =
			mw.config.get( "wgScript" )
			+ "/"
			+ me.data.get('relatedtitle')
			+ "?action=edit"
		;
		return false;
	});

	$actions.find('a.bs-social-entity-source').html( me.id );
	$actions.find('a.bs-social-entity-source').click( function(e) {
		if( me.editmode ) {
			e.preventDefault();
			return false;
		}
		window.location = mw.util.getUrl( 'BSSocial:' + me.id );
		e.preventDefault();
		return false;
	});

	$actions.find('a.bs-social-entity-editprofilefields').html(
		mw.message( "bs-social-entityaction-editprofilefields").plain()
	);
	$actions.find('a.bs-social-entity-editprofilefields').click( function(e) {
		if( me.editmode ) {
			e.preventDefault();
			return false;
		}
		me.makeEditMode();
		e.preventDefault();
		return false;
	});
	var $btn = $actionsContainer.find( '.bs-social-entity-actions-btn' ).first();
	$( document ).on( 'click', function( e ) {
		if( $( e.target ).length < 0 || $btn.length < 0 ) {
			return true;
		}
		if( $( e.target )[0] !== $btn[0] ) {
			$actions.hide();
			return true;
		}
		e.stopPropagation();
		if( $actions.is( ':visible' ) ) {
			$actions.hide();
			return false;
		}
		$actions.show();
		return false;
	});
	return null;
};



bs.social.EntityProfile.prototype.makeEditor = function() {
	return new bs.social.EntityEditorProfile( this.getEditorConfig(), this );
};

bs.social.EntityProfile.prototype.getEditorConfig = function() {
	return {};
};

bs.social.EntityProfile.prototype.save = function( newdata ) {
	var me = this;
	var dfd = $.Deferred();
	if( !me.editmode ) {
		dfd.reject( me );
		return dfd;
	}

	var taskData = me.getData();
	for( var i in newdata ) {
		taskData[i] = newdata[i];
	};

	me.showLoadMask();
	bs.api.tasks.execSilent( 'social', 'editEntity', taskData )
	.done( function(response) {
		if( !response.success ) {
			if( response.message && response.message !== '' ) {
				OO.ui.alert( response.message );
			}
			dfd.resolve( me );
			me.hideLoadMask();
			return;
		}
		if( me.getData().outputtype === 'Page' ) {
			window.location = mw.util.getUrl(
				mw.config.get( 'wgPageName' )
			);
		} else {
			me.replaceEL( response.payload.view );
		}
		dfd.resolve( me );
	})
	.then(function(){
		bs.social.init();
		$( ".bs-social-entityspawner-new" ).removeClass( "bs-social-entityspawner-new" );
		if( me.getData().outputtype !== 'Page' ) {
			me.hideLoadMask();
		}
	});

	return dfd;
};

bs.social.EntityProfile.static.name = "\\BlueSpice\\Social\\Profile\\Entity\\Profile";
bs.social.factory.register( bs.social.EntityProfile );