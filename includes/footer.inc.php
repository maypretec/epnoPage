    <footer>
      <div class='container'>
        <div class='row'>
          <div class='col-md-3'>
            <a class='footer-brand' href='index.php'><img src='images/logo-black.png' alt=''></a>
            <p id='footer_description'>Brindamos a la industria herramientas digitales que facilitan la búsqueda de proveedores confiables.</p>
            <div class='second_section'>
              <h2>Síguenos en:</h2>
                <ul class='social-icons'>
                <li><a class='facebook' href='https://www.facebook.com/epnomx' target='_blank'><i class='fa fa-facebook'></i></a></li>
                <li><a class='instagram' href='#'><i class='fa fa-instagram'></i></a></li>
                <li><a class='Linkedin' href='#'><i class='fa fa-linkedin'></i></a></li>
                <li><a class='youtube' href='#'><i class='fa fa-youtube'></i></a></li>
              </ul>
            </div>
          </div>
          <div class='col-md-3'>
              <h2>Nuestras Soluciones</h2>
                <ul class='footer_list'>
                <li><a href='proveedor.php'>Proveeduría</a></li>
                <li><a href='industria.php'>Industria</a></li>
              </ul>
          </div>
          <div class='col-md-3'>
            <h2>Compañia</h2>
                <ul class='footer_list'>
                <li><a href='contacto.php'>Contacto</a></li>
              </ul>
          </div>
          <div class='col-md-3'>
            <h2>Legal</h2>
                <ul class='footer_list'>
                <li><a href='#'>Términos y Condiciones</a></li>
                <li><a href='#'>Políticas de Privacidad</a></li>
              </ul>
          </div>
        </div>
        <p class='copyright'>
          &copy; Copyright <?php $startYear = 2024;
          $thisYear = date('Y');
          if ($startYear == $thisYear){
            echo $startYear;
          } else {
              echo '{$startYear}&#8211;{$thisYear}';
            }
          ?>. EP&O Electronic Purchase & Order. Todos los derechos reservados. Powered by <a href="https://www.maypretec.com">Maypretec</a>
        </p>
      </div>
    </footer>
    <!-- End footer -->

  </div>
  <!-- End Container -->

  <script src='js/kinetic-plugins.min.js'></script>
    <script src='js/jquery.themepunch.tools.min.js'></script>
    <script src='js/jquery.themepunch.revolution.min.js'></script>
  <script src='js/jquery.appear.js'></script>
  <script src='js/jquery.countTo.js'></script>
  <script src='js/waypoint.min.js'></script>
  <script src='https://rawgit.com/Ashish-Bansal/jquery-typewriter/master/jquery.typewriter.js' defer=''></script>
  <script src='js/script.js'></script>

  <script>  
  $(document).ready(function() {  
      $('#typewriter').typewriter({
          prefix : '',
          text : ['Capacitación', 'Moldeos', 'Maquinados', 'Automatización', 'Reclutamiento', 'Metrología', 'Cafeterías', 'Serigrafía', 'MRO', 'CNCs', 'Sorteo', 'Tecnología', 'Tool Crib', 'Retrabajos'],
          typeDelay : 100,
          waitingTime : 1500,
          blinkSpeed : 800
      });  
  });  
  </script>  

</body>
</html>