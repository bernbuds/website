$(function() 
{
	home = new Home();	
});

var Home = function()
{
	var self = this;

	self.view = $('body')
	self.submitting = false;
	self.slick;

	self.construct = function()
	{
		self.setupSignupForm();
	}

	self.setupSignupForm = function()
	{
		self.setupSlick();
		self.setupMainContactForm();
		self.setupVerifyForm();
	}

	self.setupSlick = function()
	{
		var container = self.view.find('.form-box');

		// signup page slides
		container.slick({
			speed: 300,
			cssEase: 'cubic-bezier(0.950, 0.050, 0.795, 0.035)',
			arrows: 'false',
			adaptiveHeight: true,
			draggable: false
		});

		// keep an easy handle to slick function
		self.slick = container;
	}

	self.setupMainContactForm = function()
	{
		var container = self.view.find('#become-a-bud-box');
		var contact_form = self.view.find('#contact-form');
		var submit_btn = contact_form.find('#submit');

		// checkbox
		contact_form.find('#can_drive').change(function()
		{
			var checked = contact_form.find('#can_drive')[0].checked;

			if( checked ) {
				contact_form.find('#can_pickup_txt').fadeIn();
			}
			else {
				contact_form.find('#can_pickup_txt').hide();
			}
		})
		contact_form.find('#can_drive').change();

		// # of people
		contact_form.find('#num_people_pickup').keyup(function()
		{
			var num_people = contact_form.find('#num_people_pickup').val();

			if( num_people == 1 ) {
				contact_form.find('#people_person').text('person')
			}
			else {
				contact_form.find('#people_person').text('people')
			}
		})

		contact_form.validate({
			rules: {
				name: {
					required: true,
					minlength: 2
				},
				email: {
					required: true,
					email: true
				},
				zip: {
					required: true,
					minlength: 5
				}
			},
			messages: {
				name: {
					required: "Please enter your name",
					minlength: "Your name must consist of at least 2 characters"
				},
				email: {
					required: "Please enter your email address"
				},
				zip: {
					required: "Please enter your zip code",
					minlength: "Your message must consist of at least 5 digits"
				}
			},

			invalidHandler: function()
			{
				self.redrawSlick();
			},

			submitHandler: function(form) 
			{
				if( self.submitting ) {
					return false;
				}

				self.submitting = true;
				submit_btn.val('Wait..');

				self.hideSuccess(container);
				self.hideError(container);

				self.view.find(form).ajaxSubmit({

					type:"POST",
					data: self.view.find(form).serializeObject(),
					url: "app/controllers/BecomeABudController.php?action=contactFormSubmission",

					success: function(res) 
					{
						self.submitting = false;
						submit_btn.val('Sign up now!');

						if( !res.success ) 
						{
							grecaptcha.reset();
							self.showError(container, res.message);

							return;
						}

						self.showSuccess(container, res.message);
						self.slick.slick('slickNext');
					},

					error: function(res) 
					{
						self.submitting = false;
						submit_btn.val('Sign up now!');

						grecaptcha.reset();
						self.showError(container, 'Error reading response')
					}
				});
			}
		});
	}

	self.setupVerifyForm = function()
	{
		var container = self.view.find('#verify-box')
		var verify_form = self.view.find('#verify-form');
		var submit_btn = self.view.find('#verify');

		verify_form.find('#back').click(function(e)
		{
			e.preventDefault();
			self.slick.slick('slickPrev');
		});

		submit_btn.click(function(e)
		{	
			e.preventDefault();
			verify_form.submit();
		});

		verify_form.validate({
			rules: {
				verify_email: {
					required: true
				}
			},
			messages: {
				verify_email: {
					required: "Please enter the email verification code"
				}
			},

			invalidHandler: function()
			{
				self.redrawSlick();
			},

			submitHandler: function(form) 
			{
				if( self.submitting ) {
					return false;
				}

				self.submitting = true;
				submit_btn.val('Wait..');

				self.hideSuccess(container);
				self.hideError(container);

				var data = self.view.find(form).serializeObject();
				data.email = $('#email').val();

				self.view.find(form).ajaxSubmit({

					type:"POST",
					data: data,
					url: "app/controllers/VerifyABudController.php?action=verifyContact",

					success: function(res) 
					{
						self.submitting = false;
						submit_btn.val('Verify');

						if( !res.success ) 
						{
							self.showError(container, res.message);
							return;
						}

						self.showSuccess(container, res.message);
						self.slick.slick('slickNext');
					},

					error: function(res) 
					{
						self.submitting = false;
						submit_btn.val('Verify!');

						self.showError(container, 'Error reading response')
					}
				});
			}
		});
	}

	self.showSuccess = function(container, msg)
	{
		if( !msg ) {
			return;
		}

		container.find('#response p').text(msg);
		container.find('#response').fadeIn();
		self.redrawSlick();
	}

	self.hideSuccess = function(container)
	{
		container.find('#response p').text('');
		container.find('#response').hide();
		self.redrawSlick();
	}

	self.hideError = function(container)
	{
		container.find('#error p').text('');
		container.find('#error').hide();
		self.redrawSlick();
	}

	self.showError = function(container, msg)
	{
		container.find('#error p').text(msg);
		container.find('#error').fadeIn();
		self.redrawSlick();
	}

	self.redrawSlick = function()
	{
		var i = 0;

		// captcha has no callback. So we try for a second to get it right.
		var redraw_interval = setInterval(function()
		{
			if( i++ > 10 ) {
				clearInterval(redraw_interval);
			}

			self.slick.slick('setPosition')	// force slick to redraw
		}, 100)
	};

	self.construct();
}
