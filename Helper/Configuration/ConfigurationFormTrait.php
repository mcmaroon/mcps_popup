<?php

namespace MCPS\Popup\Helper\Configuration;

trait ConfigurationFormTrait
{

    use ConfigurationTrait;

    private $configurationForm = [];
    private $configurationFormFieldsValue = [];

    private function defaultConfig()
    {
        $this->setConfigurationForm();
        return $this->configurationFormFieldsValue;
    }

    protected final function prepareActiveValues()
    {
        return [
            [
                'id' => 'active_on',
                'value' => true,
                'label' => $this->l('Enabled')
            ],
            [
                'id' => 'active_off',
                'value' => false,
                'label' => $this->l('Disabled')
            ]
        ];
    }

    public function setConfigurationForm()
    {

    }

    public final function getConfigurationForm()
    {
        return $this->configurationForm;
    }

    public final function addConfigurationBoolean($configKey, $configValue = true, $label = null, $description = null)
    {
        $values = $this->prepareActiveValues();
        $this->addConfigurationFormElement('switch', $configKey, $configValue, $label, $description, $values);
    }

    public final function addConfigurationText($configKey, $configValue = null, $label = null, $description = null, array $values = [], $lang = false)
    {

        $this->addConfigurationFormElement('text', $configKey, $configValue, $label, $description, $values, $lang);
    }

    public final function addConfigurationTextArea($configKey, $configValue = null, $label = null, $description = null, array $values = [], $lang = false)
    {
        $this->addConfigurationFormElement('textarea', $configKey, $configValue, $label, $description, $values, $lang);
    }

    public final function addConfigurationFormElement($type, $configKey, $configValue, $label = null, $description = null, array $values = [], $lang = false)
    {
        if ($lang && (!is_array($configValue) || empty($configValue))) {
            $configValue = $this->prepareLangConfigValue();
        }
        $this->configurationFormFieldsValue[$configKey] = $configValue;
        $this->configurationForm[$configKey] = [
            'type' => $type,
            'name' => $configKey,
            'label' => is_string($label) ? $label : $configKey,
            'desc' => $description,
            'lang' => $lang,
            'is_bool' => is_bool($configValue),
            'values' => $values,
            'defaultConfigValue' => $configValue,
            'autoload_rte' => true,
        ];
    }

    private function prepareLangConfigValue()
    {
        $configValue = [];
        $languages = \Language::getLanguages(true);
        foreach ($languages as $language) {
            $configValue[$language['id_lang']] = '';
        }
        return $configValue;
    }


    public function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $this->getConfigurationForm(),
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    public final function getSubmitAction()
    {
        return 'submit' . $this->name;
    }

    public function getContent()
    {
        if (((bool)\Tools::isSubmit($this->getSubmitAction())) == true) {
            $this->postProcess();
        }

        return $this->renderForm();
    }

    protected function renderForm()
    {
        $languages = \Language::getLanguages(true);

        $helper = new \HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = $this->getSubmitAction();
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = \Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfig(),
            'languages' => $languages,
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function postProcess()
    {
        $this->defaultConfig();
        $languages = \Language::getLanguages(true);
        $form_values = [];
        foreach ($this->getConfigurationForm() as $key => $value) {
            if (!$value['lang']) {
                $form_values[$key] = \Tools::getValue($key);
            } else {
                $value_lang = array();
                foreach ($languages as $language) {
                    $id_lang = $language['id_lang'];
                    $value_lang[$id_lang] = \Tools::getValue($key . '_' . $id_lang);
                }
                $form_values[$key] = $value_lang;
            }
        }
        $this->setConfig($form_values);
    }
}
