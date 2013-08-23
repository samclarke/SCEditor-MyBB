window.clickableEditor = {};

jQuery(document).ready(function($) {
	/********************************************
	 * Update editor to use align= as alignment *
	 ********************************************/
	$.sceditor.plugins.bbcode.bbcode
		.set("align", {
			html: function(element, attrs, content) {
				return '<div align="' + (attrs.defaultattr || 'left') + '">' + content + '</div>';
			},
			isInline: false
		})
		.set("center", { format: "[align=center]{0}[/align]" })
		.set("left", { format: "[align=left]{0}[/align]" })
		.set("right", { format: "[align=right]{0}[/align]" })
		.set("justify", { format: "[align=justify]{0}[/align]" });

	$.sceditor.command
		.set("center", { txtExec: ["[align=center]", "[/align]"] })
		.set("left", { txtExec: ["[align=left]", "[/align]"] })
		.set("right", { txtExec: ["[align=right]", "[/align]"] })
		.set("justify", { txtExec: ["[align=justify]", "[/align]"] });



	/************************************************
	 * Update font to support MyBB's BBCode dialect *
	 ************************************************/
	$.sceditor.plugins.bbcode.bbcode
		.set("list", {
			html: function(element, attrs, content) {
				var type = (attrs.defaultattr === '1' ? 'ol' : 'ul');

				if(attrs.defaultattr === 'a')
					type = 'ol type="a"';

				return '<' + type + '>' + content + '</' + type + '>';
			},

			breakAfter: false
		})
		.set("ul", { format: "[list]{0}[/list]" })
		.set("ol", {
			format: function($elm, content) {
				var type = ($elm.attr('type') === 'a' ? 'a' : '1');

				return '[list=' + type + ']' + content + '[/list]';
			}
		})
		.set("li", { format: "[*]{0}", excludeClosing: true })
		.set("*", { excludeClosing: true, isInline: false });

	$.sceditor.command
		.set("bulletlist", { txtExec: ["[list]\n[*]", "\n[/list]"] })
		.set("orderedlist", { txtExec: ["[list=1]\n[*]", "\n[/list]"] });



	/***********************************************************
	 * Update size tag to use xx-small-xx-large instead of 1-7 *
	 ***********************************************************/
	$.sceditor.plugins.bbcode.bbcode.set('size', {
		format: function($elm, content) {
			var	fontSize,
				sizes = ['xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large'],
				size  = $elm.data('scefontsize');

			if(!size)
			{
				fontSize = $elm.css('fontSize');

				// Most browsers return px value but IE returns 1-7
				if(fontSize.indexOf("px") > -1) {
					// convert size to an int
					fontSize = fontSize.replace("px", "") - 0;
					size     = 1;

					if(fontSize > 9)
						size = 2;
					if(fontSize > 12)
						size = 3;
					if(fontSize > 15)
						size = 4;
					if(fontSize > 17)
						size = 5;
					if(fontSize > 23)
						size = 6;
					if(fontSize > 31)
						size = 7;
				}
				else
					size = (~~fontSize) + 1;

				if(size > 7)
					size = 7;
				if(size < 1)
					size = 1;

				size = sizes[size-1];
			}

			return '[size=' + size + ']' + content + '[/size]';
		},
		html: function(token, attrs, content) {
			return '<span data-scefontsize="' + attrs.defaultattr + '" style="font-size:' + attrs.defaultattr + '">' + content + '</span>';
		}
	});

	$.sceditor.command.set('size', {
		_dropDown: function(editor, caller, callback) {
			var	content   = $("<div />"),
				clickFunc = function (e) {
					callback($(this).data('size'));
					editor.closeDropDown(true);
					e.preventDefault();
				};

			for (var i=1; i < 7; i++)
				content.append($('<a class="sceditor-fontsize-option" data-size="' + i + '" href="#"><font size="' + i + '">' + i + '</font></a>').click(clickFunc));

			editor.createDropDown(caller, "fontsize-picker", content);
		},
		txtExec: function(caller) {
			var	editor = this,
				sizes = ['xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large'];

			$.sceditor.command.get('size')._dropDown(
				editor,
				caller,
				function(size) {
					size = (~~size);
					size = (size > 7) ? 7 : ( (size < 1) ? 1 : size );

					editor.insertText("[size=" + sizes[size] + "]", "[/size]");
				}
			);
		}
	});



	/********************************************
	 * Update quote to support pid and dateline *
	 ********************************************/
	$.sceditor.plugins.bbcode.bbcode.set("quote", {
		format: function(element, content) {
			var	author = '',
				$elm  = $(element),
				$cite = $elm.children("cite").first();

			if($cite.length === 1 || $elm.data("author")) {
				author = $cite.text() || $elm.data("author");

				$elm.data("author", author);
				$cite.remove();

				$elm.children("cite").replaceWith(function() {
					return $(this).text();
				});

				content	= this.elementToBbcode($(element));
				author  = '=' + author;
			}

			if($elm.data('pid'))
				author += " pid='" + $elm.data('pid') + "'";

			if($elm.data('dateline'))
				author += " dateline='" + $elm.data('dateline') + "'";

			return '[quote' + author + ']' + content + '[/quote]';
		},
		html: function(token, attrs, content) {
			var data = '';

			if(attrs.pid)
				data += ' data-pid="' + attrs.pid + '"';

			if(attrs.dateline)
				data += ' data-dateline="' + attrs.dateline + '"';

			if(typeof attrs.defaultattr !== "undefined")
				content = '<cite>' + attrs.defaultattr + '</cite>' + content;

			return '<blockquote' + data + '>' + content + '</blockquote>';
		},
		quoteType: function(val, name) {
			return "'" + val.replace("'", "\\'") + "'";
		},
		breakStart: true,
		breakEnd: true
	});



	/************************************************************
	 * Update font tag to allow limiting to only first in stack *
	 ************************************************************/
	$.sceditor.plugins.bbcode.bbcode.set("font", {
		format: function(element, content) {
			var font;

			if(element[0].nodeName.toLowerCase() !== "font" || !(font = element.attr('face')))
				font = element.css('font-family');

			if(sceditor_opts.limitfont)
				font = font.split(',')[0];

			return '[font=' + this.stripQuotes(font) + ']' + content + '[/font]';
		}
	});



	/*************************************
	 * Remove last bits of table support *
	 *************************************/
	$.sceditor.command.remove('table');
	$.sceditor.plugins.bbcode.bbcode.remove('table')
					.remove('tr')
					.remove('th')
					.remove('td');



	/********************************************
	 * Remove code and quote if in partial mode *
	 ********************************************/
	//if(sceditor_opts.partialmode)
	//	$.sceditor.plugins.bbcode.bbcode.remove('code').remove('quote');



	/*******************
	 * Init the editor *
	 *******************/
	$("#message, #signature").sceditor({
		style:			"jscripts/sceditor/jquery.sceditor.mybb.css",
		toolbar:		"bold,italic,underline,strike,subscript,superscript|left,center,right,justify|" +
					"font,size,color,removeformat|bulletlist,orderedlist|" +
					"code,quote|horizontalrule,image,email,link,unlink|emoticon,youtube,date,time|" +
					"print,source",
		resizeMaxHeight:	800,
		plugins:		'bbcode',
		autofocus:		sceditor_opts.autofocus,
		locale:			sceditor_opts.lang,
		rtl:			null,
		emoticons:		sceditor_opts.emoticons,
		enablePasteFiltering:   true,
		autofocusEnd:           true
	});


	/**************************************************
	 * Init the editor for xmlhttp calls (Quick Edit) *
	 **************************************************/
	$(document).on("focus", 'textarea[id*="quickedit_"]', function () {
		$(this).sceditor({
			style:			"jscripts/sceditor/jquery.sceditor.mybb.css",
			toolbar:		"bold,italic,underline,strike,subscript,superscript|left,center,right,justify|" +
						"font,size,color,removeformat|bulletlist,orderedlist|" +
						"code,quote|horizontalrule,image,email,link,unlink|emoticon,youtube,date,time|" +
						"print,source",
			resizeMaxHeight:	800,
			plugins:		'bbcode',
			autofocus:		sceditor_opts.autofocus,
			locale:			sceditor_opts.lang,
			rtl:			null,
			emoticons:		sceditor_opts.emoticons,
			enablePasteFiltering:   true,
			autofocusEnd:           true
		});
	});



	/******************************
	 * Source mode option support *
	 ******************************/
	if(sceditor_opts.sourcemode) {
		$("#message, #signature").sceditor("instance").sourceMode(true);
		$(document).on("focus", 'textarea[id*="quickedit_"]', function() {
			$(this).sceditor("instance").sourceMode(true);
		});
	}



	/**************************
	 * Emoticon click support *
	 **************************/
	$("#clickable_smilies img").each(function() {
		$(this).css('cursor', 'pointer');

		$(this).click(function() {
			$("#message, #signature").data("sceditor").insert($(this).attr('alt'));
			return false;
		});
	});



	/****************************
	 * Emoticon disable support *
	 ****************************/
	var $checkbox = $("input[name=postoptions\\[disablesmilies\\]], input[name=options\\[disablesmilies\\]]");

	$checkbox.change(function() {
		$("#message, #signature").sceditor("instance").emoticons(!this.checked);
	});

	if($checkbox.length)
		$("#message, #signature").sceditor("instance").emoticons(!$checkbox[0].checked);



	/************************************
	 * clickableEditor compat functions *
	 ************************************/
	clickableEditor.insertAttachment = function(aid) {
		$("#message, #signature").data("sceditor").insertText('[attachment='+aid+']');
	};

	clickableEditor.performInsert = function(code) {
		$("#message, #signature").data("sceditor").insert(code);
	};

	clickableEditor.openGetMoreSmilies = function(editor)
	{
		MyBB.popupWindow('misc.php?action=smilies&popup=true&editor='+editor, 'sminsert', 240, 280);
	};



	/****************************
	 * Form reset compatibility *
	 ****************************/
	var textarea = $("#message, #signature").get(0);
	if(textarea)
	{
		$(textarea.form).bind("reset", function() {
			$("#message, #signature").data("sceditor").val("").emoticons(true);
		});
	}
});



/**********************************
 * Thread compatibility functions *
 **********************************/
if(typeof Thread !== "undefined")
{
	var quickReplyFunc = Thread.quickReply;
	// update the textarea to the editors value before the Thread class
	// uses it for quickReply, ect.
	Thread.quickReply = function(e) {
		var editor = jQuery("#message, #signature").data("sceditor");

		if(editor)
			editor.updateOriginal();

		return quickReplyFunc.call(this, e);
	};

	// scrollTo fix
	Thread.quickEditLoaded = function(request, pid)
	{
		var message;

		if(request.responseText.match(/<error>(.*)<\/error>/))
		{
			message = request.responseText.match(/<error>(.*)<\/error>/);

			if(this.spinner)
			{
				this.spinner.destroy();
				this.spinner = '';
			}

			alert('There was an error fetching the posts.\n\n'+(message[1] || "An unknown error occurred."));
		}
		else if(request.responseText)
		{
// Add newline if has val?
			jQuery("pid_"+pid).innerHTML = request.responseText;

			var element = jQuery("#quickedit_"+pid);
			// get the textarea offset before it gets hidden by the editor
			var offset = element.offset().top-100;
			// automatically trigger the editor by focusing the textarea
			element.focus();

			// elegantly scroll to the editor
			jQuery("html, body").animate({
				scrollTop: offset
			}, 700);
		}

		Thread.clearMultiQuoted();
		$('quickreply_multiquote').hide();
		$('quoted_ids').value = 'all';

		if(this.spinner)
		{
			this.spinner.destroy();
			this.spinner = '';
		}

		jQuery('message').focus();
	};


	var quickEditSaveFunc = Thread.quickEditSave;
	// update the textarea before sending it to xmlhttp.php
	// used for quickedit
	Thread.quickEditSave = function(pid) {
		var editor = jQuery("#quickedit_"+pid).data("sceditor");

		if(editor)
			editor.updateOriginal();

		return quickEditSaveFunc.call(this, pid);
	};

	Thread.multiQuotedLoaded = function(request)
	{
		var message, editor;

		if(request.responseText.match(/<error>(.*)<\/error>/))
		{
			message = request.responseText.match(/<error>(.*)<\/error>/);

			if(this.spinner)
			{
				this.spinner.destroy();
				this.spinner = '';
			}

			alert('There was an error fetching the posts.\n\n'+(message[1] || "An unknown error occurred."));
		}
		else if(request.responseText)
		{
			editor = jQuery("#message, #signature").data("sceditor");

			if(editor)
				editor.insert(request.responseText);
		}

		Thread.clearMultiQuoted();
		$('quickreply_multiquote').hide();
		$('quoted_ids').value = 'all';

		if(this.spinner)
		{
			this.spinner.destroy();
			this.spinner = '';
		}
	};
}
