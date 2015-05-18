/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	
		config.language = 'pt';
		config.uiColor = '#efefef';
		/*
		config.extraPlugins = 'wordcount';		
		
		config.wordcount = {

			    // Whether or not you want to show the Word Count
			    showWordCount: false,

			    // Whether or not you want to show the Char Count
			    showCharCount: true,
			    
			    // Option to limit the characters in the Editor
			    charLimit: '500',
			  
			    // Option to limit the words in the Editor
			    wordLimit: '1000'
			};
		*/

// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' },
		{ name: 'CharCount' }
		
	];
	
	
	

	
	
	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript,Source,Link,Unlink,Anchor,Maximize,About,Styles,Image,Format';
	config.removePlugins = 'elementspath'; config.resize_enabled = false;
};
