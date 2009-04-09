/**
 * Agrega un Observer para abrir al cargar la ventana de
 * componentes del proyecto
 */
new Event.observe(window, "load", function(){
	project_window = new Window({
		className: "vista",
		width: 280,
		height: 400,
		top: 80,
		left: 10,
		resizable: true,
		title: "Proyecto",
		maximizable: false,
		minimizable: false,
		closeable: false,
		showEffect: Element.show,
		hideEffect: Effect.BlindUp,
		destroyOnClose: true,

		onClose: function(){
			return false
		}
	})
	new Ajax.Request($Kumbia.path+"builder/project_components",{
		onSuccess: function(transport){
			project_window.setHTMLContent(transport.responseText);
		}
	})
	project_window.show()
})

var KumbiaBuilder = new Object();

Object.extend(KumbiaBuilder, {
	/**
	 * Abre una ventana con las propiedades del controlador
	 */
	controller_window: function(file){
		win = new Window({
			className: "vista",
			width: 500,
			height: window.screen.height-300,
			top: 20,
			left: 270,
			resizable: true,
			title: "Proyecto",
			maximizable: false,
			minimizable: false,
			closeable: false,
			showEffect: Effect.Appear,
			hideEffect: Effect.BlindUp,
			destroyOnClose: true,
			onClose: function(){
				return false
			}
		})
		new Ajax.Request($Kumbia.path+"builder/open_controller/"+file, {
			onSuccess: function(transport){
				win.setHTMLContent(transport.responseText)
				asap.ready()
			}
		})
		win.show()
		win.toFront()
	}
})