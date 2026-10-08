////document.addEventListener('DOMContentLoaded', () => {
window.onload = function() {
let paginaBEFORE = null;
let paginaNOW = null;
let mixvalo = false;

//window.onload = function() {} carga solo al regargar
const darkenable = localStorage.getItem('darkmode');

if (darkenable === 'true') {
    darkmode(true); 
}


$(document).ready(function() {
    $("#colorTHEMA").on("click", function(){
	mixvalo = !mixvalo;
    darkmode(mixvalo); 
    localStorage.setItem('darkmode', mixvalo); 
		
		}); 
		
$("#themesofpos2").on("click", function(){
	mixvalo = true;
    darkmode(mixvalo); 
    localStorage.setItem('darkmode', true); 
		
		}); 
		
	$("#themesofpos1").on("click", function(){
	mixvalo = false;
    darkmode(mixvalo); 
    localStorage.setItem('darkmode', mixvalo); 
		
		}); 
});

function darkmode(valor) {
	const body = document.body; // O el contenedor que estés usando
    if (valor === true) {
        body.classList.add('dark-mode'); // Cambiar a modo oscuro
        document.querySelector('#colorTHEMA').innerText = 'Modo Claro'; // Actualiza el texto
    } 
    else {
        body.classList.remove('dark-mode'); // Cambiar a modo claro
        document.querySelector('#colorTHEMA').innerText = 'Modo Oscuro'; // Actualiza el texto
    }
}








$(document).ready(function() {
// 1. CUANDO HACES CLIC EN UN ENLACE DEL SIDEBAR
$('.sidebar-item').click(function(e) {
        e.preventDefault(); // Evita que el navegador recargue la página
        
        // 2. QUITAMOS "active" DE TODOS LOS ENLACES
        $('.sidebar-item').removeClass('active');
        
        // 3. PONEMOS "active" SOLO AL QUE CLICKEASTE
        $(this).addClass('active');
        
        // 4. OBTENEMOS QUÉ PÁGINA CARGAR
        var pagina = $(this).data('pagina');
        //$document.title += innerHTML(pagina);
       
       //sessionStorage.set
       
        
        history.pushState(null, null, `?pagina=${pagina}`);
        // 5. CARGAMOS SOLO EL CONTENIDO (NO toda la página)
        $.ajax({
            url: '?ajax=true&pagina=' + pagina,
            success: function(respuesta) {		
                // Metemos la nueva página solo en el contenedor
                ///paginaNOW = pagina;
                $('#contenido-dinamico').html(respuesta);
            },
            error: function() {
                $('#contenido-dinamico').html('<p>Error al cargar</p>');
            }
        });
    });
    
});

function recargarContenido() {
        const urlParams = new URLSearchParams(window.location.search);
        const pagina = urlParams.get('pagina') || 'dashboard';
        $('.nav-link[data-pagina="${pagina}"]').click();
    }


function checkyouarelive(){
		fetch('modulos/check.php').then(response =>{
			if(response.status === 401){
				window.location.href = "modulos/logout.php";
			}
			
			}).catch(error =>{
				//window.location.href = "modulos/logout.php";
				alert("Error comprobando el estado de la session, porfavor recargue la pagina");
			});
	}

setInterval(checkyouarelive, 5 * 60 * 1000);
window.addEventListener("focus",checkyouarelive);


function formatearHora(fecha) {
    let horas = fecha.getHours();
    const minutos = fecha.getMinutes();
    const esPM = horas >= 12;
  
    // Convertir a formato de 12 horas
    horas = horas % 12 || 12; // Asegura que 0 horas se convierta en 12
  
    // Añadir un cero inicial a los minutos si es necesario
    const minutosFormateados = minutos < 10 ? `0${minutos}` : minutos;
  
    // Retornar la hora formateada
    return `${horas}:${minutosFormateados} ${esPM ? 'PM' : 'AM'}`;
}

// Uso
const fechaActual = new Date();
const horaFormateada = formatearHora(fechaActual);



document.getElementById('froistapgp')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('swdlpgp');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});


}
