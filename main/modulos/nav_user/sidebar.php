<!----SEARCH BAR----->
<div class="sidebar-section">
<input type="text" class="form-search_bar" id="shear" placeholder="Buscar en el menu...">
</div>
<!----SEARCH BAR----->

<?php if(verify("ver_dashboard")):?>
<!--- DASHBOARD --->
<div id="modum0">
<label class="sidebar-item nav-link" data-pagina="dashboard">
<span><i class="bi bi-house-door"></i> Panel Principal</span>
</label>
</div>
<?php endif;?>


<!--- MODULO 1 --->
<div class="sidebar-section">
<i class="bi bi-box"></i> MENU GESTION
</div>

<?php if(verify("crear_lotes") || verify("ver_productos") || verify("crear_categorias")):?>
<!----1--->
<div id="modum1">
<label class="menu-item toggle-submenu" data-submenu="inventario">
<span>Gestion de inventario</span>
<i class="bi bi-chevron-down"></i>
</label>

<div class="submenu" id="submenu-inventario">
<?php if(verify("ver_productos")):?>
<label class="sidebar-item nav-link" data-pagina="productos" id="contenmodum1-1">Gestionar Productos</label>
<?php endif;?>

<?php if(verify("crear_lotes")):?>
<label class="sidebar-item nav-link" data-pagina="lotes" id="contenmodum1-2">Entradas de Inventario</label>
<?php endif;?>

<?php if(verify("crear_categorias")):?>
<label class="sidebar-item nav-link" data-pagina="categorias" id="contenmodum1-3">Clasificación por Categorías</label>
<?php endif;?>

<?php if(verify("crear_alertas")):?>
<label class="sidebar-item nav-link" data-pagina="nda" id="contenmodum1-4">Alertas de Inventario</label>
<?php endif;?>

<?php if(verify("ver_proveedores")):?>
<label class="sidebar-item nav-link" data-pagina="proveedores" id="contenmodum1-5">Base de Proveedores</label>
<?php endif;?>


<?php if(verify("ver_monedas")):?>
<label class="sidebar-item nav-link" data-pagina="monedas" id="contenmodum1-6">Parámetros Monetarios</label>
<?php endif;?>

<?php if(verify("ver_empleados")):?>
<label class="sidebar-item nav-link" data-pagina="empleados" id="contenmodum1-7">Usuarios y Accesos</label>
<?php endif;?>
</div>
</div>
<?php endif;?>


<?php if(verify("editar_venta") || verify("crear_venta")):?>
<!---2---->
<div id="modum2">
<!--- MODULO 2 --->      
<div class="sidebar-section">
<i class="bi bi-arrow-left-right"></i> OPERACIONES
</div>

<label class="menu-item toggle-submenu" data-submenu="ventas">
<span>Gestión de Ventas</span>
<i class="bi bi-chevron-down"></i>
</label>

<div class="submenu" id="submenu-ventas">

<?php if(verify("crear_venta")):?>
<label class="sidebar-item nav-link" data-pagina="ventas" id="contenmodum2-1">Realizar Venta</label>
<?php endif;?>

<?php if(verify("editar_venta")):?>
<label class="sidebar-item nav-link" data-pagina="gestionv" id="contenmodum2-2">Gestionar Ventas</label>
<?php endif;?>
</div>
</div>
<?php endif;?>



<?php if (verify("gestionar_reportes") || verify("editar_usuario_all")):?>
<!---3---->
<div id="modum3">
<!--- MODULO 3 --->	
<div class="sidebar-section">
<i class="bi bi-gear"></i> ADMINISTRACIÓN
</div>

<label class="menu-item toggle-submenu" data-submenu="reportes">
<span>Reportes y Análisis</span>
<i class="bi bi-chevron-down"></i>
</label>

<div class="submenu" id="submenu-reportes">
<?php if (verify("gestionar_reportes")):?>
<label class="sidebar-item nav-link" data-pagina="reportes" id="contenmodum3-1">Reportes</label>
<?php endif;?>

<?php if(verify("editar_usuario_all")):?>
 <label class="sidebar-item nav-link" data-pagina="gestion_permisos" id="contenmodum3-3">Gestion de Permisos</label>
 <?php endif;?>
 
</div>
</div>
<?php endif;?>

<?php if(verify("ver_ajustes")):?>
<!---4---->
<div id="modum4">
<label class="menu-item toggle-submenu" data-submenu="admin_config">
<span>Configuración del Sistema</span>
<i class="bi bi-chevron-down"></i>
</label>

<div class="submenu" id="submenu-admin_config">
<?php if(verify("crear_roles") || verify("crear_roles")):?>
<label class="sidebar-item nav-link" data-pagina="roles" id="contenmodum4-1">Roles y Permisos</label>
<?php endif;?>

<?php if(verify("ver_ajustes")):?>
<label class="sidebar-item nav-link" data-pagina="config" id="contenmodum4-3">Configuraciones</label>
<?php endif;?>

<label class="sidebar-item nav-link" data-pagina="ayuda" id="contenmodum4-3">Ayuda</label>

</div>


</div>
<?php endif;?>


<style>
.sidebar-section {
padding: 15px 20px;
font-weight: 600;
border-bottom: 1px solid rgba(255, 255, 255, 0.1);
color: #3498db;
}

.form-search_bar {
width: 100%;
padding: 10px 15px;
border: none;
border-radius: 5px;
background-color: rgba(255, 255, 255, 0.1);
color: white;
margin-bottom: 10px;
transition: all 0.3s;
}

.form-search_bar:focus {
outline: none;
background-color: rgba(255, 255, 255, 0.2);
box-shadow: 0 0 0 2px #3498db;
}

.form-search_bar::placeholder {
color: rgba(255, 255, 255, 0.5);
}

.menu-item {
display: flex;
justify-content: space-between;
align-items: center;
padding: 12px 20px;
cursor: pointer;
transition: all 0.2s;
border-left: 3px solid transparent;
}

.menu-item:hover {
background-color: #34495e;
border-left: 3px solid #3498db;
}

.sidebar-item {
display: block;
padding: 10px 20px 10px 40px;
cursor: pointer;
transition: all 0.2s;
border-left: 3px solid transparent;
}

.sidebar-item:hover {
background-color: #34495e;
border-left: 3px solid #3498db;
}

.submenu {
max-height: 0;
overflow: hidden;
transition: max-height 0.3s ease-out;
background-color: rgba(0, 0, 0, 0.1);
}

.submenu.expanded {
max-height: 500px;
}

.bi-chevron-down {
transition: transform 0.3s;
}

.rotate-180 {
transform: rotate(180deg);
}

.search-highlight {
background-color: #f39c12;
color: #2c3e50;
font-weight: bold;
padding: 0 2px;
border-radius: 2px;
}

.no-results {
padding: 15px 20px;
text-align: center;
color: rgba(255, 255, 255, 0.6);
font-style: italic;
}

.hidden-module {
display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
const toggleButtons = document.querySelectorAll('.toggle-submenu');
const searchInput = document.getElementById('shear');

// Función para alternar submenús
toggleButtons.forEach(button => {
button.addEventListener('click', function(e) {
	e.preventDefault();
	
	const submenuId = this.getAttribute('data-submenu');
	const submenu = document.getElementById(`submenu-${submenuId}`);
	
	if (submenu) {
		submenu.classList.toggle('expanded');
		
		const chevron = this.querySelector('.bi-chevron-down');
		if (chevron) {
			chevron.classList.toggle('rotate-180');
		}
	}
});
});

// Función para buscar en el menú
function searchMenu(query) {
const allItems = document.querySelectorAll('.sidebar-item, .menu-item');
const allModules = document.querySelectorAll('#modum0, #modum1, #modum2, #modum3, #modum4');
let foundItems = 0;

// Resetear todos los elementos
allItems.forEach(item => {
	item.style.display = 'flex';
	item.classList.remove('hidden-item');
	
	const textElement = item.querySelector('span') || item;
	const originalText = textElement.getAttribute('data-original-text');
	if (originalText) {
		textElement.innerHTML = originalText;
	}
});

// Mostrar todos los módulos inicialmente
allModules.forEach(mod => {
	mod.classList.remove('hidden-module');
});

// Si la consulta está vacía, mostrar todo y colapsar submenús
if (!query.trim()) {
	document.querySelectorAll('.submenu').forEach(submenu => {
		submenu.classList.remove('expanded');
	});
	
	document.querySelectorAll('.bi-chevron-down').forEach(chevron => {
		chevron.classList.remove('rotate-180');
	});
	
	// Eliminar mensaje de no resultados si existe
	const noResults = document.querySelector('.no-results');
	if (noResults) noResults.remove();
	
	return;
}

const queryLower = query.toLowerCase();
const modulesToShow = new Set();
const submenusToExpand = new Set();

// Buscar elementos que coincidan
allItems.forEach(item => {
	const textElement = item.querySelector('span') || item;
	const originalText = textElement.getAttribute('data-original-text') || textElement.textContent;
	const searchText = originalText.toLowerCase();
	
	if (searchText.includes(queryLower)) {
		foundItems++;
		item.style.display = 'flex';
		item.classList.remove('hidden-item');
		
		// Resaltar el texto coincidente (sin usar innerHTML que podría romper eventos)
		const regex = new RegExp(`(${query})`, 'gi');
		const highlightedText = originalText.replace(regex, '<span class="search-highlight">$1</span>');
		
		// Crear un elemento temporal para convertir el HTML seguro
		const tempDiv = document.createElement('div');
		tempDiv.innerHTML = highlightedText;
		
		// Limpiar el contenido y añadir nodos seguros
		textElement.innerHTML = '';
		while(tempDiv.firstChild) {
			textElement.appendChild(tempDiv.firstChild);
		}
		
		// Marcar el módulo para mostrar
		const parentModule = item.closest('[id^="modum"]');
		if (parentModule) {
			modulesToShow.add(parentModule.id);
		}
		
		// Marcar submenús para expandir
		const parentSubmenu = item.closest('.submenu');
		if (parentSubmenu) {
			submenusToExpand.add(parentSubmenu.id);
		}
		
	} else {
		item.style.display = 'none';
		item.classList.add('hidden-item');
	}
});

// Expandir submenús que contienen resultados
document.querySelectorAll('.submenu').forEach(submenu => {
	if (submenusToExpand.has(submenu.id)) {
		submenu.classList.add('expanded');
		
		// También expandir el elemento padre del submenú
		const parentToggle = document.querySelector(`[data-submenu="${submenu.id.replace('submenu-', '')}"]`);
		if (parentToggle) {
			const chevron = parentToggle.querySelector('.bi-chevron-down');
			if (chevron) chevron.classList.add('rotate-180');
		}
	} else {
		submenu.classList.remove('expanded');
		
		const parentToggle = document.querySelector(`[data-submenu="${submenu.id.replace('submenu-', '')}"]`);
		if (parentToggle) {
			const chevron = parentToggle.querySelector('.bi-chevron-down');
			if (chevron) chevron.classList.remove('rotate-180');
		}
	}
});

// Mostrar/ocultar módulos basado en resultados
allModules.forEach(mod => {
	if (modulesToShow.has(mod.id) || modulesToShow.size === 0) {
		mod.classList.remove('hidden-module');
	} else {
		mod.classList.add('hidden-module');
	}
});

// Mostrar mensaje si no se encontraron resultados
const existingNoResults = document.querySelector('.no-results');
if (existingNoResults) existingNoResults.remove();

if (foundItems === 0 && query.trim()) {
	const noResults = document.createElement('div');
	noResults.className = 'no-results';
	noResults.textContent = `No se encontraron resultados para "${query}"`;
	document.querySelector('.sidebar').appendChild(noResults);
}
}

// Evento de entrada para la búsqueda (con debounce para mejor rendimiento)
let searchTimeout;
searchInput.addEventListener('input', function() {
clearTimeout(searchTimeout);
searchTimeout = setTimeout(() => {
	searchMenu(this.value);
}, 300); // 300ms de retardo para evitar búsquedas muy frecuentes
});

// Evento para limpiar búsqueda con ESC
searchInput.addEventListener('keydown', function(e) {
if (e.key === 'Escape') {
	this.value = '';
	searchMenu('');
}
});

// Inicializar datos de texto original
document.querySelectorAll('.sidebar-item, .menu-item').forEach(item => {
const textElement = item.querySelector('span') || item;
// Guardar el HTML original, no solo el texto
textElement.setAttribute('data-original-text', textElement.innerHTML);

// Asegurar que los elementos de menú sean clickeables
if (item.classList.contains('sidebar-item') && item.getAttribute('data-pagina')) {
	item.addEventListener('click', function() {
		// Aquí puedes agregar la lógica para cargar la página
		const pagina = this.getAttribute('data-pagina');
		console.log('Cargando página:', pagina);
		
		// Ejemplo: cargar contenido vía AJAX
		// loadPage(pagina);
		
		// O redireccionar
		// window.location.href = `?pagina=${pagina}`;
	});
}
});
});


</script>
