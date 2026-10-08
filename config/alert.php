<?php
function alert($titulo,$porque,$tipo) {
return "<script>Swal.fire({title: '$titulo',text: '$porque',icon: '$tipo',customClass: {popup: 'sweety-window',title: 'sweety-title',content: 'sweety-content'}});</script>";
}
?>
