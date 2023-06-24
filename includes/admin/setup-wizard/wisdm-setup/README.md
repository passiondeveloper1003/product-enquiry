# WISDM Setup Wizard

WISDM setup wizard is a tool which can be integrated with any WordPress plugin. It has been developed by referring to the Dokan Lite Setup wizard.

## How to integrate?

- Add `wisdm-setup` folder in your plugin. You can add it anywhere but recommended to add in the `admin` directory of your plugin.
- Once added, include `wisdm-setup/class-wisdm-setup-wizard.php` file once. For example, `include_once 'admin/wisdm-setup/class-wisdm-setup-wizard.php';`
- Add your setup wizard data to `wisdm_setup_wizards` filter hook as shown in the example below.
- There could be multiple wizards added to the above hook but only one will be displayed at a time.

## Example

```php
if ( ! class_exists( 'Test_Setup_Wizard' ) ) {
	class Test_Setup_Wizard {

		public function __construct() {
			add_filter( 'wisdm_setup_wizards', array( $this, 'test_setup_wizard' ) );
		}

		/**
		 * Injects the wizard, steps and other data to Wisdm setup wizard.
		 *
		 * @param array $wizards
		 * @return array
		 */
		public function test_setup_wizard( $wizards ) {

			$cpb_wizard = array(
				'custom-product-boxes' => array( // Unique wizard slug.
					'title'      => 'WISDM Group Registration For LearnDash', // Product Name
					'capability' => 'manage_options', // The user must have this capability to load the wizard.
					'steps'      => array( // Sequential steps.
						'introduction'    => array( // step slug, every step slug must be unique.
							'step_title'    => 'Introduction', // This will display at the top as a step title.
							'view_callback' => array( $this, 'intro_view' ), // A callback function to display content of this step.
						),
						'all-form-fields' => array(
							'step_title'    => 'Form Fields Example',
							'view_callback' => array( $this, 'form_fields_view' ),
							'save_callback' => array( $this, 'form_fields_save' ), // A callback function to save the data of this step. Optional.
						),
						'ready'           => array(
							'step_title'    => 'Ready!',
							'view_callback' => array( $this, 'ready_view' ),
						),
					),
				),
			);

			return array_merge( $wizards + $cpb_wizard );
		}

		/**
		 * This is the initialization step. Generally, we do not consider this as a step and ask the user for any action except continue or dismiss the setup.
		 *
		 * @return string
		 */
		public function intro_view() {
			$wizard_handler = Wisdm_Wizard_Handler::get_instance();
			?>
			<h1>Welcome to the Group Registration Setup!</h1>
			<p>Thank you for trusting WisdmLabs! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than three minutes.</strong></p>
			<p>No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!</p>
			<p class="wc-setup-actions step">
				<a href="<?php echo esc_url( $wizard_handler->get_next_step_link() ); ?>" class="button-primary button button-large button-next">Let's Go!</a>
				<a href="<?php echo esc_url( admin_url( 'index.php' ) ); ?>" class="button button-large">Not right now</a>
			</p>
			<?php
		}

		/**
		 * A sample function with possibly all HTML input fields to showcase how they would display in the form. Content is expected by wrapping in the <form> tag.
		 *
		 * @return string
		 */
		public function form_fields_view() {
			$wizard_handler = Wisdm_Wizard_Handler::get_instance();
			?>
			<form method="post" action="<?php echo esc_url( $wizard_handler->get_next_step_link() ); ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="inp_textbox">Textbox</label></th>
							<td>
								<input type="text" id="inp_textbox" name="inp_textbox" class="location-input" value="">
								<p class="description">The textbox input field</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="male">Radio Buttons</label></th>
							<td>
								<input type="radio" id="male" name="gender" value="male">
								<label for="male">Male</label><br>
								<input type="radio" id="female" name="gender" value="female">
								<label for="female">Female</label><br>
								<input type="radio" id="other" name="gender" value="other">
								<label for="other">Other</label>
								<p class="description">The Readio Button input field</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_checkbox">Checkbox/Switch</label></th>
							<td>
								<input type="checkbox" name="inp_checkbox" id="inp_checkbox" class="switch-input">
								<label for="inp_checkbox" class="switch-label">
									<span class="toggle--on">On</span>
									<span class="toggle--off">Off</span>
								</label>
								<span class="description">Checkbox input type.</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_color">Color</label></th>
							<td>
								<input type="color" id="inp_color" name="inp_color">
								<span class="description">Color input type. Probably it will behave differently in different browsers.</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_date">Date</label></th>
							<td>
								<input type="date" id="inp_date" name="inp_date">
								<span class="description">Date input type. Probably it will behave differently in different browsers.</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_email">Email</label></th>
							<td>
								<input type="email" id="inp_email" name="inp_email">
								<span class="description">Email input type.</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_file">File</label></th>
							<td>
								<input type="file" id="inp_file" name="inp_file">
								<span class="description">File input type.</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="inp_number">Number</label></th>
							<td>
								<input type="number" id="inp_number" name="inp_number" min="1" max="5">
								<span class="description">Number input type.</span>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="wc-setup-actions step">
					<input type="submit" class="button-primary button button-large button-next" value="Continue" name="save_step">
					<input type="hidden" name="wisdm_setup_step" value="<?php echo esc_attr( $wizard_handler->get_current_step_slug() ); ?>" />
					<a href="<?php echo esc_url( $wizard_handler->get_next_step_link() ); ?>" class="button button-large button-next">Skip this step</a>
					<?php wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * A final step to tell the user that all steps are completed. Now, you decide what to do.
		 *
		 * @return string
		 */
		public function ready_view() {
			$setup_wizard = Wisdm_Setup_Wizard::get_instance();
			?>
			<div class="wisdm-setup-done">
						<?php echo $setup_wizard->get_checked_image_html(); ?>
				<h1>All settings are done!</h1>
			</div>

			<div class="wisdm-setup-done-content">
				<p class="wc-setup-actions step">
					<a class="button button-primary" href="#">Setup Group Product</a>
					<a class="button" href="#">More Settings</a>
				</p>
			</div>
			<?php
		}

		/**
		 * A callback function to save the data.
		 *
		 * @return void
		 */
		function form_fields_save() {
			// Your code to save the data.
		}
	}
}
```

## Setup Wizard Link
Assuming that you have included the file as mentioned in the "How to Integrate" section, use the following code to get the link to the setup wizard of your plugin.

```php
$wizard_handler = Wisdm_Wizard_Handler::get_instance();
$link = $wizard_handler->get_wizard_first_step_link( 'your-wizard-slug' );
```

