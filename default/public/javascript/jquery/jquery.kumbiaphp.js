/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * Plugin para jQuery que incluye los callbacks basicos para los Helpers
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

(function ($) {
	/**
	 * Objeto Kumbia
	 *
	 */
	$.Kumbia = {
		/**
		 * Ruta al directorio public en el servidor
		 *
		 * @var String
		 */
		publicPath: null,

		/**
		 * Plugins cargados
		 *
		 * @var Array
		 */
		plugin: [],

		/**
		 * Muestra mensaje de confirmacion
		 *
		 * @param Object event
		 */
		cConfirm: function (event) {
			let el = $(this);
			if (!confirm(el.data("msg"))) {
				event.preventDefault();
			}
		},

		/**
		 * Aplica un efecto a un elemento
		 *
		 * @param String fx
		 */
		cFx: function (fx) {
			return function (event) {
				event.preventDefault();
				let el = $(this),
					rel = $("#" + el.data("to"));
				rel[fx]();
			};
		},

		/**
		 * Carga con AJAX
		 *
		 * @param Object event
		 */
		cRemote: function (event) {
			let el = $(this),
				rel = $("#" + el.data("to"));
			event.preventDefault();
			rel.hide();
			rel.load(this.href);
			rel.show('fast');
		},

		/**
		 * Carga con AJAX y Confirmacion
		 *
		 * @param Object event
		 */
		cRemoteConfirm: function (event) {
			let el = $(this),
				rel = $("#" + el.data("to"));
			event.preventDefault();
			if (confirm(el.data("msg"))) {
				rel.hide();
				rel.load(this.href);
				rel.show('fast');
			}
		},

		/**
		 * Enviar formularios de manera asincronica, via POST
		 * Y los carga en un contenedor
		 */
		cFRemote: function (event) {
			event.preventDefault();
			let el = $(this);
			let button = $("[type=submit]", el);
			button.attr("disabled", "disabled");
			let url = el.attr("action");
			let div = el.attr("data-to");
			$.post(url, el.serialize(), function (data, status) {
				let capa = $("#" + div);
				capa.hide();
				capa.html(data);
				capa.show("fast");
				button.attr("disabled", null);
			});
		},

		/**
		 * Carga con AJAX al cambiar select
		 *
		 * @param Object event
		 */
		cUpdaterSelect: function (event) {
			let $t = $(this),
				$u = $("#" + $t.data("update")),
			    url = $t.data("url");
			$u.empty();
			$.get(
				url,
				{ id: $t.val() },
				function (d) {
					for (let i in d) {
						let a = $("<option />").text(d[i]).val(i);
						$u.append(a);
					}
				},
				"json"
			);
		},

		/**
		 * Enlaza a los métodos por defecto
		 *
		 */
		bind: function () {
			// Enlace y boton con confirmacion
			$("body").on("click", "a.js-confirm, input.js-confirm", this.cConfirm);

			// Enlace ajax
			$("body").on("click", "a.js-remote", this.cRemote);

			// Enlace ajax con confirmacion
			$("body").on("click", "a.js-remote-confirm", this.cRemoteConfirm);

			// Efecto show
			$("body").on("click", ".js-show", this.cFx("show"));

			// Efecto hide
			$("body").on("click", ".js-hide", this.cFx("hide"));

			// Efecto toggle
			$("body").on("click", ".js-toggle", this.cFx("toggle"));

			// Efecto fadeIn  @deprecated use CSS
			$("body").on("click", ".js-fade-in", this.cFx("fadeIn"));

			// Efecto fadeOut @deprecated use CSS
			$("body").on("click", ".js-fade-out", this.cFx("fadeOut"));

			// Formulario ajax
			$("body").on("submit", "form.js-remote", this.cFRemote);

			// Lista desplegable que actualiza con ajax
			$("body").on("change", "select.js-remote", this.cUpdaterSelect);

			// Enlazar DatePicker
			$.Kumbia.bindDatePicker();
		},

		/**
		 * Implementa la autocarga de plugins, estos deben seguir
		 * una convención para que pueda funcionar correctamente
		 */
		autoload: function () {
			let elem = $("[class*='jp-']");
			$.each(elem, function (i) {
				let el = $(this); //apunta al elemento con clase jp-*
				let classes = el.attr("class").split(" ");
				for (i in classes) {
					if (classes[i].substr(0, 3) == "jp-") {
						if ($.inArray(classes[i].substr(3), $.Kumbia.plugin) != -1)
							continue;
						$.Kumbia.plugin.push(classes[i].substr(3));
					}
				}
			});
			let head = $("head");
			for (let i in $.Kumbia.plugin) {
				$.ajaxSetup({ cache: true });
				head.append(
					'<link href="' +
					$.Kumbia.publicPath +
					"css/" +
					$.Kumbia.plugin[i] +
					'.css" type="text/css" rel="stylesheet"/>'
				);
				$.getScript(
					$.Kumbia.publicPath +
					"javascript/jquery/jquery." +
					$.Kumbia.plugin[i] +
					".js",
					function (data, text) { }
				);
			}
		},

		/**
		 * Carga y Enlaza Unobstrusive DatePicker en caso de ser necesario
		 *
		 */
		bindDatePicker: function () {
			// Selecciona los campos input
			let inputs = $("input.js-datepicker");
			/**
			 * Funcion encargada de enlazar el DatePicker a los Input
			 *
			 */
			let bindInputs = function () {
				inputs.each(function () {
					let opts = { monthSelector: true, yearSelector: true };
					let input = $(this);
					// Verifica si hay mínimo
					if (input.attr("min") != undefined) {
						opts.dateMin = input.attr("min").split("-");
					}
					// Verifica si ha máximo
					if (input.attr("max") != undefined) {
						opts.dateMax = input.attr("max").split("-");
					}

					// Crea el calendario
					input.pickadate(opts);
				});
			};

			// Si ya esta cargado Unobstrusive DatePicker, lo integra de una vez
			if (typeof $.pickadate != undefined) {
				return bindInputs();
			}

			// Carga la hoja de estilos
			$("head").append(
				'<link href="' +
				this.publicPath +
				'css/pickadate.css" rel="stylesheet">'
			);

			// Carga Unobstrusive DatePicker, para poder usar cache
			jQuery
				.ajax({
					dataType: 'script',
					cache: true,
					url: this.publicPath + 'javascript/jquery/pickadate.js',
				})
				.done(function () {
					bindInputs();
				});
		},

		/**
		 * Inicializa el plugin
		 *
		 */
		initialize: function () {
			// Obtiene el publicPath
			let src = document.currentScript.src;
			this.publicPath = src.slice(0, src.lastIndexOf('javascript/'));

			// Enlaza a los métodos por defecto
			$(function () {
				$.Kumbia.bind();
				$.Kumbia.autoload();
			});
		},
	};

	// Inicializa el plugin
	$.Kumbia.initialize();
})(jQuery);
