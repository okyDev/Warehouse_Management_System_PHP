document.getElementById("n1").addEventListener("click", function(event) {
    event.preventDefault(); // Prevent the default anchor behavior
    myFunction();
});

function myFunction() {
    var n = document.getElementById('iframeContent');
		n.setAttribute("src","../test2.php");
}
