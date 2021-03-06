<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');
?>
<script type="text/javascript">
    wplj(document).ready(function()
    {
        // If client leave the wizard in between, this will find out the step in the page load and will jump to that step
        //Start: Get URL
        wplj.urlParam = function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results == null) {
                return null;
            }
            else {
                return results[1] || 0;
            }
        }
        if(!wplj.urlParam('tpl'))
        {
            var step = wpl_idx_get_step();
            if(step > 3)
            {
                window.location.replace("<?php echo wpl_global::add_qs_var('tpl', 'valid'); ?>");
            }
        }
        if(wplj.urlParam('tpl') == 'valid')
        {
            var step = wpl_idx_get_step();
            wpl_idx_goto_step(step);
        }
        if(wplj.urlParam('tpl') == 'setting')
        {
            wpl_idx_setting_table();
        }
        if(wplj.urlParam('tpl') == 'trial')
        {
            wpl_idx_protect_trial();
        }
        //Back event in idx wizard
        wplj('.wpl-idx-wizard-navigation .back').on('click',function(){

            if(wplj('.wpl-wizard-tabs .wpl-column.current').prev().length)
            {
                wplj('.wpl-wizard-tabs .wpl-column.current').removeClass('current').removeClass('active').prev().addClass('current');
                wplj('.wpl-wizard-section.current').removeClass('current').prev().addClass('current');
            }
        });

        //Search through mls provider table
        wplj("#wpl-idx-search-mls-provider").keyup(function()
        {
            var term = wplj(this).val().toLowerCase();

            if(term != "")
            {
                wplj("#wpl-idx-all-mls-providers tbody tr").hide();
                wplj("#wpl-idx-all-mls-providers tbody tr").filter(function()
                {
                    var activity_values = wplj(this)
                        .children('td.provider, td.name')
                        .text();

                    return activity_values.toLowerCase().indexOf(term) > -1;
                }).show();
            }
            else
            {
                wplj("#wpl-idx-all-mls-providers tbody tr").show();
            }
        });
        // mls provider table checkboxes
        wplj(document).on('click','#wpl-idx-all-mls-providers .wpl-idx-table-checkbox',function(){
            if(wplj(this).is(':checked'))
            {
                wplj('#wpl-idx-all-mls-providers .wpl-idx-table-checkbox').removeAttr('checked');
                wplj(this).attr('checked','checked');
            }
        });
        //Calculate total amount of what client should pay
        wplj(document).on('click','.wpl-idx-table-checkbox',function(){
            wplj('#wpl-idx-total-price-choose-mls .price').html(wpl_idx_total_amount()+'$');
        });


        /*Configuration table checkbox events*/
        /*wplj('document,.wpl-idx-form-checkbox .yesno').checkbox({
         cls: 'jquery-safari-checkbox',
         empty: '<?php echo wpl_global::get_wpl_asset_url('img/empty.png'); ?>'
     });*/

        wplj(document).on('click','#active_listings_checkbox',function(){
            wplj(this).parents('.wpl-idx-addon-table-row').find('#configure_checkbox').trigger('click');
        });
        wplj(document).on('click','#configure_checkbox',function(){
            wplj(this).parents('.wpl-idx-addon-table-row').find('.wpl-idx-config-form-part2').toggle();
            if(wplj(this).is(':checked'))
            {
                wplj(this).parents('.wpl-idx-addon-table-row').find('#active_listings_checkbox').removeAttr('checked');
            }
            else
            {
                wplj(this).parents('.wpl-idx-addon-table-row').find('#active_listings_checkbox').attr('checked','checked');
                wplj(this).parents('.wpl-idx-addon-table-row').find('.wpl-idx-config-form-part2 input[type="checkbox"]').removeAttr('checked');
                wplj(this).parents('.wpl-idx-addon-table-row').find('.wpl-idx-config-form-part2 input[type="number"]').val("");
            }
        });
        wplj(document).on('click','#office_listing',function(){
            if(wplj(this).is(':checked'))
            {
                wplj(this).parents('.wpl-idx-addon-table-row').find('#all_listing').removeAttr('checked');
                wplj(this).parents('.wpl-idx-addon-table-row').find('#agent_listing').removeAttr('checked');
            }
        });
        wplj(document).on('click','#all_listing',function(){
            if(wplj(this).is(':checked'))
            {
                wplj(this).parents('.wpl-idx-addon-table-row').find('#office_listing').removeAttr('checked');
                wplj(this).parents('.wpl-idx-addon-table-row').find('#agent_listing').removeAttr('checked');
            }
        });
        wplj(document).on('click','#agent_listing',function(){
            if(wplj(this).is(':checked'))
            {
                wplj(this).parents('.wpl-idx-addon-table-row').find('#all_listing').removeAttr('checked');
                wplj(this).parents('.wpl-idx-addon-table-row').find('#office_listing').removeAttr('checked');
            }
        });
    });

    // IDX addon wizard jump to specific step
    function wpl_idx_goto_step(step)
    {
        for(var i=1;i<=step;i++) {
            wplj("#wpl-idx-wizard-step" + i).addClass('active').removeClass('current');
        }
        wplj("#wpl-idx-wizard-step" + step).addClass('current');

        if(step == 2)
        {
            wpl_idx_providers();
        }
        if(step == 3)
        {
            wpl_idx_check_payment();
        }
        if(step == 4)
        {
            wpl_idx_show_configuration_list();
        }
        wplj('.wpl-wizard-section').removeClass('current');
        wplj('#wpl-wizard-section'+step).addClass('current');

    }
    // IDX addon wizard next button action
    function wpl_idx_next_step()
    {

        wplj('.wpl-wizard-tabs .wpl-column.current').addClass('active');

        if(wplj('.wpl-wizard-tabs .wpl-column.current').hasClass('active') && wplj('.wpl-wizard-tabs .wpl-column.current').next().length)
        {
            wplj('.wpl-wizard-tabs .wpl-column.current').removeClass('current').next().addClass('current');
            wplj('.wpl-wizard-section.current').removeClass('current').next().addClass('current');
        }
    }
    // Get the wizard get current Step.It returns step from database. if the client leave the wizard in the middle.
    function wpl_idx_get_step()
    {

        wpl_remove_message('.wpl_show_message_idx');

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-addon .panel-body', 'normal', 'center', true);
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=get_step';
        var step = 0;
        /** run ajax query **/
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                async: false,
                success: function(data)
                {
                    step = data.step_value;
                    if(data.message == 'Finished')
                    {
                        wpl_idx_wizard_thank_you();
                    }
                    if(data.message == 'idx user already exists')
                    {
                        wpl_idx_wizard_already_registered();
                    }
                    if(data.message == 'PHP >= 5.5 is required.')
                    {
                        wpl_idx_wizard_php_version();
                    }
                    Realtyna.ajaxLoader.hide(loader);

                }
            });
        return step;
    }
    // Calculate Total amount of selected mls packages that client purchase
    function wpl_idx_total_amount()
    {
        var total_amount = 0;
        var price = '';
        wplj('.wpl-idx-table-checkbox').each(function () {
            if(wplj(this).is(':checked')){


                price = wplj(this).parents('.wpl-idx-addon-table-row').find('.price_total').html();
                price = price.split('$');
                total_amount += parseInt(price[0]);
            }
        });
        return total_amount;
    }
    // Form valication
    function wpl_idx_form_validation(form,step)
    {
        wpl_remove_message('.wpl_show_message_idx');
        var valid = 1;

        wplj(form).find('input').each(function(){
            if(!wplj(this).val()){
                wplj(this).addClass('required');
                valid = 0;
            }
            else
            {
                wplj(this).removeClass('required');
            }
        });
        if(valid)
        {
            if (step == 'registration')
            {
                wpl_idx_registration();
            }
            if (step =='payment')
            {
                wpl_idx_payment();
            }
            if (step == 'configuration')
            {
                wpl_idx_configuration();
            }
        }
        else
        {
            wpl_show_messages('<?php echo __("All fields are required!"); ?>', '.wpl_show_message_idx', 'wpl_red_msg');
        }
    }
    // New user registration -- Sign up step
    function wpl_idx_registration()
    {
        wpl_remove_message('.wpl_show_message_idx');

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=registration';
        var name = wplj('#name').val();
        var email = wplj('#email').val();
        var phone = wplj('#phone').val();
        var errors = '';

        request_str += "&name="+name+"&second_email="+email+"&phone="+phone;

        /** run ajax query **/
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function(data)
                {
                    if(data.status == 500)
                    {
                        if(data.message == 'idx user already exists' || data.error == 'email is already registered')
                        {
                            Realtyna.ajaxLoader.hide(loader);
                            if(data.message)
                            {
                                wpl_show_messages(data.message, '.wpl_show_message_idx', 'wpl_red_msg');
                            }
                            if(data.error)
                            {
                                wpl_show_messages(data.error, '.wpl_show_message_idx', 'wpl_red_msg');
                            }
                        }
                        else
                        {
                            for (var error in data.error) {
                                if (data.error.hasOwnProperty(error)) {
                                    errors += data.error[error]+'<br/>';
                                }
                            }
                            Realtyna.ajaxLoader.hide(loader);
                            wpl_show_messages(errors, '.wpl_show_message_idx', 'wpl_red_msg');
                        }
                    }
                    if(data.status == 200 || data.status == 201)
                    {
                        wpl_show_messages(data.error, '.wpl_show_message_idx', 'wpl_green_msg');
                        Realtyna.ajaxLoader.hide(loader);
                        wpl_idx_next_step();
                        if(wplj.urlParam('tpl') == 'valid') wpl_idx_providers();
                        if(wplj.urlParam('tpl') == 'trial') wpl_idx_load_trial_data();
                    }
                }
            });
    }
    // Showing all providers in the table -- Choose mls Step
    function wpl_idx_providers() {

        wpl_remove_message('.wpl_show_message_idx');

        /*If the providers table already loaded*/
        if (wplj('#wpl-idx-all-mls-providers').hasClass('loaded')) return false;

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=providers';

        /** run ajax query **/
        var mlsProviders = [];
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    mlsProviders += '<tr class="wpl-idx-addon-table-row">';
                    mlsProviders += '<td class="wpl-idx-addon-table-title"></td>'
                    mlsProviders += '<td class="wpl-idx-addon-table-title" colspan="3"><?php echo __('MLS Provider','wpl'); ?></td>';
                    mlsProviders += '<td class="wpl-idx-addon-table-title"><?php echo __('Price','wpl'); ?></td>';
                    mlsProviders += '</tr>';
                    wplj.each(data.response, function (key, value) {
                        mlsProviders += '<tr class="wpl-idx-addon-table-row">';
                        mlsProviders += '<td class="mls_id" width="40"><input id='+value.mls_id+' class="wpl-idx-table-checkbox" type="radio" /></td>';
                        mlsProviders += '<td class="logo" width="40"><img height="25" src="'+ value.logo +'" /></td>';
                        mlsProviders += '<td class="provider">'+ value.provider +'</td>';
                        mlsProviders += '<td class="name">'+ value.name +'</td>';
                        mlsProviders += '<td class="price"><del>'+ value.price +'$</del><span class="price_total">'+ value.price_total +'$</span></td>';
                        mlsProviders += '</tr>';
                    });
                    Realtyna.ajaxLoader.hide(loader);
                    wplj('#wpl-idx-all-mls-providers tbody').html("");
                    wplj('#wpl-idx-all-mls-providers tbody').append(mlsProviders);
                    wplj('#wpl-idx-all-mls-providers').addClass("loaded");
                }
            });
    }
    // Adding mls package information that client choose -- Choose mls step
    function wpl_idx_save()
    {

        wpl_remove_message('.wpl_show_message_idx');

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var name= '';
        var provider = '';
        var mls_id = '';
        var request_str = "";

        if(!wplj('.wpl-idx-table-checkbox:checked').length)
        {
            wpl_show_messages('<?php echo __("Please choose a mls provider"); ?>', '.wpl_show_message_idx', 'wpl_red_msg');
            Realtyna.ajaxLoader.hide(loader);
            return false;
        }

        wplj('.wpl-idx-table-checkbox').each(function () {
            if(wplj(this).is(':checked')){

                mls_id = wplj(this).attr('id');
                name = wplj(this).parents('.wpl-idx-addon-table-row').children('.name').html();
                provider = wplj(this).parents('.wpl-idx-addon-table-row').children('.provider').html();

                request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=save';
                request_str += "&mls_id="+mls_id+"&name="+name+"&provider="+provider;

                /** run ajax query **/
                wplj.ajax(
                    {
                        type: "POST",
                        url: '<?php echo wpl_global::get_full_url(); ?>',
                        data: request_str,
                        success: function (data) {
                            if(data.status == 500)
                            {
                                Realtyna.ajaxLoader.hide(loader);
                                wpl_show_messages(data.error, '.wpl_show_message_idx', 'wpl_red_msg');
                            }
                            if(data.status == 200 || data.status == 201)
                            {
                                Realtyna.ajaxLoader.hide(loader);
                                wpl_show_messages(data.error, '.wpl_show_message_idx', 'wpl_green_msg');
                                wpl_idx_next_step();
                                wpl_idx_calculate_price();
                            }

                        }
                    });
            }
        });
    }
    // Showing mls package information that client choose in the choose mls section -- Payment step
    function wpl_idx_calculate_price()
    {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var totalAmount = 0;
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=price';

        /** run ajax query **/
        var mlsProviders = [];
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    //wplj.each(data, function (key, value) {
                    mlsProviders += '<tr class="wpl-idx-addon-table-row">';
                    mlsProviders += '<td class="logo" width="40"><img height="25" src="' + data.price_list.provider_information.logo + '" /></td>';
                    mlsProviders += '<td class="provider" width="40">' + data.price_list.Provider + '</td>';
                    mlsProviders += '<td class="price_total">' + data.price_list.pricePerUnit.USD.total + '$ <?php echo __("Per"); ?> ' + data.price_list.unit + '<input type="hidden" value="'+data.price_list.pricePerUnit.USD.total +'"></td>';
                    mlsProviders += '</tr>';
                    totalAmount = parseInt(data.price_list.pricePerUnit.USD.total);

                    //});
                    Realtyna.ajaxLoader.hide(loader);
                    wplj('#wpl-idx-selected-mls-providers tbody').html("");
                    wplj('#wpl-idx-selected-mls-providers tbody').append(mlsProviders);
                    wplj('#wpl-idx-total-price-payment .price').html(totalAmount+'$');
                }
            });
    }
    /*Configuration table*/
    function wpl_idx_show_configuration_list()
    {
        /*If the configuration table already loaded*/
        if (wplj('#wpl-idx-selected-mls-providers-configuration').hasClass('loaded')) return false;

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var config_form ="";
        wpl_remove_message('.wpl_show_message_idx');
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=price';

        /** run ajax query **/
        var mlsProviders = [];
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    //wplj.each(data.price_list, function (key, value) {
                    config_form = wpl_idx_generate_config_form(data.price_list.provider_information);

                    mlsProviders += '<div id="'+data.price_list.MlsId+'" class="wpl-idx-addon-table-row">';
                    mlsProviders += '<div class="mls_info">';
                    mlsProviders += '<span class="logo" width="40"><img height="25" src="' + data.price_list.provider_information.logo + '" /></span>';
                    mlsProviders += '<span id="provider" class="provider">' + data.price_list.Provider + '</span>';
                    mlsProviders += '<span class="provider_full_name">' + data.price_list.ProviderFullName + '</span>';
                    mlsProviders += '</div>';
                    mlsProviders += '<div id="config_form" class="wpl-idx-config-row">'+config_form+'</div>';
                    mlsProviders += '</div>';
                    //});
                    Realtyna.ajaxLoader.hide(loader);
                    wplj('#wpl-idx-selected-mls-providers-configuration').html("");
                    wplj('#wpl-idx-selected-mls-providers-configuration').append(mlsProviders).addClass("loaded");
                }
            });
    }
    /*Payment*/
    function wpl_idx_payment()
    {
        wpl_remove_message('.wpl_show_message_idx');

        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = '';
        var messages = '';
        var row = '#wpl-idx-selected-mls-providers .wpl-idx-addon-table-row';
        var url = window.location;

        wplj(row).each(function(){
            var mls = wplj(this).find('.provider').html();
            request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=get_keys';
            /** run ajax query **/
            wplj.ajax(
                {
                    type: "POST",
                    url: '<?php echo wpl_global::get_full_url(); ?>',
                    data: request_str,
                    success: function (data) {
                        Realtyna.ajaxLoader.hide(loader);
                        window.location.replace("https://payment.realtyna.com/"+data.secret+'/'+mls+'/'+window.location);
                    }
                });
        });
    }
    /*Insert Configuration*/
    function wpl_idx_configuration()
    {

        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = "";
        var row = '#wpl-idx-selected-mls-providers-configuration .wpl-idx-addon-table-row';

        //wplj('#wpl-idx-selected-mls-providers-configuration .wpl-idx-addon-table-row').each(function(){
        var mls_id         = wplj(row).attr('id');
        var provider       = wplj(row).find('#provider').html();
        var agent_id       = wplj(row).find('#agent_id').val();
        var office_id      = wplj(row).find('#office_id').val();
        var agent_name     = wplj(row).find('#agent_name').val();
        var office_name    = wplj(row).find('#office_name').val();

        var import_status  = (wplj(row).find('#import_status').is(':checked')) ? 1 : 0;
        var listing_status = (wplj(row).find('#listing_status').is(':checked')) ? 0 : 1;
        var office_listing = (wplj(row).find('#office_listing').is(':checked')) ? 1 : 0;
        var agent_listing  = (wplj(row).find('#agent_listing').is(':checked')) ? 1 : 0;
        var all_listing    = (wplj(row).find('#all_listing').is(':checked')) ? 1 : 0;

        var property_type;
        if(!wplj("#category").val()) property_type = ""; else property_type = wplj("#category").val();

        var min_bathrooms   = wplj(row).find('#min_bathrooms').val();
        var max_bathrooms   = wplj(row).find('#max_bathrooms').val();
        var min_bedrooms    = wplj(row).find('#min_bedrooms').val();
        var max_bedrooms    = wplj(row).find('#max_bedrooms').val();
        var min_price       = wplj(row).find('#min_price').val();
        var max_price       = wplj(row).find('#max_price').val();
        var square_feet_min = wplj(row).find('#square_feet_min').val();
        var square_feet_max = wplj(row).find('#square_feet_max').val();
        var zipcode         = wplj(row).find('#zipcode').val();
        var errors = '';

        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=configuration';
        request_str += '&mls_id='+mls_id+'&provider='+provider+'&agent_id='+agent_id+'&office_id='+office_id+
        '&agent_name='+agent_name+'&office_name='+office_name+
        '&property_type='+property_type+
        '&import_status='+import_status+'&listing_status='+listing_status+'&office_listing='+office_listing+
        '&agent_listing='+agent_listing+'&all_listing='+all_listing+
        '&min_bathrooms='+min_bathrooms+'&max_bathrooms='+max_bathrooms+'&min_bedrooms='+min_bedrooms+
        '&max_bedrooms='+max_bedrooms+'&min_price='+min_price+'&max_price='+max_price+
        '&square_feet_min='+square_feet_min+'&square_feet_max='+square_feet_max+'&zipcode='+zipcode;

        /** run ajax query **/
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    if(data.status == 500)
                    {
                        if(typeof data.error == 'string')
                        {
                            errors = data.error;
                        }
                        else
                        {
                            for (var error in data.error) {
                                if (data.error.hasOwnProperty(error)) {
                                    errors += data.error[error]+'<br/>';
                                }
                            }
                        }

                        wpl_show_messages(errors, '.wpl_show_message_idx', 'wpl_red_msg');
                    }
                    if(data.status == 200 || data.status == 201)
                    {
                        wpl_show_messages(data.message, '.wpl_show_message_idx', 'wpl_green_msg');
                        Realtyna.ajaxLoader.hide(loader);
                        wpl_idx_wizard_thank_you();
                    }
                }

            });
        //});
    }
    /*Generate configuration form*/
    function wpl_idx_generate_config_form(property_types)
    {
        var options;

        wplj.each(property_types.category, function (key, value) {
            options +=' <option value="'+value.category+'">'+value.category+'</option>';
        });

        var config_form = '<div class="wpl-idx-config-form" style="display: none">';
        config_form += '<div class="wpl-idx-config-form-part1 clearfix">';
        config_form +='<div class="wpl-small-12 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +='<input id="active_listings_checkbox" type="checkbox" class="yesno" checked="checked">';
        config_form += '<?php echo __('Import all active listings', 'wpl')?>';
        config_form +='</div>';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +='<input id="configure_checkbox" type="checkbox" class="yesno">';
        config_form += '<?php echo __('Configure', 'wpl')?>';
        config_form +='</div>';
        config_form +='</div>';
        config_form +='<div class="wpl-small-12 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form wpl-row">';
        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon agent-icon"></span>';
        //config_form +='<span class="wpl-idx-icon tooltip-icon wpl_setting_form_tooltip wpl_help">';
        //config_form += '<span class="wpl_help_description"><?php echo __('The agent id should be real, In order to find out about it your should click here.', 'wpl')?></span>';
        //config_form += '</span></span>';
        config_form +='<input id="agent_name" type="text" placeholder="Agent Name">';
        config_form +='</div></div>';
        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon agent-icon"></span>';
        config_form +='<input id="agent_id" type="text" placeholder="Agent ID">';
        config_form +='</div></div>';
        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon office-icon"></span>';
        config_form +='<input id="office_name" type="text" placeholder="Office Name">';
        config_form +='</div></div>';
        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon office-icon"></span>';
        config_form +='<input id="office_id" type="text" placeholder="Office ID">';
        config_form +='</div></div>';
        config_form +='</div>';
        config_form +='</div>';
        config_form +='</div>';
        config_form +='<div class="wpl-idx-config-form-part2 clearfix" style="display: none">';
        config_form +='<div class="wpl-small-12 wpl-medium-6 wpl-large-3 wpl-column">';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +='<input id="all_listing" type="checkbox" class="yesno">';
        config_form += '<?php echo __('Import all the listings', 'wpl')?>';
        config_form +='</div>';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +='<input id="office_listing" type="checkbox" class="yesno">';
        config_form += '<?php echo __('Import office listings only', 'wpl')?>';
        config_form +='</div>';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +='<input id="agent_listing" type="checkbox" class="yesno">';
        config_form += '<?php echo __('Import agent listings only', 'wpl')?>';
        config_form +='</div>';
        config_form +='<div class="wpl-idx-form-checkbox">';
        config_form +=' <input id="listing_status" type="checkbox" class="yesno">';
        config_form += '<?php echo __('I want the sold data as well', 'wpl')?>';
        config_form +='</div>';
        config_form +='</div>';
        config_form +='<div class="wpl-small-12 wpl-medium-6 wpl-large-9 wpl-column">';
        config_form +='<div class="wpl-idx-form wpl-row">';

        config_form +='<div class="wpl-idx-form-element  wpl-small-12 wpl-medium-12 wpl-large-12 wpl-column">';
        config_form +=' <select  id="category" multiple data-placeholder="<?php echo __('All Categories', 'wpl')?>">';
        config_form += options;
        config_form +=' </select>';
        config_form +='</div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon bed-icon"></span>';
        config_form +=' <input id="min_bedrooms" type="number" placeholder="Min Beds">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon bed-icon"></span>';
        config_form +='<input id="max_bedrooms" type="number" placeholder="Max Beds">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon bath-icon"></span>';
        config_form +='<input id="min_bathrooms" type="number" placeholder="Min Baths">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon bath-icon"></span>';
        config_form +='<input id="max_bathrooms" type="number" placeholder="Max Baths">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon price-icon"></span>';
        config_form +='<input id="min_price"  type="number" placeholder="Min Price">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon price-icon"></span>';
        config_form +='<input id="max_price" type="number" placeholder="Max Price">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon sqft-icon"></span>';
        config_form +=' <input id="square_feet_min" type="number" placeholder="Min SQFT">';
        config_form +='</div></div>';

        config_form +=' <div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon sqft-icon"></span>';
        config_form +='<input id="square_feet_max" type="text" placeholder="Max SQFT">';
        config_form +='</div></div>';

        config_form +='<div class="wpl-small-6 wpl-medium-6 wpl-large-6 wpl-column">';
        config_form +='<div class="wpl-idx-form-element">';
        config_form +='<span class="wpl-idx-icon zipcode-icon"></span>';
        config_form +='<input id="zipcode" type="text" placeholder="Zip Code">';
        config_form +='</div></div>';

        config_form +='</div>';
        config_form +='</div>';
        config_form +='</div>';
        config_form +='</div>';

        return config_form;

    }
    /*messages*/
    function wpl_idx_wizard_thank_you() {
        wplj('.wpl-idx-wizard').remove();
        wplj('.wpl-idx-thank-you').remove();

        var success = '<div class="wpl-idx-thank-you">';
        success += '<h4 class="title"><?php echo __('Thank You!','wpl'); ?></h4>';
        success += '<p><?php echo __('Your request for adding MLS properties has been received. In order to check the status of importing properties click here:','wpl'); ?></p>';
        success += '<a class="wpl-button button-1" href="<?php echo wpl_global::add_qs_var('tpl', 'setting'); ?>"><?php echo __('Check status','wpl'); ?></a>';
        success += '<div>';

        wplj('.wpl-idx-wizard-main .panel-body').append(success);
    }
    function wpl_idx_wizard_already_registered() {
        wplj('.wpl-idx-wizard').remove();
        wplj('.wpl-idx-thank-you').remove();

        var success = '<div class="wpl-idx-thank-you">';
        success += '<h4 class="title"><?php echo __('You Already Registered!','wpl'); ?></h4>';
        success += '<p><?php echo __('Your request for adding MLS properties has been already registered in the system. In order to check the status of importing properties click here:','wpl'); ?></p>';
        success += '<a class="wpl-button button-1" href="<?php echo wpl_global::add_qs_var('tpl', 'setting'); ?>"><?php echo __('Check status','wpl'); ?></a>';
        success += '<div>';
        wplj('.wpl-idx-wizard-main .panel-body').append(success);
    }
    function wpl_idx_wizard_thank_you_trial() {
        wplj('.wpl-idx-wizard').remove();
        wplj('.wpl-idx-thank-you').remove();

        var success = '<div class="wpl-idx-thank-you">';
        success += '<h4 class="title"><?php echo __('Thank You!','wpl'); ?></h4>';
        success += '<p><?php echo __('All properties are imported. In order to see your properties please click here:','wpl'); ?></p>';
        success += '<a class="wpl-button button-1" href="<?php echo wpl_global::get_wpl_admin_menu('wpl_admin_listings'); ?>"><?php echo __('Listing Manager','wpl'); ?></a>';
        success += '<div>';

        wplj('.wpl-idx-wizard-main .panel-body').append(success);
    }
    function wpl_idx_wizard_already_used_trial()
    {
        wplj('.wpl-idx-wizard').remove();
        wplj('.wpl-idx-thank-you').remove();

        var success = '<div class="wpl-idx-thank-you">';
        success += '<h4 class="title"><?php echo __('You already used trial version!','wpl'); ?></h4>';
        success += '<p><?php echo __('You may already used the trial version or you may have valid version purchased. if you want to test again please click "Try again"','wpl'); ?></p>';
        success += '<a class="wpl-button button-1" href="<?php echo wpl_global::get_wpl_admin_menu('wpl_admin_listings'); ?>"><?php echo __('Listing Manager','wpl'); ?></a>';
        success += '<a class="wpl-button button-1" href="#" onclick="wpl_idx_reset_trial();"><?php echo __('Try Again','wpl'); ?></a>';
        success += '<div>';

        wplj('.wpl-idx-wizard-main .panel-body').append(success);
    }
    function wpl_idx_wizard_php_version() {
        wplj('.wpl-idx-wizard').remove();
        wplj('.wpl-idx-thank-you').remove();

        var success = '<div class="wpl-idx-thank-you">';
        success += '<h4 class="title"><?php echo __('PHP >= 5.5 is required.','wpl'); ?></h4>';
        success += '<div>';
        wplj('.wpl-idx-wizard-main .panel-body').append(success);
    }
    /*import sample properties in Trial version*/
    function wpl_idx_load_trial_data() {

        wpl_remove_message('.wpl_show_message_idx');
        //var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=load_trial_data';

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    wpl_idx_wizard_thank_you_trial();
                }

            });
    }
    /*Check if the Trial version is used once go to thank you page*/
    function wpl_idx_protect_trial()
    {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=protect_trial';

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    Realtyna.ajaxLoader.hide(loader);
                    if(data.status == 500)
                    {
                        wpl_idx_wizard_already_used_trial();
                    }
                }

            });

    }
    /*Settings page*/
    function wpl_idx_setting_table() {

        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main #wpl-idx-setting-table .message', 'normal', 'center', true);
        var totalAmount = 0;
        request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=status';

        /** run ajax query **/
        var mlsProviders = [];
        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {

                    wplj.each(data.response, function (key, value) {
                        console.log(value);
                        mlsProviders += '<tr class="wpl-idx-addon-table-row">';
                        mlsProviders += '<td class="logo" width="40"><img height="25" src="' + value.image + '"/></td>';
                        mlsProviders += '<td class="provider" width="40">' + value.short_name + '</td>';
                        mlsProviders += '<td class="provider-full-name">' + value.name + '</td>';
                        mlsProviders += '<td class="status '+ value.status +'">' + value.status + '</td>';
                        mlsProviders += '<td class="actions"><a href="#" onclick="wpl_idx_delete(0);"><?php echo __('delete','wpl'); ?></a></td>';
                        mlsProviders += '</tr>';
                    });
                    Realtyna.ajaxLoader.hide(loader);
                    if(mlsProviders.length)
                    {
                        wplj('#wpl-idx-setting-table tbody').html("");
                        wplj('#wpl-idx-setting-table tbody').append(mlsProviders);
                    }
                }
            });
    }
    function wpl_idx_delete(confirmed)
    {
        if (!confirmed)
        {
            message = "<?php echo __('Are you sure you want to remove this item?', 'wpl'); ?>";
            message += '&nbsp;<span class="wpl_actions" onclick=" wpl_idx_delete(1);"><?php echo __('Yes', 'wpl'); ?></span>&nbsp;<span class="wpl_actions" onclick="wpl_remove_message();"><?php echo __('No', 'wpl'); ?></span>';
            wpl_show_messages(message, '.wpl_idx_servers_list .wpl_show_message');
            return false;
        }
        else
        {
            wpl_remove_message();
            var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main #wpl-idx-setting-table', 'normal', 'center', true);
            request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=delete';

            /** run ajax query **/
            wplj.ajax(
                {
                    type: "POST",
                    url: '<?php echo wpl_global::get_full_url(); ?>',
                    data: request_str,
                    success: function (data) {
                        wplj('.wpl-idx-wizard-main #wpl-idx-setting-table tbody').html('<tr><td colspan="4"><div class="message"><?php echo __('No MLS Provider is Found! In order to add one please ', 'wpl').'<a href="'.wpl_global::get_wpl_admin_menu('wpl_addon_idx').'">'.__('Click here', 'wpl').'</a>';?></div></td></tr>');
                        Realtyna.ajaxLoader.hide(loader);
                    }
                });
        }
    }
    function wpl_idx_reset_trial() {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=reset';

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    Realtyna.ajaxLoader.hide(loader);
                    if (data.status == 500) {
                        var full_version = '<a href="<?php echo wpl_global::add_qs_var('tpl', 'valid'); ?>">Please click here to Continue the full version wizard.</a>';
                        wpl_show_messages(data.mesage+' '+full_version, '.wpl_show_message_idx', 'wpl_red_msg');
                    }
                    if (data.status == 200) {
                        wpl_show_messages(data.mesage, '.wpl_show_message_idx', 'wpl_green_msg');
                        window.location.replace("<?php echo wpl_global::add_qs_var('tpl', 'trial'); ?>");
                    }
                }

            });
    }
    function wpl_idx_back_step(step_name)
    {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=back_step&step_name='+step_name;

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    Realtyna.ajaxLoader.hide(loader);
                    if(step_name == 'save_action')
                    {
                        wpl_idx_providers();
                    }
                }

            });
    }
    function wpl_idx_request_mls()
    {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);

        var state = wplj('#wpl_request_mls_state').val();
        var provider = wplj('#wpl_request_mls_provider').val();
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=save_client_request&provider='+provider+'&state='+state;

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    Realtyna.ajaxLoader.hide(loader);
                    wplj._realtyna.lightbox.close();
                    if(data.status == '404')
                    {
                        wpl_show_messages(data.error, '.wpl_show_message_idx', 'wpl_red_msg');
                    }
                    if(data.status == '200' || data.status == '201')
                    {
                        wpl_show_messages(data.response, '.wpl_show_message_idx', 'wpl_green_msg');
                    }
                }

            });
    }
    /*Check if the payment is done skip the payment section*/
    function wpl_idx_check_payment()
    {
        wpl_remove_message('.wpl_show_message_idx');
        var loader = Realtyna.ajaxLoader.show('.wpl-idx-wizard-main .panel-body', 'normal', 'center', true);
        var request_str = 'wpl_format=b:addon_idx:ajax&wpl_function=check_payment';

        wplj.ajax(
            {
                type: "POST",
                url: '<?php echo wpl_global::get_full_url(); ?>',
                data: request_str,
                success: function (data) {
                    Realtyna.ajaxLoader.hide(loader);
                    if(data.status == "200")
                    {
                        wpl_idx_goto_step(4);
                    }
                    else
                    {
                        wpl_idx_calculate_price();
                    }
                }
            });
    }

</script>