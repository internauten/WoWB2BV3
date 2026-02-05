<?php

/**
 * Group Price Text Module
 *
 * @author    Your Name
 * @copyright Copyright (c) 2026
 * @license   Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class GroupPriceText extends Module
{
    public function __construct()
    {
        $this->name = 'grouppricetext';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'die.internauten.ch';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Group Price Text');
        $this->description = $this->l('Display custom text on product pages for specific customer groups');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductPriceBlock')
            && Configuration::updateValue('GROUPPRICETEXT_GROUP_ID', 1)
            && Configuration::updateValue('GROUPPRICETEXT_MESSAGE', $this->l('Special pricing for your group!'))
            && Configuration::updateValue('GROUPPRICETEXT_ENABLED', 1);
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('GROUPPRICETEXT_GROUP_ID')
            && Configuration::deleteByName('GROUPPRICETEXT_MESSAGE')
            && Configuration::deleteByName('GROUPPRICETEXT_ENABLED');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $groupId = (int)Tools::getValue('GROUPPRICETEXT_GROUP_ID');
            $message = Tools::getValue('GROUPPRICETEXT_MESSAGE');
            $enabled = (int)Tools::getValue('GROUPPRICETEXT_ENABLED');

            if (!$groupId || !Validate::isUnsignedId($groupId)) {
                $output .= $this->displayError($this->l('Invalid group ID'));
            } elseif (!$message || !Validate::isGenericName($message)) {
                $output .= $this->displayError($this->l('Invalid message'));
            } else {
                Configuration::updateValue('GROUPPRICETEXT_GROUP_ID', $groupId);
                Configuration::updateValue('GROUPPRICETEXT_MESSAGE', $message);
                Configuration::updateValue('GROUPPRICETEXT_ENABLED', $enabled);
                $output .= $this->displayConfirmation($this->l('Settings updated successfully'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Get all customer groups
        $groups = Group::getGroups($defaultLang);

        $groupOptions = [];
        foreach ($groups as $group) {
            $groupOptions[] = [
                'id' => $group['id_group'],
                'name' => $group['name']
            ];
        }

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'GROUPPRICETEXT_ENABLED',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable the module'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ]
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Customer Group'),
                        'name' => 'GROUPPRICETEXT_GROUP_ID',
                        'required' => true,
                        'options' => [
                            'query' => $groupOptions,
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('Select the customer group that will see the message')
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Message'),
                        'name' => 'GROUPPRICETEXT_MESSAGE',
                        'required' => true,
                        'desc' => $this->l('Text to display for the selected customer group'),
                        'cols' => 60,
                        'rows' => 5
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        $helper->fields_value['GROUPPRICETEXT_GROUP_ID'] = Configuration::get('GROUPPRICETEXT_GROUP_ID');
        $helper->fields_value['GROUPPRICETEXT_MESSAGE'] = Configuration::get('GROUPPRICETEXT_MESSAGE');
        $helper->fields_value['GROUPPRICETEXT_ENABLED'] = Configuration::get('GROUPPRICETEXT_ENABLED');

        return $helper->generateForm([$fieldsForm]);
    }

    public function hookDisplayProductPriceBlock($params)
    {
        // Check if module is enabled
        if (!Configuration::get('GROUPPRICETEXT_ENABLED')) {
            return '';
        }

        // Check if we have the 'after_price' type
        if (isset($params['type']) && $params['type'] !== 'after_price') {
            return '';
        }

        // Get current customer
        $context = Context::getContext();

        if (!$context->customer || !$context->customer->isLogged()) {
            return '';
        }

        // Get configured group ID
        $targetGroupId = (int)Configuration::get('GROUPPRICETEXT_GROUP_ID');

        // Check if customer's main (default) group matches target group
        if ((int)$context->customer->id_default_group !== $targetGroupId) {
            return '';
        }

        // Check if product has specific price/discount using multiple methods
        $hasSpecificPrice = false;

        // Method 1: Check reduction_type
        if (isset($params['product']->reduction_type) && !empty($params['product']->reduction_type)) {
            $hasSpecificPrice = true;
        }

        // Method 2: Check reduction value
        if (isset($params['product']->reduction) && $params['product']->reduction > 0) {
            $hasSpecificPrice = true;
        }

        // Method 3: Check specific_prices array
        if (isset($params['product']->specific_prices) && !empty($params['product']->specific_prices)) {
            $hasSpecificPrice = true;
        }

        // Method 4: Check has_discount on product object
        if (isset($params['product']->product->has_discount) && $params['product']->product->has_discount) {
            $hasSpecificPrice = true;
        }

        $regularPrice = null;
        $message = null;

        // Do not show text if product has specific price
        if ($hasSpecificPrice) {
            $productObj = new Product((int)$params['product']->id_product, false, $context->language->id);
            $taxRate = $productObj->getTaxesRate();
            $priceIncl = $productObj->price * (1 + ($taxRate / 100));
            $regularPrice = Tools::displayPrice($priceIncl);
        } else {
            // Get the message
            $message = Configuration::get('GROUPPRICETEXT_MESSAGE');
        }

        // Assign variables to template
        $this->context->smarty->assign([
            'group_message' => $message,
            'regular_price' => $regularPrice,
            'debug_info' => null,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayproductpriceblock.tpl');
    }
}
