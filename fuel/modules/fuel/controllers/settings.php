<?php

require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class Settings extends Fuel_base_controller {

	function __construct()
	{
		parent::__construct();
		
		$this->_validate_user('settings');

		$crumbs = array(lang('section_settings'));
		$this->fuel->admin->set_titlebar($crumbs);
	}

	function index()
	{
		$this->_validate_user('settings');
		
		$settings = array();
		$modules = $this->fuel->modules->advanced(TRUE);
		foreach ($modules as $key => $module)
		{
			if ($module->has_settings())
			{
				$settings[$module->name()] = $module;
			}
		}
		$vars['settings'] = $settings;
		
		
		$crumbs = array(lang('section_settings'));
		$this->fuel->admin->set_titlebar($crumbs, 'ico_settings');
		
		$this->fuel->admin->render('settings', $vars);
	}

	function manage($module = '')
	{
		$this->_validate_user($module.'_settings');
		
		if (empty($module))
		{
			redirect('fuel/settings');
		}
		
		$this->js_controller_params['method'] = 'add_edit';
		
		$mod = $this->fuel->modules->get($module);

		$settings = $this->fuel->modules->get($module)->settings_fields();
		
		if (empty($settings)) 
		{
			show_error(lang('settings_problem', $module, $module, $module));
		}
		
		$new_settings = $this->input->post('settings', TRUE);
		
		if ($this->fuel->settings->process($module, $settings, $new_settings))
		{
			$this->fuel->cache->clear_module($module);
			$this->session->set_flashdata('success', lang('data_saved'));
			redirect($this->uri->uri_string());
		}
		
		$field_values = $this->fuel->settings->get($module);

		$this->load->library('form_builder');
		
		$this->form_builder->label_layout = 'left';
		$this->form_builder->form->validator = $this->fuel->settings->get_validation();
		$this->form_builder->use_form_tag = FALSE;
		$this->form_builder->set_fields($settings);
		$this->form_builder->display_errors = FALSE;
		$this->form_builder->name_array = 'settings';
		$this->form_builder->submit_value = 'Save';
		$this->form_builder->set_field_values($field_values);
		
		$vars = array();
		$vars['module'] = $mod->friendly_name();
		$vars['form'] = $this->form_builder->render();
		
		$this->_validate_user('manage');
		$crumbs = array('settings' => lang('section_settings'), $mod->friendly_name());
		$this->fuel->admin->set_titlebar($crumbs, 'ico_settings');
		
		$this->fuel->admin->render('manage/settings', $vars, Fuel_admin::DISPLAY_NO_ACTION);
	}

}