<?php include './includes/header.inc.php' ?>

    <!-- page-pagination-section 
			================================================== -->
		<section class='page-pagination-section'>
			<div class='container'>
				<h1>Get in Touch with Us</h1>
			</div>
		</section>
		<!-- End page-pagination section -->

			<section id='contact-section'>
				<div class='container-fluid'>
				<div class='title-section'>
					<!-- <span>Lorem Ipsum doloris imums.</span> -->
					<h1>Let's Talk</h1>
				</div>
				<form id='contact-form'>
					<div class='row'>


						<div class='col-md-6 contact-info-box'>
						<div class='contact-info'>
							<span><i class='la la-phone'></i></span>
							<h2>Telephone</h2>
							<p> (656) 689-9149</p>
						</div>
						<div class='contact-info'>
							<span><i class='la la-envelope'></i></span>
							<h2>Email</h2>
							<a href='mailto:contacto@epno.com.mx'><p> contacto@epno.com.mx</p></a>
							<a href='mailto:proveeduria@epno.com.mx'><p> proveeduria@epno.com.mx</p></a>
							<a href='mailto:industria@epno.com.mx'><p> industria@epno.com.mx</p></a>
							<a href='mailto:finanzas@epno.com.mx'><p> finanzas@epno.com.mx</p></a>
						</div>
						<div class='contact-info'>
							<span><i class='la la-map-marker'></i></span>
							<h2>Location</h2>
							<a href='https://goo.gl/maps/f3C1z5zXXQckh7aB9' target='_blank'><p>Plaza Solid, Blvrd. Municipio <br> Libre 3529 Local D</p></a>
						</div>
					</div>


						<div class='col-md-6'>
							<input name='name' id='name' type='text' placeholder='Name*'>
							<input name='mail' id='mail' type='text' placeholder='Email*'>
							<input name='subject' id='subject' type='text' placeholder='Cell Phone Number*'>
							<input name='website' id='website' type='text' placeholder='Subject'>
							<textarea name='comment' id='comment' placeholder='How May we Help You?*'></textarea>
							<div class='submit-area'>
								<input type='submit' id='submit_contact' value='Send Message'>
								<div id='msg' class='message'></div>
							</div>
						</div>
						
					
					</div>
					
				</form>
				
			</div>

		</section>
		<!-- End contact section -->
		<div class='google-map'>
			<iframe src='https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d848.4914216410136!2d-106.4543583!3d31.7168323!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86e75eb06accadf1%3A0x2534e3a999d4f8af!2sEP%26O!5e0!3m2!1ses-419!2smx!4v1679957127968!5m2!1ses-419!2smx' width='600' height='450' style='border:0;' allowfullscreen='' loading='lazy' referrerpolicy='no-referrer-when-downgrade'></iframe>
		</div>


		<?php include './includes/footer.inc.php' ?>