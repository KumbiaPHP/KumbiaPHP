/* vim: set ai sts=4 sw=4:
 *
 * Syntax Highlighting heavily derived from Dan Webb's Unobtrusive Code
 * Highlighter (which was derived form Dean Edward's star-light)
 *
 * Dan Webb's Unobtrusive Code Highlighter:
 * http://projects.danwebb.net/wiki/CodeHighlighter
 *
 * Dean Edwards' star-light:
 * http://dean.edwards.name/star-light/
 *
 * TODO: 
 * - contained syntax (e.g. PHP contains HTML, HTML contains JavaScript...)
 * - (off-topic) improve 'asap' fallback handling
 */
function asap(fn) {
    asap.done ? setTimeout(fn, 0) : asap.waiting.push(fn);
}
asap.waiting = [];
asap.done = 0;
asap.ready = function() {
    // (note: deliberately avoids using 'this')
    if (!asap.done++) {
	asap.timer && clearInterval(asap.timer);
	var funcs = asap.waiting;
	for (var i = 0, l = funcs.length; i < l; i++) {
	    setTimeout(funcs[i], 0);
	}
    }
}
// IE
/*@cc_on@if(@_win32)document.write('<script defer onreadystatechange="readyState==\'complete\'&&asap.ready()" src=//:></script>')@end@*/
// Moz/Op
document.addEventListener && document.addEventListener('DOMContentLoaded', asap.ready, false);
// Safari
asap.timer = navigator.userAgent.match(/WebKit|KHTML/i) && setInterval(function() { document.readyState.match(/loaded|complete/i) && asap.ready() }, 10);
// Fallback
window.onload = asap.ready;

var Syntax = {
    'languages': [],
    'defineLanguage': function(name, rules) {
	if (this.languages.push({ name: name, rules: rules, ignoreCase: !!arguments[2] }) == 1) {
	    asap('Syntax.init()');
	}
    },
    'init': function() {
	var codeEls = document.getElementsByTagName("CODE");
	// collect array of all code elements
	codeEls.filter = function(f) {
	    var a =  [];
	    for (var i = 0; i < this.length; i++) if (f(this[i])) a[a.length] = this[i];
	    return a;
	} 

	var rules = [];
	rules.toString = function() {
	    // joins regexes into one big parallel regex
	    var exps = new Array;
	    for (var i = 0; i < this.length; i++) exps.push(this[i].exp);
	    return exps.join("|");
	}

	// add a token replacment rule
	function addRule(className, rule) {
	    // FIXME: we're using a try-block because Safari falls-over using
	    // the 'regex' rule from the javascript ruleset.
	    // (it works in FF, Opera and IE5.5+ without issue)
	    try {
		var className = rule.className || className;
		// convert regexs to strings and chop of the slashes
		// (note: style-rule reg-ex's never have any flags, helping slice(1, -1) work)
		var exp = (typeof rule.exp != "string") ? String(rule.exp).slice(1, -1) : rule.exp;
		// calculate number of capturing groups in reg-exp
		var captures;
		if (rule.sample) {
		    captures = rule.sample.match(exp).length;
		} else {
		    captures = (exp.match(/(^|[^\\])\([^?]/g) || "").length + 1;
		}
		rules.push({
		    'className' : className,
		    'exp' : '(' + exp + ')',
		    'length': captures
		    });
		} catch (e) {
	    }
	}

	// Escape any HTML-sensitive characters (<, > and &)
	function htmlEncode(string) {
	    return string.replace(/[<&>]/g, function(c) { return {'<': '&lt;', '&': '&amp;', '>': '&gt;'}[c] });
	}

	function markup() {
	    var i = 0, j = 1, rule;
	    while (rule = rules[i++]) {
		if (arguments[j]) {
		    return "<span class=\"" + rule.className + "\">" + htmlEncode(arguments[0]) + "</span>";
		} else {
		    j += rule.length;
		}
	    }
	    return htmlEncode(arguments[0]);
	}

	function textNodes(parent) {
	    var nodes = [], children = parent.childNodes;
	    for (var j = 0, l = children.length; j < l; j++) {
		var child = children[j];
		switch (child.nodeType) {
		    case 1:
			nodes.push.apply(nodes, textNodes(child));
			break;
		    case 3:
			nodes[nodes.length] = child;
			break;
		}
	    }
	    return nodes;
	}

	// surrogate element;
	var surrogate = document.createElement('pre');
	function replaceNode(node, html) {
	    var parentNode = node.parentNode;
	    surrogate.innerHTML = html;
	    while (surrogate.firstChild) {
		parentNode.insertBefore(surrogate.firstChild, node);
	    }
	    parentNode.removeChild(node);
	}

	// normalize source code. tabs -> spaces, smart-quotes -> standard-quotes
	function normalize(text) {
	    function spaces() {
		var col = arguments[1] + inserted,
		    spaces = '        '.substr(col % 8);
		inserted += spaces.length - 1;
		return spaces;
	    }
	    var tabs = /\t/g, inserted;
	    // replace tabs with spaces:
	    text = text.replace(/[^\n\r\u2028\u2029]+/g, function (line) { inserted = 0; return line.replace(tabs, spaces); });
	    // replace wordpress's "smart-quotes" with standard quotes: (TODO: fix wordpress)
	    text = text.replace(/[\u201c\u201d]/g, '"').replace(/[\u2018\u2019]/g, "'");
	    return text;
	}

	function highlight(language) {
	    // clear rules array
	    rules.length = 0;
	    rules.name = language.name;
	    // add style rules to parser
	    for (var className in language.rules) addRule(className, language.rules[className]);
	    var tokens = new RegExp(rules, language.ignoreCase ? "gi" : "g");

	    // get stylable elements by filtering out all code elements without the correct className 
	    var codeBlocks = codeEls.filter(function(item) { return (item.className.indexOf(language.name) >= 0) });

	    // replace for all elements
	    for (var i = 0; i < codeBlocks.length; i++) {
		// grab text-nodes (recursive)
		var nodes = textNodes(codeBlocks[i]);
		for (var j = 0, l = nodes.length; j < l; j++) {
		    var node = nodes[j], text = normalize(node.data);

		    // main text parsing and replacement
		    var html = text.replace(tokens, markup);

		    // Replace line-breaks with explicit HTML for Internet Explorer
		    // (implementation should be harmless to other browsers)
		    html = html.replace(/(\r\n|\r|\n\r|^)( *)/g, function(_,nl,spaces) { return (nl && '<br />&shy;') + Array(spaces.length + 1).join('&nbsp;'); });
		    replaceNode(node, html);
		}
	    }
	}

	// run highlighter on all languages
	for (var i = 0; i < this.languages.length; i++) {
	    highlight(this.languages[i]);
	}
    }
};


Syntax.defineLanguage("javascript", {
    'comment':	    { className: 'comment',	sample: '// ',  exp: /(\/\/[^\n\r]*(?=\n|\r|$))|(\/\*[^*]*\*+([^\/][^*]*\*+)*\/)/ },
    'regex':	    { className: 'constant',	sample: '/_/',  exp: /\/(?:\\[^\n\r\u2028\u2029]|[^\n\r\u2028\u2029*\/])(?:\\[^\n\r\u2028\u2029]|[^\n\r\u2028\u2029\/])*\/(?:[gim]+\b|(?!\w))/ },
    'string':	    { className: 'string',	sample: '"_"',  exp: /'(?:\.|(\\\')|[^\''])*'|"(?:\.|(\\\")|[^\""])*"/ },
    'type':	    { className: 'type',	sample: 'var',	exp: /\b(?:function|var|this)\b/ },
    'literal':	    { className: 'constant',	sample: 'true', exp: /\b(?:true|false|null)\b/ },
    'globals':	    { className: 'identifier',	sample: 'NaN',	exp: /\b(?:undefined|Infinity|NaN|Object|Function|Array|String|Boolean|Number|Math|Date|RegExp|Error|arguments|window|document)\b/ },
    'statement':    { className: 'statement',	sample: 'if',	exp: /\b(?:if|else|do|while|for|continue|break|return|with|switch|case|default|try|throw|catch|finally)\b/ },
    'hex':	    { className: 'number',	sample: '0xf',	exp: /\b0[xX][a-fA-F0-9]+\b/ },
    'octal':	    { className: 'number',	sample: '077',	exp: /\b0[0-7]+\b/ },
    'decimal':	    { className: 'number',	sample: '1.1',	exp: /(?:\b(?:0\b|[1-9]\d*)(?:\.\d*)?|\.\d+\b)(?:[eE][-+]?\d+\b)?/ },
    'comparison':   { className: 'operator',	sample: '<=',   exp: /!==|===|==|!=|<=|>=/ },
    'assign':	    { className: 'assign',	sample: '=',	exp: /<<=|>>>=|>>=|[-+*%&|^]?=/ },
    'operator':	    { className: 'operator',	sample: '<<',	exp: /<<|>>>|>>|--|\+\+|&&|\|\||[-+*\/^%&|^?:<>]|\b(new|delete|void|typeof|instanceof|in)\b/ },
    // ECMAScript Spec section 7.5.2 Keywords:
    'keyword':	    { className: 'keyword',	sample: 'this', exp: /\b(?:break|case|catch|continue|default|delete|do|else|finally|for|function|if|in|instanceof|new|return|switch|this|throw|try|typeof|var|void|while|with)\b/ },
    // ECMAScript Spec section 7.5.3 Future Reserved Words:
    'reserved':	    { className: 'reserved',	sample: 'long', exp: /\b(?:abstract|boolean|byte|char|class|const|debugger|double|enum|export|extends|final|float|goto|implements|import|int|interface|long|native|package|private|protected|public|short|static|super|synchronized|throws|transient|volatile)\b/ }
});

Syntax.defineLanguage("php", {
    'comment':	    { className: 'comment',	sample: '// ',  exp: /(\/\/[^\n\r]*(?=\n|\r|$))|(\/\*[^*]*\*+([^\/][^*]*\*+)*\/)/ },
   // 'regex':	    { className: 'constant',	sample: '/_/',  exp: /\/(?:\\[^\n\r\u2028\u2029]|[^\n\r\u2028\u2029*\/])(?:\\[^\n\r\u2028\u2029]|[^\n\r\u2028\u2029\/])*\/(?:[gim]+\b|(?!\w))/ },
    'string':	    { className: 'string',	sample: '"_"',  exp: /'(?:\.|(\\\')|[^\''])*'|"(?:\.|(\\\")|[^\""])*"/ },
   // 'type':	    { className: 'type',	sample: 'var',	exp: /\b(?:function|var|this)\b/ },
    //'literal':	    { className: 'constant',	sample: 'true', exp: /\b(?:true|false|null)\b/ },
    //'globals':	    { className: 'identifier',	sample: 'NaN',	exp: /\b(?:undefined|Infinity|NaN|Object|Function|Array|String|Boolean|Number|Math|Date|RegExp|Error|arguments|window|document)\b/ },
   // 'statement':    { className: 'statement',	sample: 'if',	exp: /\b(?:if|else|do|while|for|continue|break|return|with|switch|case|default|try|throw|catch|finally)\b/ },
   // 'hex':	    { className: 'number',	sample: '0xf',	exp: /\b0[xX][a-fA-F0-9]+\b/ },
   // 'octal':	    { className: 'number',	sample: '077',	exp: /\b0[0-7]+\b/ },
   // 'decimal':	    { className: 'number',	sample: '1.1',	exp: /(?:\b(?:0\b|[1-9]\d*)(?:\.\d*)?|\.\d+\b)(?:[eE][-+]?\d+\b)?/ },
   // 'comparison':   { className: 'operator',	sample: '<=',   exp: /!==|===|==|!=|<=|>=/ },
   // 'assign':	    { className: 'assign',	sample: '=',	exp: /<<=|>>>=|>>=|[-+*%&|^]?=/ },
   // 'operator':	    { className: 'operator',	sample: '<<',	exp: /<<|>>>|>>|--|\+\+|&&|\|\||[-+*\/^%&|^?:<>]|\b(new|delete|void|typeof|instanceof|in)\b/ },
    // ECMAScript Spec section 7.5.2 Keywords:
    'keyword':	    { className: 'keyword',	sample: 'this', exp: /\b(?:break|case|catch|continue|default|delete|do|else|for|function|if|in|instanceof|new|return|switch|this|throw|try|typeof|var|void|while|with|abstract|public|private|protected|parent|static|class|extends)\b/ }
    // ECMAScript Spec section 7.5.3 Future Reserved Words:
});


Syntax.defineLanguage("css", {
    'comment':	    { className: 'comment',	sample: '/* */',	    exp: /\/\*[^*]*\*+([^\/][^*]*\*+)*\// },
    'keyword':	    { className: 'keyword',	sample: '@import',	    exp: /@\w[\w\s]*/ },
    'selector':     { className: 'type',	sample: 'div { }',	    exp: /([\w-:\[.#][^{};>]*)(?={)/ },
    'vendor-prop':  { className: 'todo',    	sample: '-moz-box-sizing:', exp: /(-[\w-]+)(?=\s*:)/ },
    'prop':	    { className: 'statement',   sample: 'color:',	    exp: /([\w-]+)(?=\s*:)/ },
    'units':	    { className: 'number',	sample: '3px',		    exp: /((\d+\.\d+|\d+|\.\d+)(em|en|px|%|pt)|0)\b/ },
    'urls':	    { className: 'string',	sample: 'url(image.png)',   exp: /url\([^\)]*\)/ },
    'string':	    { className: 'string',	sample: '"foo"',	    exp: /'(?:\.|(\\\')|[^\''])*'|"(?:\.|(\\\")|[^\""])*"/ },
    'priority':	    { className: 'keyword',	sample: '!important',	    exp: /!\s*important/ }
});
// TODO: support Syntax.highlight('code.javascript', 'javascript');
