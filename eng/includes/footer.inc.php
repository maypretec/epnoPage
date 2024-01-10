    <footer>
      <div class='container'>
        <div class='row'>
          <div class='col-md-3'>
            <a class='footer-brand' href='eng/index.php'><img src='../images/logo-black.png' alt=''></a>
            <p id='footer_description'>We provide the industry with digital tools that facilitate the search for reliable suppliers.</p>
            <div class='second_section'>
              <h2>Follow Us:</h2>
                <ul class='social-icons'>
                <li><a class='facebook' href='https://www.facebook.com/epnomx' target='_blank'><i class='fa fa-facebook'></i></a></li>
                <li><a class='instagram' href='#'><i class='fa fa-instagram'></i></a></li>
                <li><a class='Linkedin' href='#'><i class='fa fa-linkedin'></i></a></li>
                <li><a class='youtube' href='#'><i class='fa fa-youtube'></i></a></li>
              </ul>
            </div>
          </div>
          <div class='col-md-3'>
              <h2>Our Solutions</h2>
                <ul class='footer_list'>
                <li><a href='proveedor.php'>Supplier</a></li>
                <li><a href='industria.php'>Industry</a></li>
              </ul>
          </div>
          <div class='col-md-3'>
            <h2>Company</h2>
                <ul class='footer_list'>
                <li><a href='contacto.php'>Contact Us</a></li>
              </ul>
          </div>
          <div class='col-md-3'>
            <h2>Legal</h2>
                <ul class='footer_list'>
                <li><a href='#'>Terms & Conditions</a></li>
                <li><a href='#'>Privacy Policies</a></li>
              </ul>
          </div>
        </div>
        <p class='copyright'>
          &copy; Copyright <?php $startYear = 2023;
          $thisYear = date('Y');
          if ($startYear == $thisYear){
            echo $startYear;
          } else {
              echo '{$startYear}&#8211;{$thisYear}';
            }
          ?>. EP&O Electronic Purchase & Order. All Rights Reserved.
        </p>
      </div>
    </footer>
    <!-- End footer -->

  </div>
  <!-- End Container -->

  <script src='../js/kinetic-plugins.min.js'></script>
    <script src='../js/jquery.themepunch.tools.min.js'></script>
    <script src='../js/jquery.themepunch.revolution.min.js'></script>
  <script src='../js/jquery.appear.js'></script>
  <script src='../js/jquery.countTo.js'></script>
  <script src='../js/waypoint.min.js'></script>
  <script src='https://rawgit.com/Ashish-Bansal/jquery-typewriter/master/jquery.typewriter.js' defer=''></script>
  <script src='../js/script.js'></script>

  <script>  
  $(document).ready(function() {  
      $('#typewriter').typewriter({
          prefix : '',
          text : ['Training', 'Molding', 'Machining', 'Automation', 'Recruitment', 'Metrology', 'Coffee Shops', 'Screen Printing', 'MRO', 'CNCs', 'Raffle', 'Technology', 'Tool Crib ', 'Reworks'],
          typeDelay : 100,
          waitingTime : 1500,
          blinkSpeed : 800
      });  
  });  
  </script>  

</body>
</html>